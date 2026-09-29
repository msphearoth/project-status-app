<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Exports\DashboardExport;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DashboardController extends Controller
{
    /**
     * The day ranges shown on the dashboard, keyed by bucket name.
     */
    private const BUCKETS = ['under5', 'between5and10', 'over10'];

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
     * Render the current in-progress projects behind one dashboard count
     * (a day bucket, optionally narrowed to a year and assignee) for the
     * drill-down popup.
     */
    public function projects(Request $request): View
    {
        $validated = $request->validate([
            'bucket' => ['required', Rule::in(self::BUCKETS)],
            'year' => ['nullable', 'integer'],
            'assignee_id' => ['nullable', 'integer'],
        ]);

        $projects = $this->inProgressProjects()
            ->when($validated['year'] ?? null, fn ($projects, $year) => $projects->where('year', $year))
            ->when($validated['assignee_id'] ?? null, fn ($projects, $assigneeId) => $projects->where('assignee_id', $assigneeId))
            ->filter(fn (Project $project) => $this->bucketFor($project) === $validated['bucket'])
            ->sortByDesc(fn (Project $project) => $this->daysSinceAssignment($project))
            ->values();

        return view('dashboard._projects', [
            'projects' => $projects,
            'daysSinceAssignment' => fn (Project $project) => $this->daysSinceAssignment($project),
        ]);
    }

    /**
     * Build the year > assignee > day-bucket breakdown of in-progress
     * projects shared by the dashboard view and its Excel export.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function buildRows(): Collection
    {
        $rows = $this->inProgressProjects()
            ->groupBy(fn (Project $project) => $project->year.'-'.$project->assignee_id)
            ->map(function ($projects) {
                $buckets = array_fill_keys(self::BUCKETS, 0);

                foreach ($projects as $project) {
                    $buckets[$this->bucketFor($project)]++;
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

    /**
     * @return EloquentCollection<int, Project>
     */
    private function inProgressProjects(): EloquentCollection
    {
        return Project::query()
            ->where('status', ProjectStatus::InProgress)
            ->with(['assignee', 'latestAssignmentLog'])
            ->get();
    }

    /**
     * Whole days since the project was last handed to its assignee.
     */
    private function daysSinceAssignment(Project $project): int
    {
        $assignedAt = $project->latestAssignmentLog?->assignedDate() ?? $project->updated_at;

        return (int) Carbon::parse($assignedAt)->diffInDays(now());
    }

    /**
     * The dashboard day bucket the project falls into.
     */
    private function bucketFor(Project $project): string
    {
        $days = $this->daysSinceAssignment($project);

        return match (true) {
            $days < 5 => 'under5',
            $days < 10 => 'between5and10',
            default => 'over10',
        };
    }
}
