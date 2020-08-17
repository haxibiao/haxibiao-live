<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCamerasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('cameras')) {
            return;
        }
        Schema::create('cameras', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('store_id')->index()->comment('商户 ID');
            $table->unsignedInteger('user_id')->index()->comment('用户 ID');//冗余
            $table->string('title')->index()->nullable()->comment('实况标题');
            $table->string('description')->nullable()->comment('描述');

            /**
             * ["*"]            所有用户可见
             * []               仅自己可见
             * ["1","2","3"]    数组中的人可见
             */
            $table->string('uids')->default('["*"]')->comment('json_encode所有在组内的用户id');
            $table->tinyInteger('status')->default(0)->comment('状态:0默认,-1关闭,1推荐');
            $table->string('cover')->nullable()->comment('直播的截图，回调自动更新');
            //开播
            $table->string('push_stream_url')->nullable()->comment('推流地址');
            $table->string('push_stream_key')->nullable()->comment('鉴权密钥');
            $table->string('pull_stream_url')->nullable()->comment('拉流地址');
            $table->string('stream_name')->index()->nullable()->comment('流名称');

            $table->json('data')->nullable();

            $table->boolean('is_push_stream')->default(false)
                ->comment('是否在推流中');

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
        Schema::dropIfExists('cameras');
    }
}
