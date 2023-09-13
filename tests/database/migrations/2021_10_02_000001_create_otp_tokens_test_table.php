<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtpTokensTestTable extends Migration
{
    public function up(): void
    {
        Schema::create('otp_tokens', static function (Blueprint $table): void {
            $table->morphs("authenticable");
            $table->string('token', 10);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            // $table->unique(['authenticable_id', 'authenticable_type']);
        });
    }

    public function down(): void
    {
        Schema::drop('otp_tokens');
    }
}
