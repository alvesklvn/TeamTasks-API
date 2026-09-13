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
        Schema::table('project_user', function (Blueprint $table){
            $table->datetime('joined_at')->nullable()->change();
            $table->enum('status', ['invited', 'joined']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_user', function (Blueprint $table){
            $table->date('joined_at')->change();
            $table->dropColumn('status');
        });        
    }
};
