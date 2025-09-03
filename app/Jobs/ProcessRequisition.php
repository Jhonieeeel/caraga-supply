<?php

namespace App\Jobs;

use App\Models\Requisition;
use App\Services\Afms\ConvertRisService;
use App\Services\Afms\GenerateRisService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;

class ProcessRequisition implements ShouldQueue
{
    use  Queueable, SerializesModels;

    public function __construct(
        public int $requisitionId
    ) {}

    public function handle(GenerateRisService $word, ConvertRisService $pdf): void
    {
        $requisition = Requisition::findOrFail($this->requisitionId);

        $generatedWord = $word->handle($requisition);
        $pdf->handle($generatedWord, $requisition);
    }
}
