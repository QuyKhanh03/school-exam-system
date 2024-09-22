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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('exam_id')->unsigned()->nullable();
            $table->bigInteger('subject_id')->unsigned()->nullable();
            $table->text('content');
            $table->enum('type',['single','multiple','input']);
            $table->boolean('is_group')->default(false);
            $table->bigInteger('parent_id')->unsigned()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
