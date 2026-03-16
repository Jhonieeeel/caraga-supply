<?php

namespace App\Services\Afms\Renderers;


use App\Services\Afms\Renderers\BlockRenderer;
use PhpOffice\PhpSpreadsheet\RichText\RichText;

class ServiceBlockRenderer implements BlockRenderer
{
    public function __construct(
        private $sheet,
        private array $config,
        private \Closure $duplicator
    ) {}

    // ==================== MAIN ====================

    public function render(array $block, int $startRow, bool $insertNew = false): array
    {
        try {
            $estimatedCost = ($block['quantity'] ?? 0) * ($block['estimated_unit_cost'] ?? 0);

            $this->fillMarkers(array_merge($block, [
                'estimated_cost' => number_format($estimatedCost, 2),
                'overall_cost' => number_format($estimatedCost, 2),
            ]));

            return [0, $estimatedCost];
        } catch (\Exception $e) {
            return [0, 0];
        }
    }

    // ==================== MARKER FILLING ====================

    private function fillMarkers(array $block): void
    {
        foreach ($this->config['markers'] as $field => $cell) {
            $value   = $this->getCellValue($cell);
            $replace = (string) ($block[$field] ?? '');

            $this->sheet->setCellValue(
                $cell,
                str_replace('{{' . $field . '}}', $replace, $value)
            );
        }
    }

    // ==================== RICH TEXT SAFE READ ====================

    private function getCellValue(string $cell): string
    {
        $value = $this->sheet->getCell($cell)->getValue();

        if ($value instanceof RichText) {
            return $value->getPlainText();
        }

        return is_string($value) ? $value : '';
    }
}
