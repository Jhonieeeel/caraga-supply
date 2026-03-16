<?php

namespace App\Services\Afms\Renderers;

use App\Services\Afms\Renderers\RendersSpreadsheetRows;
use App\Services\Afms\Renderers\BlockRenderer;
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

            // Fill block_title
            $this->fillBlockMarker('block_title', $block['block_title'] ?? '', $offset);

            // Render each item group, get per-group totals
            $adminTotal = $this->renderGroup(
                $block['administrative_items'] ?? [],
                $this->config['item_groups']['administrative_items']['start_row'] + $offset,
                'administrative_items'
            );

            $janiTotal = $this->renderGroup(
                $block['janitorial_items'] ?? [],
                $this->config['item_groups']['janitorial_items']['start_row'] + $offset,
                'janitorial_items'
            );

            $blockTotal = $adminTotal + $janiTotal;

            // Write per-group subtotals and block total
            $this->fillBlockMarker('admin_total_cost', number_format($adminTotal, 2), $offset);
            $this->fillBlockMarker('jani_total_cost',  number_format($janiTotal, 2),  $offset);
            $this->fillBlockMarker('total_cost',       number_format($blockTotal, 2), $offset);

            $nextBlockRow = $startRow + ($this->config['block_end'] - $this->config['block_start'] + 1);

            return [$nextBlockRow, $blockTotal];
        } catch (\Exception) {
            return [$startRow, 0];
        }
    }

    // ==================== BLOCK MARKER FILLING ====================
    // Resolves a block_markers cell ref, applies row offset, writes value

    private function fillBlockMarker(string $markerKey, string $value, int $offset): void
    {
        $cell = $this->config['block_markers'][$markerKey] ?? null;
        if (!$cell) return;

        preg_match('/([A-Z]+)(\d+)/', $cell, $m);
        $targetCell = $m[1] . (((int) $m[2]) + $offset);
        $cellValue  = $this->sheet->getCell($targetCell)->getValue();
        $placeholder = '{{' . $markerKey . '}}';

        $this->writeCell($targetCell, $cellValue, $placeholder, $value);
    }

    // ==================== ITEM GROUP RENDERING ====================

    private function renderGroup(array $items, int $startRow, string $groupKey): float
    {
        if (empty($items)) {
            return 0.0;
        }

        $templateRow = $startRow;
        $total       = 0.0;

        foreach ($items as $index => $item) {
            if ($index > 0) {
                $this->sheet->insertNewRowBefore($startRow, 1);
                $this->cloneRow($templateRow, $startRow);
            }

            $estimated              = ($item['quantity'] ?? 0) * ($item['estimated_unit_cost'] ?? 0);
            $item['estimated_cost'] = $estimated;
            $total                 += $estimated;

            $this->populateRow($startRow, $item, $groupKey);
            $startRow++;
        }

        return $total;
    }

    // ==================== HELPERS ====================

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
