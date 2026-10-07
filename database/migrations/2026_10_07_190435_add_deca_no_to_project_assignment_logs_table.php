<?php

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
        Schema::table('project_assignment_logs', function (Blueprint $table) {
            $table->string('deca_no', 50)->nullable()->after('note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_assignment_logs', function (Blueprint $table) {
            $table->dropColumn('deca_no');
        });
    }
};
