<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserLivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('user_lives')) {
            return;
        }
        Schema::create('user_lives', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->comment('主播 ID');
            $table->unsignedInteger('live_id')->comment('直播间 ID');
            $table->unsignedInteger('video_id')->comment('直播录制视频 ID')->nullable();
            $table->unsignedInteger('live_duration')->nullable()->comment('直播时长');
            $table->unsignedInteger('count_users')->nullable()->comment('总观看人数');
            $table->json('data')->nullable();

            $table->index(['user_id', 'live_id', 'live_duration', 'video_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_lives');
    }
}
