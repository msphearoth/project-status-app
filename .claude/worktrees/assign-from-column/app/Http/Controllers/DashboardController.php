<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Exports\DashboardExport;
use App\Models\Project;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DashboardController extends Controller
{
    /**
     * Display the dashboard: in-progress projects grouped by year and then
     * by their current assignee, broken down by how many days it's been
     * since they were assigned.
     */
    public function index(): View
    {
        return view('dashboard', [
            'rows' => $this->buildRows(),
        ]);
    }

    /**
     * Download the same dashboard data as an Excel spreadsheet.
     */
    public function export(): BinaryFileResponse
    {
        $filename = 'dashboard-'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(new DashboardExport($this->buildRows()), $filename);
    }

    /**
     * Build the year > assignee > day-bucket breakdown of in-progress
     * projects shared by the dashboard view and its Excel export.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function buildRows(): Collection
    {
        $rows = Project::query()
            ->where('status', ProjectStatus::InProgress)
            ->with(['assignee', 'latestAssignmentLog'])
            ->get()
            ->groupBy(fn (Project $project) => $project->year.'-'.$project->assignee_id)
            ->map(function ($projects) {
                $buckets = ['under5' => 0, 'between5and10' => 0, 'over10' => 0];

                foreach ($projects as $project) {
                    $assignedAt = $project->latestAssignmentLog?->assignedDate() ?? $project->updated_at;
                    $days = Carbon::parse($assignedAt)->diffInDays(now());

                    $buckets[match (true) {
                        $days < 5 => 'under5',
                        $days < 10 => 'between5and10',
                        default => 'over10',
                    }]++;
                }

                return [
                    'year' => $projects->first()->year,
                    'assignee' => $projects->first()->assignee,
                    ...$buckets,
                    'total' => $projects->count(),
                ];
            })
            ->sortBy(fn (array $row) => $row['assignee']?->name)
            ->sortByDesc('year')
            ->values();

        $rowsPerYear = $rows->countBy('year');
        $seenYears = [];

        return $rows->map(function (array $row) use ($rowsPerYear, &$seenYears) {
            $isFirstOfYear = ! isset($seenYears[$row['year']]);
            $seenYears[$row['year']] = true;

            $row['yearRowspan'] = $isFirstOfYear ? $rowsPerYear[$row['year']] : null;

            return $row;
        });
    }
}
