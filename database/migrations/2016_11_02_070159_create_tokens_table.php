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
        Schema::create('tokens', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('token')->index();
            $table->timestamp('expired_at')->nullable();
            $table->integer('usage_count')->default(0);
            $table->integer('max_usage_limit')->default(1);
            $table->mediumText('data')->nullable();
            $table->string('type')->nullable();
            $table->nullableMorphs('tokenable');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tokens');
    }
};
