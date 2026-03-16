<?php

namespace App\Livewire\Forms\PurchaseRequest;

use Livewire\Form;

/**
 * Base for flat (non-block) draft forms: ServiceDraftForm, TransportationDraftForm.
 *
 * Flat forms represent a single record per PR — no blocks array.
 * All flat forms must implement the four core methods below.
 * cancelEdit() is provided as a no-op default; override if your form has inline editing.
 */
abstract class FlatDraftForm extends Form
{
    /**
     * Reset all properties to their default/empty state.
     */
    abstract public function initDefaults(): void;

    /**
     * Hydrate form properties from a saved draft array.
     */
    abstract public function fillFromDraft(array $data): void;

    /**
     * Validate the form. Return an error string or null if valid.
     */
    abstract public function validateForm(): ?string;

    /**
     * Export the form state as a plain array for saving or rendering.
     * Named toFormArray() to avoid conflicting with Livewire\Form::toArray().
     */
    abstract public function toFormArray(): array;

    /**
     * Cancel any active inline edit. Override in forms that have editing state.
     */
    public function cancelEdit(): void
    {
        // no-op by default — override in TransportationDraftForm
    }

    /**
     * Save the active inline edit. Return an error string or null on success.
     * Override in forms that have inline item editing (e.g. TransportationDraftForm).
     */
    public function saveEdit(): ?string
    {
        return null; // no-op by default
    }
}
