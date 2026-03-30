<?php

namespace App\Services\Afms\Renderers;

use App\Services\Afms\Renderers\RendersSpreadsheetRows;
use App\Services\Afms\Renderers\BlockRenderer;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\RichText\RichText;

class AdminJanitorialBlockRenderer implements BlockRenderer
{
    use RendersSpreadsheetRows;

    public function __construct(
        private $sheet,
        private array $config,
        private \Closure $duplicator
    ) {}

    // ==================== MAIN ====================

    public function render(array $block, int $startRow, bool $insertNew = false): array
    {
        try {
            if ($insertNew) {
                $startRow = ($this->duplicator)($startRow);
            }

            $offset = $startRow - $this->config['block_start'];
            $this->fillBlockMarker('block_title', $block['block_title'] ?? '', $offset);
            $adminExtraRows = max(0, count($block['administrative_items'] ?? []) - 1);
            $janiExtraRows  = max(0, count($block['janitorial_items']      ?? []) - 1);
            $adminStartRow = $this->config['item_groups']['administrative_items']['start_row'] + $offset;
            $adminStyles   = $this->snapshotRowStyles($adminStartRow);
            $adminMerges   = $this->getRowMerges($adminStartRow);

            [$adminCursor, $adminTotal] = $this->renderGroup(
                $block['administrative_items'] ?? [],
                $adminStartRow,
                'administrative_items',
                $adminStyles,
                $adminMerges
            );
            $janiStartRow = $this->config['item_groups']['janitorial_items']['start_row'] + $offset + $adminExtraRows;
            $janiStyles   = $this->snapshotRowStyles($janiStartRow);
            $janiMerges   = $this->getRowMerges($janiStartRow);

            [$janiCursor, $janiTotal] = $this->renderGroup(
                $block['janitorial_items'] ?? [],
                $janiStartRow,
                'janitorial_items',
                $janiStyles,
                $janiMerges
            );

            $blockTotal = $adminTotal + $janiTotal;
            $nextBlockRow = ($startRow + ($this->config['block_end'] - $this->config['block_start'] + 1))
                          + $adminExtraRows
                          + $janiExtraRows;
            $this->fillBlockMarker('admin_total_cost', number_format($adminTotal, 2), $offset);
            $this->fillBlockMarkerWithShift('jani_total_cost', number_format($janiTotal, 2), $offset, $adminExtraRows);
            $this->fillBlockMarker('total_cost', number_format($blockTotal, 2), $offset);

            return [$nextBlockRow, $blockTotal];

        } catch (\Exception) {
            return [$startRow, 0];
        }
    }

    // ==================== BLOCK MARKER FILLING ====================

    private function fillBlockMarker(string $markerKey, string $value, int $offset): void
    {
        $cell = $this->config['block_markers'][$markerKey] ?? null;
        if (!$cell) return;

        preg_match('/([A-Z]+)(\d+)/', $cell, $m);
        $targetCell  = $m[1] . (((int) $m[2]) + $offset);
        $cellValue   = $this->sheet->getCell($targetCell)->getValue();
        $placeholder = '{{' . $markerKey . '}}';

        $this->writeCell($targetCell, $cellValue, $placeholder, $value);
    }

    private function fillBlockMarkerWithShift(string $markerKey, string $value, int $offset, int $shift): void
    {
        $cell = $this->config['block_markers'][$markerKey] ?? null;
        if (!$cell) return;

        preg_match('/([A-Z]+)(\d+)/', $cell, $m);
        $targetCell  = $m[1] . (((int) $m[2]) + $offset + $shift);
        $cellValue   = $this->sheet->getCell($targetCell)->getValue();
        $placeholder = '{{' . $markerKey . '}}';

        $this->writeCell($targetCell, $cellValue, $placeholder, $value);
    }

    // ==================== ITEM GROUP RENDERING ====================

