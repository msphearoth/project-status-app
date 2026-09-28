<?php

namespace App\Models;

use App\Enums\ProjectAssignmentAction;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable(['project_id', 'action', 'assigned_by', 'assigned_from', 'assigned_to', 'note', 'assigned_on'])]
class ProjectAssignmentLog extends Model
{
    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'action' => ProjectAssignmentAction::class,
            'assigned_on' => 'date',
            'created_at' => 'datetime',
        ];
    }

    /**
     * The date the project was handed over, falling back to when the log was recorded.
     */
    public function assignedDate(): ?Carbon
    {
        return $this->assigned_on ?? $this->created_at;
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * The assignee the project was taken from when it was reassigned.
     *
     * @return BelongsTo<User, $this>
     */
    public function assignedFrom(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_from');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
