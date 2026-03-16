<?php

namespace App\Services\Afms\Renderers;

use App\Services\Afms\Renderers\RendersSpreadsheetRows;
use App\Services\Afms\Renderers\BlockRenderer;
use PhpOffice\PhpSpreadsheet\RichText\RichText;

class TransportationBlockRenderer implements BlockRenderer
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
            $this->fillMarkers($block);

            $total   = $this->calculateTotal($block);
            $lastRow = $this->renderItems($block['items'] ?? []);

            return [$lastRow, $total];
        } catch (\Exception) {
            return [$this->config['items_start_row'] ?? $startRow, 0];
        }
    }

    // ==================== MARKER FILLING ====================

    private function fillMarkers(array $block): void
    {
        foreach ($this->config['markers'] as $field => $cell) {
            $placeholder = '{{' . $field . '}}';
            $replacement = (string) ($block[$field] ?? '');

            $value = $this->sheet->getCell($cell)->getValue();

            if ($value instanceof RichText) {
                // Preserve formatting — replace within each RichText element
                foreach ($value->getRichTextElements() as $element) {
                    $element->setText(str_replace($placeholder, $replacement, $element->getText()));
                }
                $this->sheet->setCellValue($cell, $value);
            } elseif (is_string($value)) {
                $this->sheet->setCellValue($cell, str_replace($placeholder, $replacement, $value));
            }
        }
    }

    // ==================== ITEM RENDERING ====================

    private function renderItems(array $items): int
    {
        if (empty($items)) {
            return $this->config['items_start_row'];
        }

        $startRow = $this->config['items_start_row'];
        $templateRow = $startRow;

        foreach ($items as $index => $item) {
            if ($index > 0) {
                $this->sheet->insertNewRowBefore($startRow, 1);
                $this->cloneRow($templateRow, $startRow);
            }

            $this->populateRow($startRow, $item, 'transportation');
            $startRow++;
        }

        return $startRow;
    }

    // ==================== OVERRIDES ====================

    protected function populateRow(int $row, array $item, string $type): void
    {
        foreach ($this->config['fields_mapping'][$type] ?? [] as $col => $field) {
            $value = match ($field) {
                'calculated_cost' => ($item['no_of_vehicles'] ?? 0) * ($item['estimated_unit_cost'] ?? 0),
                default           => $item[$field] ?? 0,
            };

            $this->sheet->setCellValue("{$col}{$row}", $value);
        }
    }

    protected function calculateTotal(array $block): float
    {
        return array_reduce(
            $block['items'] ?? [],
            fn ($carry, $item) => $carry + (($item['no_of_vehicles'] ?? 0) * ($item['estimated_unit_cost'] ?? 0)),
            0.0
        );
    }
}
