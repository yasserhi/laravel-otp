<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTestTable extends Migration
{
    public function up(): void
    {
        Schema::create('users', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name')->nullable()->default(null);
            $table->string('email');
            $table->string('mobile');
        });
    }

    public function down(): void
    {
        Schema::drop('users');
    }
}
