<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tokens', function (Blueprint $table) {
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
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tokens');
    }
}
