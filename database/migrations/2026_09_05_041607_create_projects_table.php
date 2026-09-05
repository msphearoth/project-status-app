<?php

use App\Enums\ProjectStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_code')->unique();
            $table->string('work_code');
            $table->string('on_road');
            $table->string('start_road');
            $table->string('end_road');
            $table->string('pipe_type');
            $table->decimal('pipe_diameter', 10, 2);
            $table->decimal('pipe_length', 10, 2);
            $table->date('received_date');
            $table->decimal('project_amount', 14, 2)->nullable();
            $table->string('request_number')->nullable();
            $table->string('status')->default(ProjectStatus::Pending->value);
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
