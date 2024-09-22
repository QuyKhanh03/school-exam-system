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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->string('role_name')->default('user');
            $table->date('dob')->nullable();
            $table->tinyInteger('gender')->nullable();
            $table->string('address')->nullable();
            $table->string('facebookurl')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('workplace',500)->nullable();
            $table->string('user_code',16)->nullable();
            $table->integer('school_id')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->text('noted')->nullable();
            $table->integer('province_id')->nullable();
            $table->string('username')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->unsignedSmallInteger('published')->default(0);
            $table->dateTime('published_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
