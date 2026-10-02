<?php

namespace App\Actions\Procurement;

use App\Models\Procurement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use League\Csv\Reader;

/**
 * Imports an APP exported to CSV from the GPPB APP template (RA 12009):
 *
 *  [0] PAP Code              [5] Gen. Description (Procurement)  [10] End of Procurement Activity
 *  [1] Object Code           [6] Mode of Procurement             [11] Source of Fund
 *  [2] Project Title         [7] Early Procurement Activity?     [12] Estimated Budget / ABC (PhP)
 *  [3] End-User              [8] Criteria for Bid Evaluation     [14] Remarks
 *  [4] Gen. Description      [9] Start of Procurement Activity
 *
 * Only project rows are imported (title, end-user and a numeric budget); section
 * headings, totals and signatories are skipped. Rows already in the database for
 * the same APP year, title and end-user are updated instead of duplicated. Codes
 * are generated as APP-{year}-{nnn}, since a PAP code is shared by every project
 * under the same program.
 */
class ImportAppCsv
{
    /**
     * @return array{year: int, created: int, updated: int, total_budget: float}
     */
    public function handle(string $path): array
    {
        $records = iterator_to_array(Reader::from($path, 'r')->getRecords(), false);

        $header = array_map(fn ($cell) => $this->clean($cell), $records[0] ?? []);

        if (! str_contains(strtolower($header[2] ?? ''), 'project title')
            || ! str_contains(strtolower($header[3] ?? ''), 'end-user')
            || ! str_contains(strtolower($header[12] ?? ''), 'budget')) {
            throw new InvalidArgumentException(
                'This file is not in the GPPB APP format. Expected "Project Title" in column C, "End-User" in column D and "Estimated Budget" in column M.'
            );
        }

        $projects = array_values(array_filter(array_map(fn ($row) => $this->toProject($row), array_slice($records, 1))));

        if ($projects === []) {
            throw new InvalidArgumentException('No project rows were found in this file.');
        }

        $year = $this->appYear($projects);

        return DB::transaction(function () use ($projects, $year) {
            $created = 0;
            $updated = 0;
            $sequence = $this->lastSequence($year);

            foreach ($projects as $project) {
                $attributes = [
                    'project_title' => $project['project_title'],
                    'pmo_end_user' => $project['pmo_end_user'],
                    'early_activity' => $project['early_activity'],
                    'mode_of_procurement' => $project['mode_of_procurement'],
                    'bid_evaluation_criteria' => $project['bid_evaluation_criteria'],
                    'procurement_start' => $project['procurement_start'],
                    'procurement_end' => $project['procurement_end'],
                    'source_of_funds' => $project['source_of_funds'],
                    'estimated_budget_total' => $project['budget'],
                    'total_abc' => $project['budget'],
                    'remarks' => $project['remarks'],
                    'app_year' => $year,
                ];

                $existing = Procurement::where('app_year', $year)
                    ->where('project_title', $project['project_title'])
                    ->where('pmo_end_user', $project['pmo_end_user'])
                    ->first();

                if ($existing) {
                    // keep remaining_budget in step with a revised ABC
                    if (! is_null($existing->remaining_budget) && ! is_null($existing->total_abc)) {
                        $attributes['remaining_budget'] = (float) $existing->remaining_budget
                            + ($project['budget'] - (float) $existing->total_abc);
                    }

                    $existing->update($attributes);
                    $updated++;

                    continue;
                }

                Procurement::create(['code' => sprintf('APP-%d-%03d', $year, ++$sequence)] + $attributes);
                $created++;
            }

            return [
                'year' => $year,
                'created' => $created,
                'updated' => $updated,
                'total_budget' => array_sum(array_column($projects, 'budget')),
            ];
        });
    }

    private function toProject(array $row): ?array
    {
        $row = array_map(fn ($cell) => $this->clean($cell), $row);

        $title = $row[2] ?? '';
        $endUser = $row[3] ?? '';
        $budget = str_replace(',', '', $row[12] ?? '');

        if ($title === '' || $endUser === '' || ! is_numeric($budget)) {
            return null;
        }

        $remarks = $row[14] ?? '';

        return [
            'project_title' => $title,
            'pmo_end_user' => $endUser,
            'mode_of_procurement' => $row[6] ?? null,
            'early_activity' => strtolower($row[7] ?? '') ?: null,
            'bid_evaluation_criteria' => ($row[8] ?? '') ?: null,
            'procurement_start' => ($row[9] ?? '') ?: null,
            'procurement_end' => ($row[10] ?? '') ?: null,
            'source_of_funds' => ($row[11] ?? '') ?: null,
            'budget' => (float) $budget,
            'remarks' => ($remarks === '' || strtoupper($remarks) === 'N/A') ? null : $remarks,
        ];
    }

    /**
     * The APP year is the year most projects end in (early procurement
     * activities can start in the previous year).
     */
    private function appYear(array $projects): int
    {
        $years = [];

        foreach ($projects as $project) {
            if (preg_match_all('/\b(20\d{2})\b/', (string) $project['procurement_end'], $matches)) {
                $year = max($matches[1]);
                $years[$year] = ($years[$year] ?? 0) + 1;
            }
        }

        if ($years === []) {
            return (int) now()->year;
        }

        arsort($years);

        return (int) array_key_first($years);
    }

    private function lastSequence(int $year): int
    {
        return (int) Procurement::where('code', 'like', "APP-{$year}-%")
            ->pluck('code')
            ->map(fn ($code) => (int) substr($code, strlen("APP-{$year}-")))
            ->max();
    }

    /**
     * Trim and collapse the line breaks Excel leaves inside wrapped cells.
     */
    private function clean(?string $value): string
    {
        $value = preg_replace('/^\xEF\xBB\xBF/', '', (string) $value);

        return trim(preg_replace('/\s+/u', ' ', $value));
    }
}
