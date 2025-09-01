<?php

namespace App\Jobs;

use App\Models\Requisition;
use App\Services\Afms\ConvertRisService;
use App\Services\Afms\GenerateRisService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessRequisition implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Requisition $requisition
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(GenerateRisService $word, ConvertRisService $pdf): void
    {
        $generatedWord = $word->handle($this->requisition);
        $pdf->handle($generatedWord, $this->requisition);
    }
}