    private function renderGroup(
        array  $items,
        int    $startRow,
        string $groupKey,
        array  $snapshotStyles = [],
        array  $snapshotMerges = []
    ): array {
        if (empty($items)) {
            return [$startRow, 0.0];
        }

        $templateRow = $startRow;
        $total       = 0.0;

        foreach ($items as $index => $item) {
            if ($index > 0) {
                $this->sheet->insertNewRowBefore($startRow, 1);
                $this->applyMergesToRow($snapshotMerges, $templateRow, $startRow);
            }

            $estimated              = ($item['quantity'] ?? 0) * ($item['estimated_unit_cost'] ?? 0);
            $item['estimated_cost'] = $estimated;
            $total                 += $estimated;

            $this->populateRow($startRow, $item, $groupKey);
            $this->applySnapshotStyles($snapshotStyles, $startRow);

            $startRow++;
        }

        return [$startRow, $total];
    }

    // ==================== STYLE SNAPSHOT ====================

    private function snapshotRowStyles(int $row): array
    {
        $colEnd = Coordinate::columnIndexFromString(
            $this->config['item_columns']['end'] ?? 'L'
        ) + 3;

        $styles = [];
        for ($col = 1; $col <= $colEnd; $col++) {
            $letter       = Coordinate::stringFromColumnIndex($col);
            $styles[$col] = $this->sheet->getStyle("{$letter}{$row}")->exportArray();
        }
        return $styles;
    }

    private function applySnapshotStyles(array $styles, int $targetRow): void
    {
        foreach ($styles as $col => $styleArray) {
            $letter = Coordinate::stringFromColumnIndex($col);
            $this->sheet->getStyle("{$letter}{$targetRow}")->applyFromArray($styleArray);
        }
    }

    // ==================== MERGE HELPERS ====================

    private function getRowMerges(int $row): array
    {
        $merges = [];
        foreach ($this->sheet->getMergeCells() as $range) {
            if (preg_match('/([A-Z]+)(\d+):([A-Z]+)(\d+)/', $range, $m)) {
                if ((int) $m[2] === $row) {
                    $merges[] = [
                        'startCol' => $m[1],
                        'endCol'   => $m[3],
                        'rowSpan'  => (int) $m[4] - (int) $m[2],
                    ];
                }
            }
        }
        return $merges;
    }

    private function applyMergesToRow(array $merges, int $templateRow, int $targetRow): void
    {
        foreach ($merges as $merge) {
            $start = $merge['startCol'] . $targetRow;
            $end   = $merge['endCol']   . ($targetRow + $merge['rowSpan']);
            $this->sheet->mergeCells("{$start}:{$end}");
        }
    }

    // ==================== CELL WRITE HELPER ====================

    private function writeCell(string $cell, mixed $cellValue, string $placeholder, string $replacement): void
    {
        if ($cellValue instanceof RichText) {
            foreach ($cellValue->getRichTextElements() as $element) {
                $element->setText(str_replace($placeholder, $replacement, $element->getText()));
            }
            $this->sheet->setCellValue($cell, $cellValue);
        } elseif (is_string($cellValue)) {
            $this->sheet->setCellValue($cell, str_replace($placeholder, $replacement, $cellValue));
        } else {
            $this->sheet->setCellValue($cell, $replacement);
        }
    }

    protected function populateRow(int $row, array $item, string $type): void
    {
        foreach ($this->config['fields_mapping'][$type] ?? [] as $col => $field) {
            $this->sheet->setCellValue("{$col}{$row}", $item[$field] ?? '');
        }
    }

    protected function calculateTotal(array $block): float
    {
        $total = 0.0;
        foreach (['administrative_items', 'janitorial_items'] as $group) {
            foreach ($block[$group] ?? [] as $item) {
                $total += ($item['quantity'] ?? 0) * ($item['estimated_unit_cost'] ?? 0);
            }
        }
        return $total;
    }
}
