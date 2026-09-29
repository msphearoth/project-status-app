<?php

namespace App\Exports;

use App\Models\Project;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Exports the full project list - every project field plus the current
 * assignee's name and the date of their latest assignment.
 */
class ProjectsExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
{
    /**
     * @param  Collection<int, Project>  $projects
     */
    public function __construct(private readonly Collection $projects) {}

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            __('Project Code'),
            __('Year'),
            __('Work Code'),
            __('Deca No.'),
            __('On Road'),
            __('Start Road'),
            __('End Road'),
            __('Pipe Type'),
            __('Pipe Diameter'),
            __('Pipe Length'),
            __('Received Date'),
            __('Status'),
            __('Assignee'),
            __('Assigned On'),
            __('Project Amount'),
            __('Request Number'),
            __('Created By'),
            __('Created At'),
            __('Completed At'),
        ];
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        return $this->projects->map(fn (Project $project) => [
            $project->project_code,
            $project->year,
            $project->work_code,
            $project->deca_no ?? '',
            $project->on_road,
            $project->start_road,
            $project->end_road,
            $project->pipe_type,
            (float) $project->pipe_diameter,
            (float) $project->pipe_length,
            $project->received_date->format('Y-m-d'),
            $project->status->label(),
            $project->assignee?->name ?? __('Unassigned'),
            $project->assignee_id ? $project->latestAssignmentLog?->assignedDate()?->format('Y-m-d') ?? '' : '',
            $project->project_amount !== null ? (float) $project->project_amount : '',
            $project->request_number ?? '',
            $project->creator?->name,
            $project->created_at->format('Y-m-d H:i'),
            $project->completed_at?->format('Y-m-d H:i') ?? '',
        ])->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
