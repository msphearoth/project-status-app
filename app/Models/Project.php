<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'project_code',
    'year',
    'work_code',
    'on_road',
    'start_road',
    'end_road',
    'pipe_type',
    'pipe_diameter',
    'pipe_length',
    'received_date',
    'project_amount',
    'request_number',
    'status',
    'assignee_id',
    'created_by',
    'completed_at',
])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'pipe_diameter' => 'decimal:2',
            'pipe_length' => 'decimal:2',
            'project_amount' => 'decimal:2',
            'received_date' => 'date',
            'status' => ProjectStatus::class,
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<ProjectAssignmentLog, $this>
     */
    public function assignmentLogs(): HasMany
    {
        return $this->hasMany(ProjectAssignmentLog::class)->latest('created_at');
    }

    /**
     * The most recent assignment/reassignment/completion log entry, used to
     * determine when the current assignee was assigned.
     *
     * @return HasOne<ProjectAssignmentLog, $this>
     */
    public function latestAssignmentLog(): HasOne
    {
        return $this->hasOne(ProjectAssignmentLog::class)->latestOfMany('created_at');
    }
}
