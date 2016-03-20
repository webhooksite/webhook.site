<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBasicTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tokens', function (Blueprint $table) {
            $table->string('uuid', 36)->unique();
            $table->timestamps();
            $table->softDeletes();

            $table->string('ip');
            $table->string('user_agent');
            $table->string('default_content')->default('');
            $table->string('default_content_type')->default('application/json');
            $table->integer('default_status', false, true)->default(200);
        });

        Schema::create('requests', function (Blueprint $table) {
            $table->string('uuid', 36)->unique();
            $table->timestamps();
            $table->softDeletes();

            $table->string('url');
            $table->string('method');
            $table->string('ip');
            $table->string('hostname');
            $table->string('user_agent');
            $table->longText('content');
            $table->text('headers');
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
        Schema::dropIfExists('requests');
    }
}
