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
    private array $templateRowCache = [];

    // ==================== MAIN ENTRY POINT ====================

    public function handle(string $template, array $blocks, $sourceRecord): string
    {
        $spreadsheet  = IOFactory::load(public_path("templates/$template"));
        $this->sheet  = $spreadsheet->getActiveSheet();
        $this->config = $this->loadTemplateConfig($template);

        $this->cacheTemplateRows();
        $this->fillCommonFields($sourceRecord);

        // Fill document-level markers (e.g. delivery_period, delivery_site) that appear
        // outside the block range — read once from the first block's injected values.
        if (!empty($this->config['document_markers']) && !empty($blocks[0])) {
            $replacements = [];
            foreach ($this->config['document_markers'] as $field => $cells) {
                $value = (string) ($blocks[0][$field] ?? '');
                foreach ((array) $cells as $cell) {
                    $cellValue = $this->sheet->getCell($cell)->getValue();
                    if (is_string($cellValue) && str_contains($cellValue, '{{' . $field . '}}')) {
                        $this->sheet->setCellValue($cell, str_replace('{{' . $field . '}}', $value, $cellValue));
                    }
                }
            }
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

    // ==================== ABSTRACT: subclasses must implement ====================

    /**
     * Fill fields that differ per document type (PR vs PO vs future types).
     * Called once per handle() after cacheTemplateRows().
     */
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

        // Guard: every config must declare placeholders
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
            sheet:      $this->sheet,
            config:     $this->config,
            duplicator: fn (int $row) => $this->duplicateTemplateBlock($row)
        );
    }

    // ==================== TEMPLATE CACHING ====================

    private function cacheTemplateRows(): void
    {
        if (!isset($this->config['block_start'], $this->config['block_end'])) {
            return;
        }

        $start  = $this->config['block_start'];
        $end    = $this->config['block_end'];
        $colEnd = Coordinate::columnIndexFromString($this->config['item_columns']['end'] ?? 'M');

        for ($row = $start; $row <= $end; $row++) {
            $styles = [];
            $values = [];

            foreach (range(1, $colEnd + 5) as $col) {
                $colLetter    = Coordinate::stringFromColumnIndex($col);
                $cellRef      = $colLetter . $row;
                $styles[$col] = $this->sheet->getStyle($cellRef)->exportArray();
                // Cache value so header rows ("Administrative Supplies" etc.) are reproduced on duplication
                $values[$col] = $this->sheet->getCell($cellRef)->getValue();
            }

            $this->templateRowCache[$row] = [
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
                    $this->templateRowCache[$row]['merges'][] = $range;
                }
            }
        }
    }

    private function duplicateTemplateBlock(int $insertAt): int
    {
        if ($insertAt <= 0) {
            throw new \InvalidArgumentException("Invalid row for block duplication: $insertAt");
        }

        $blockSize = $this->config['block_end'] - $this->config['block_start'] + 1;
        $this->sheet->insertNewRowBefore($insertAt, $blockSize);
        $offset = $insertAt - $this->config['block_start'];

        foreach ($this->templateRowCache as $templateRow => $cache) {
            $targetRow = $insertAt + ($templateRow - $this->config['block_start']);

            if ($cache['height'] !== -1) {
                $this->sheet->getRowDimension($targetRow)->setRowHeight($cache['height']);
            }

            // Write cached cell values (restores static header text like "Administrative Supplies")
            foreach ($cache['values'] as $col => $value) {
                if ($value !== null && $value !== '') {
                    $this->sheet->setCellValue(
                        Coordinate::stringFromColumnIndex($col) . $targetRow,
                        $value
                    );
                }
            }

            foreach ($cache['styles'] as $col => $style) {
                $this->sheet->getStyle(Coordinate::stringFromColumnIndex($col) . $targetRow)
                    ->applyFromArray($style);
            }

            foreach ($cache['merges'] as $mergeRange) {
                if (preg_match('/([A-Z]+)(\d+):([A-Z]+)(\d+)/', $mergeRange, $m)) {
                    $this->sheet->mergeCells("{$m[1]}" . ($m[2] + $offset) . ":{$m[3]}" . ($m[4] + $offset));
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
