<?php

namespace App\Services\Print;

use App\Services\Afms\Renderers\RendererRegistry;
use App\Services\Print\PrintService;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

abstract class BasePrintService implements PrintService
{
    protected $sheet;
    protected array $config;
    private array $templateRowCache        = [];
    private array $accommodationRowCache   = [];


    // ==================== MAIN ENTRY POINT ====================

    public function handle(string $template, array $blocks, $sourceRecord): string
    {
        $spreadsheet  = IOFactory::load(public_path("templates/$template"));
        $this->sheet  = $spreadsheet->getActiveSheet();
        $this->config = $this->loadTemplateConfig($template);

        $this->cacheTemplateRows();
        $this->deleteAccommodationTemplateRows();
        $this->fillCommonFields($sourceRecord);

        if (!empty($this->config['document_markers']) && !empty($blocks[0])) {
            $docReplacements = [];
            foreach ($this->config['document_markers'] as $field => $cells) {
                $docReplacements['{{' . $field . '}}'] = (string) ($blocks[0][$field] ?? '');
            }
            $this->bulkReplace($docReplacements);
        }

        $row          = $this->config['items_start_row'] ?? $this->config['block_start'] ?? 0;
        $overallTotal = 0;

        foreach ($blocks as $index => $block) {
            $renderer = $this->resolveRenderer($template);

            if ($renderer && $result = $renderer->render($block, $row, $index > 0)) {
                [$row, $blockTotal] = $result;
                $overallTotal += $blockTotal;
            }
        }

        $this->bulkReplace([
            $this->config['placeholders']['overall_total'] ?? '{{overall_total}}' => number_format($overallTotal, 2),
        ]);

        return $this->saveFile($template, $spreadsheet);
    }


    // ==================== ABSTRACT ====================

    abstract protected function fillCommonFields($sourceRecord): void;


    // ==================== CONFIGURATION ====================

    private function loadTemplateConfig(string $template): array
    {
        $name = pathinfo($template, PATHINFO_FILENAME);
        $path = config_path("excel_templates/{$name}.xlsx.php");

        if (!file_exists($path)) {
            throw new \Exception("Template config not found: {$name}.php");
        }

        $config = require $path;

        if (!isset($config['placeholders'])) {
            throw new \Exception("Template config missing 'placeholders' key: {$name}");
        }

        return $config;
    }

    private function resolveRenderer(string $template): ?object
    {
        $key = collect(RendererRegistry::$rendererMap)
            ->keys()
            ->first(fn ($k) => str_contains($template, $k));

        if (!$key || !isset(RendererRegistry::$rendererMap[$key])) {
            return null;
        }

        $class = RendererRegistry::$rendererMap[$key];

        return new $class(
            sheet: $this->sheet,
            config: $this->config,
            duplicator: fn (int $row) => $this->duplicateBlock(
                $row,
                $this->templateRowCache,
                $this->config['block_start'],
                $this->config['block_end']
            ),
            accommodationDuplicator: fn (int $row) => $this->duplicateBlock(
                $row,
                $this->accommodationRowCache,
                $this->config['accommodation_block_start'],
                $this->config['accommodation_block_end']
            ),
        );
    }


    // ==================== TEMPLATE CACHING ====================

    private function cacheTemplateRows(): void
    {
        $this->cacheBlock(
            $this->config['block_start'],
            $this->config['block_end'],
            $this->templateRowCache
        );

        if (isset($this->config['accommodation_block_start'], $this->config['accommodation_block_end'])) {
            $this->cacheBlock(
                $this->config['accommodation_block_start'],
                $this->config['accommodation_block_end'],
                $this->accommodationRowCache
            );
        }
    }

    private function deleteAccommodationTemplateRows(): void
    {
        if (!isset($this->config['accommodation_block_start'], $this->config['accommodation_block_end'])) {
            return;
        }

        $start = $this->config['accommodation_block_start'];
        $end = $this->config['accommodation_block_end'];
        $count = $end - $start + 1;

        $this->sheet->removeRow($start, $count);
    }

