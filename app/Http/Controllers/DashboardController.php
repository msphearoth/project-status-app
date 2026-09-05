<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard: in-progress projects grouped by their current
     * assignee, broken down by how many days it's been since they were
     * assigned.
     */
    public function index(): View
    {
        $rows = Project::query()
            ->where('status', ProjectStatus::InProgress)
            ->with(['assignee', 'latestAssignmentLog'])
            ->get()
            ->groupBy('assignee_id')
            ->map(function ($projects) {
                $buckets = ['under5' => 0, 'between5and10' => 0, 'over10' => 0];

                foreach ($projects as $project) {
                    $assignedAt = $project->latestAssignmentLog?->created_at ?? $project->updated_at;
                    $days = Carbon::parse($assignedAt)->diffInDays(now());

                    $buckets[match (true) {
                        $days < 5 => 'under5',
                        $days < 10 => 'between5and10',
                        default => 'over10',
                    }]++;
                }

                return [
                    'assignee' => $projects->first()->assignee,
                    ...$buckets,
                    'total' => $projects->count(),
                ];
            })
            ->sortBy(fn (array $row) => $row['assignee']?->name)
            ->values();

        return view('dashboard', [
            'rows' => $rows,
        ]);
    }
}
