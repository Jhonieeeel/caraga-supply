<?php

namespace App\Livewire\Forms\PurchaseRequest;

use Livewire\Form;

class AdminJanitorialDraftForm extends Form
{
    // ==================== DOCUMENT-LEVEL FIELDS ====================
    // Filled once — not per block

    public string $delivery_period = '';
    public string $delivery_site   = '';

    // ==================== BLOCK STATE ====================

    public array $blocks = [];

    // Edit state
    public array $editForm = [];
    public array $editing  = [
        'blockIndex' => null,
        'itemIndex'  => null,
        'group'      => null,   // 'administrative_items' | 'janitorial_items'
    ];

    // Add-item staging (one per group, shared across all blocks)
    public array $currentAdminItem      = [];
    public array $currentJanitorialItem = [];

    // ==================== DEFAULTS ====================

    public function initDefaults(): void
    {
        $this->delivery_period        = '';
        $this->delivery_site          = '';
        $this->blocks                 = [];
        $this->editForm               = [];
        $this->editing                = ['blockIndex' => null, 'itemIndex' => null, 'group' => null];
        $this->currentAdminItem       = $this->defaultItem();
        $this->currentJanitorialItem  = $this->defaultItem();
    }

    private function defaultItem(): array
    {
        return [
            'item_name'           => '',
            'quantity'            => 0,
            'unit'                => '',
            'estimated_unit_cost' => 0,
        ];
    }

    private function defaultBlock(): array
    {
        return [
            'block_title'          => '',
            'administrative_items' => [],
            'janitorial_items'     => [],
        ];
    }

    // ==================== BLOCK ACTIONS ====================

    public function addBlock(): void
    {
        $this->blocks[] = $this->defaultBlock();
    }

    public function removeBlock(int $blockIndex): void
    {
        array_splice($this->blocks, $blockIndex, 1);
    }

    // ==================== ITEM ACTIONS ====================

    public function addAdminItem(int $blockIndex): ?string
    {
        return $this->addItem($blockIndex, 'administrative_items', $this->currentAdminItem, function () {
            $this->currentAdminItem = $this->defaultItem();
        });
    }

    public function addJanitorialItem(int $blockIndex): ?string
    {
        return $this->addItem($blockIndex, 'janitorial_items', $this->currentJanitorialItem, function () {
            $this->currentJanitorialItem = $this->defaultItem();
        });
    }

    private function addItem(int $blockIndex, string $group, array $item, \Closure $reset): ?string
    {
        if (empty($item['item_name']))                return 'Item name is required.';
        if (($item['quantity'] ?? 0) <= 0)           return 'Quantity must be greater than 0.';
        if (empty($item['unit']))                     return 'Unit is required.';
        if (($item['estimated_unit_cost'] ?? 0) <= 0) return 'Estimated Unit Cost must be greater than 0.';

        $this->blocks[$blockIndex][$group][] = $item;
        $reset();

        return null;
    }

    public function removeItem(int $blockIndex, int $itemIndex, string $group): void
    {
        array_splice($this->blocks[$blockIndex][$group], $itemIndex, 1);
    }

    // ==================== EDIT ACTIONS ====================

    public function startEditItem(int $blockIndex, int $itemIndex, string $group): void
    {
        $this->editing  = ['blockIndex' => $blockIndex, 'itemIndex' => $itemIndex, 'group' => $group];
        $this->editForm = $this->blocks[$blockIndex][$group][$itemIndex];
    }

    public function saveEdit(): ?string
    {
        $item = $this->editForm;

        if (empty($item['item_name']))                return 'Item name is required.';
        if (($item['quantity'] ?? 0) <= 0)           return 'Quantity must be greater than 0.';
        if (empty($item['unit']))                     return 'Unit is required.';
        if (($item['estimated_unit_cost'] ?? 0) <= 0) return 'Estimated Unit Cost must be greater than 0.';

        $this->blocks[$this->editing['blockIndex']][$this->editing['group']][$this->editing['itemIndex']] = $item;
        $this->cancelEdit();

        return null;
    }

    public function cancelEdit(): void
    {
        $this->editing  = ['blockIndex' => null, 'itemIndex' => null, 'group' => null];
        $this->editForm = [];
    }

    // ==================== TOTALS ====================

    public function getBlockTotal(int $blockIndex): float
    {
        $block = $this->blocks[$blockIndex] ?? [];
        return $this->sumGroup($block['administrative_items'] ?? [])
             + $this->sumGroup($block['janitorial_items'] ?? []);
    }

    public function getGroupTotal(int $blockIndex, string $group): float
    {
        return $this->sumGroup($this->blocks[$blockIndex][$group] ?? []);
    }

    private function sumGroup(array $items): float
    {
        return array_reduce(
            $items,
            fn ($carry, $item) => $carry + (($item['quantity'] ?? 0) * ($item['estimated_unit_cost'] ?? 0)),
            0.0
        );
    }
}