    private function cacheBlock(int $start, int $end, array &$cache): void
    {
        $colEnd = Coordinate::columnIndexFromString($this->config['item_columns']['end'] ?? 'M');

        for ($row = $start; $row <= $end; $row++) {
            $styles = [];
            $values = [];

            foreach (range(1, $colEnd + 5) as $col) {
                $colLetter = Coordinate::stringFromColumnIndex($col);
                $cellRef = $colLetter . $row;
                $styles[$col] = $this->sheet->getStyle($cellRef)->exportArray();
                $values[$col] = $this->sheet->getCell($cellRef)->getValue();
            }

            $cache[$row] = [
                'height' => $this->sheet->getRowDimension($row)->getRowHeight(),
                'styles' => $styles,
                'values' => $values,
                'merges' => [],
            ];
        }

        foreach ($this->sheet->getMergeCells() as $range) {
            if (preg_match('/([A-Z]+)(\d+):([A-Z]+)(\d+)/', $range, $m)) {
                $row = (int) $m[2];
                if ($row >= $start && $row <= $end) {
                    $cache[$row]['merges'][] = $range;
                }
            }
        }
    }

    private function duplicateBlock(int $insertAt, array $cache, int $blockStart, int $blockEnd): int
    {
        if ($insertAt <= 0) {
            throw new \InvalidArgumentException("Invalid row for block duplication: $insertAt");
        }

        $blockSize = $blockEnd - $blockStart + 1;
        $this->sheet->insertNewRowBefore($insertAt, $blockSize);
        $offset = $insertAt - $blockStart;

        foreach ($cache as $templateRow => $data) {
            $targetRow = $insertAt + ($templateRow - $blockStart);

            if ($data['height'] !== -1) {
                $this->sheet->getRowDimension($targetRow)->setRowHeight($data['height']);
            }

            foreach ($data['values'] as $col => $value) {
                if ($value !== null && $value !== '') {
                    $this->sheet->setCellValue(
                        Coordinate::stringFromColumnIndex($col) . $targetRow,
                        $value
                    );
                }
            }

            foreach ($data['styles'] as $col => $style) {
                $this->sheet->getStyle(Coordinate::stringFromColumnIndex($col) . $targetRow)
                    ->applyFromArray($style);
            }

            foreach ($data['merges'] as $mergeRange) {
                if (preg_match('/([A-Z]+)(\d+):([A-Z]+)(\d+)/', $mergeRange, $m)) {
                    $this->sheet->mergeCells(
                        "{$m[1]}" . ($m[2] + $offset) . ":{$m[3]}" . ($m[4] + $offset)
                    );
                }
            }
        }

        return $insertAt;
    }


    // ==================== HELPERS ====================

    protected function bulkReplace(array $replacements): void
    {
        foreach ($this->sheet->getRowIterator(1, 200) as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);

            foreach ($cellIterator as $cell) {
                try {
                    $value = $cell->getValue();

                    if ($value instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                        $changed = false;
                        foreach ($value->getRichTextElements() as $element) {
                            $original = $element->getText();
                            $replaced = str_replace(
                                array_keys($replacements),
                                array_values($replacements),
                                $original
                            );
                            if ($replaced !== $original) {
                                $element->setText($replaced);
                                $changed = true;
                            }
                        }
                        if ($changed) {
                            $cell->setValue($value);
                        }
                        continue;
                    }

                    if (!is_string($value) || $value === '') {
                        continue;
                    }

                    $newValue = str_replace(
                        array_keys($replacements),
                        array_values($replacements),
                        $value
                    );

                    if ($newValue !== $value) {
                        $cell->setValue($newValue);
                    }
                } catch (\Exception) {
                    continue;
                }
            }
        }
    }

    private function saveFile(string $template, $spreadsheet): string
    {
        $prefix   = collect(RendererRegistry::$templateMap)->first(fn ($_, $k) => str_contains($template, $k)) ?? 'PR';
        $fileName = "{$prefix}_" . now()->format('Y-m-d_His') . '.xlsx';
        $path     = storage_path("app/public/pr-records/$fileName");

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        (new Xlsx($spreadsheet))->save($path);

        return $fileName;
    }
}
