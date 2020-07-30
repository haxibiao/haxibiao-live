<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        //FIXME: 重构改动太大，线上价值太小，无需兼容了
        Schema::dropIfExists('user_lives');
        Schema::dropIfExists('lives');

        Schema::create('lives', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->index()->nullable()->comment('直播秀标题');
            $table->unsignedInteger('user_id')->index()->comment('主播 ID');
            $table->unsignedInteger('live_room_id')->index()->comment('直播间 ID');
            $table->tinyInteger('status')->default(0)->comment('状态:0默认,-1关闭,1推荐');
            $table->string('cover')->nullable()->comment('直播的截图，回调自动更新');
            //开播
            $table->string('push_stream_url')->nullable()->comment('推流地址');
            $table->string('push_stream_key')->nullable()->comment('鉴权密钥');
            $table->string('pull_stream_url')->nullable()->comment('拉流地址');
            $table->string('stream_name')->index()->nullable()->comment('流名称');
            $table->timestamp('begen_time')->comment('直播开始时间')->useCurrent();
            //回放
            $table->unsignedInteger('video_id')->index()->nullable()->comment('直播录制视频 ID');
            $table->unsignedInteger('duration')->nullable()->comment('直播时长');

            $table->unsignedInteger('count_users')->nullable()->comment('总观看人数');
            $table->unsignedInteger('count_comments')->nullable()->comment('总观看人数');
            $table->json('data')->nullable();

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
        Schema::dropIfExists('lives');

    }
}
