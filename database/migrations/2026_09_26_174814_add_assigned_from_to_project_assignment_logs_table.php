<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('project_assignment_logs', function (Blueprint $table) {
            $table->foreignId('assigned_from')->nullable()->after('assigned_by')->constrained('users')->nullOnDelete();
        });

        DB::table('project_assignment_logs')
            ->orderBy('project_id')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get(['id', 'project_id', 'action', 'assigned_to'])
            ->groupBy('project_id')
            ->each(function ($logs) {
                $previousAssignee = null;

                foreach ($logs as $log) {
                    if ($log->action === 'REASSIGNED' && $previousAssignee !== null) {
                        DB::table('project_assignment_logs')->where('id', $log->id)->update(['assigned_from' => $previousAssignee]);
                    }

                    if (in_array($log->action, ['ASSIGNED', 'REASSIGNED'], true)) {
                        $previousAssignee = $log->assigned_to;
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_assignment_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_from');
        });
    }
};
