<?php

namespace App\Livewire\Forms\PurchaseRequest;

class ServiceDraftForm extends FlatDraftForm
{
    public string $type = 'service';
    public string $delivery_period = '';
    public string $delivery_site = '';
    public int $quantity = 0;
    public string $unit = '';
    public float $estimated_unit_cost = 0;
    public string $technical_specifications = '';

    // ==================== DEFAULTS ====================

    public function initDefaults(): void
    {
        $this->type = 'service';
        $this->delivery_period = '';
        $this->delivery_site = '';
        $this->quantity = 0;
        $this->unit = '';
        $this->estimated_unit_cost = 0;
        $this->technical_specifications = '';
    }

    // ==================== HYDRATION ====================

    public function fillFromDraft(array $data): void
    {
        $this->delivery_period = $data['delivery_period'] ?? '';
        $this->delivery_site = $data['delivery_site'] ?? '';
        $this->quantity = (int) ($data['quantity'] ?? 0);
        $this->unit = $data['unit'] ?? '';
        $this->estimated_unit_cost = (float) ($data['estimated_unit_cost'] ?? 0);
        $this->technical_specifications = $data['technical_specifications'] ?? '';
    }

    // ==================== COMPUTED ====================

    public function getEstimatedCost(): float
    {
        return $this->quantity * $this->estimated_unit_cost;
    }

    // ==================== VALIDATION ====================

    public function validateForm(): ?string
    {
        if (empty($this->delivery_period)) return 'Delivery Period is required.';
        if (empty($this->delivery_site)) return 'Delivery Site is required.';
        if ($this->quantity <= 0) return 'Quantity must be greater than 0.';
        if (empty($this->unit)) return 'Unit is required.';
        if ($this->estimated_unit_cost <= 0) return 'Estimated Unit Cost must be greater than 0.';
        if (empty($this->technical_specifications)) return 'Technical Specifications are required.';

        return null;
    }

    // ==================== TO ARRAY ====================

    public function toFormArray(): array
    {
        return [
            'type' => $this->type,
            'delivery_period' => $this->delivery_period,
            'delivery_site' => $this->delivery_site,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'estimated_unit_cost' => $this->estimated_unit_cost,
            'technical_specifications' => $this->technical_specifications,
        ];
    }
}
