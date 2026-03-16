<?php

namespace App\Livewire\Forms\PurchaseRequest;

use Livewire\Form;

class WorkbookDraftForm extends Form
{
    public array $blocks = [];

    public array $currentWorkbookItem = [
        'particular' => '',
        'delivery_date' => '',
        'qty' => 0,
        'unit' => '',
        'estimated_unit_cost' => 0,
    ];

    public array $editing = [
        'type' => null,
        'blockIndex' => null,
        'itemIndex' => null,
    ];

    public array $editForm = [];

    private array $blockDefault = [
        'type' => 'workbook',
        'block_title' => '',
        'items' => [],
    ];

    public function initDefaults(): void
    {
        $this->blocks = [$this->blockDefault];
        $this->cancelEdit();
        $this->resetCurrentWorkbookItem();
    }

    // ==================== BLOCK MANAGEMENT ====================

    public function addWorkbookBlock(): void
    {
        $this->cancelEdit();
        $this->blocks[] = $this->blockDefault;
    }

    public function removeBlock(int $blockIndex): void
    {
        $this->cancelEdit();

        if (count($this->blocks) > 1) {
            array_splice($this->blocks, $blockIndex, 1);
        }
    }

    // ==================== ITEM MANAGEMENT ====================

    public function addWorkbookItem(int $blockIndex): ?string
    {
        $this->cancelEdit();

        $required = ['particular', 'delivery_date', 'qty', 'unit', 'estimated_unit_cost'];

        if ($error = $this->validateFields($this->currentWorkbookItem, $required)) {
            return $error;
        }

        $this->blocks[$blockIndex]['items'][] = $this->currentWorkbookItem;
        $this->resetCurrentWorkbookItem();

        return null;
    }

    public function removeItem(int $blockIndex, int $itemIndex): void
    {
        $this->cancelEdit();
        array_splice($this->blocks[$blockIndex]['items'], $itemIndex, 1);
    }

    // ==================== EDITING ====================

    public function startEditWorkbookItem(int $blockIndex, int $itemIndex): void
    {
        $this->editing = [
            'type' => 'workbook',
            'blockIndex' => $blockIndex,
            'itemIndex' => $itemIndex,
        ];

        $this->editForm = $this->blocks[$blockIndex]['items'][$itemIndex] ?? [];
    }

    public function cancelEdit(): void
    {
        $this->editing = [
            'type' => null,
            'blockIndex' => null,
            'itemIndex' => null,
        ];

        $this->editForm = [];
    }

    public function saveEdit(): ?string
    {
        if ($this->editing['type'] !== 'workbook') {
            return null;
        }

        $required = ['particular', 'delivery_date', 'qty', 'unit', 'estimated_unit_cost'];

        if ($error = $this->validateFields($this->editForm, $required)) {
            return $error;
        }

        $b = $this->editing['blockIndex'];
        $i = $this->editing['itemIndex'];

        $this->blocks[$b]['items'][$i] = $this->editForm;
        $this->cancelEdit();

        return null;
    }

    // ==================== TOTALS ====================

    public function getBlockTotal(int $blockIndex): float
    {
        $block = $this->blocks[$blockIndex] ?? [];
        return $this->sumItems($block['items'] ?? []);
    }

    public function sumItemsPublic(array $items): float
    {
        return $this->sumItems($items);
    }

    // ==================== PRIVATE HELPERS ====================

    private function validateFields(array $data, array $fields): ?string
    {
        foreach ($fields as $field) {
            if (empty($data[$field]) && $data[$field] !== 0) {
                return ucfirst(str_replace('_', ' ', $field)) . ' is required!';
            }
        }

        return null;
    }

    private function resetCurrentWorkbookItem(): void
    {
        $this->currentWorkbookItem = [
            'particular' => '',
            'delivery_date' => '',
            'qty' => 0,
            'unit' => '',
            'estimated_unit_cost' => 0,
        ];
    }

    private function sumItems(array $items): float
    {
        return array_reduce(
            $items,
            fn ($carry, $item) => $carry + (($item['qty'] ?? 0) * ($item['estimated_unit_cost'] ?? 0)),
            0.0
        );
    }
}
