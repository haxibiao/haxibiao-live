<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLiveUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('live_users');
        Schema::create('live_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('live_id')->index()->comment('直播秀id');
            $table->unsignedInteger('user_id')->index()->comment('用户id');
            $table->unsignedInteger('duration')->default(0)->comment('用户加入直播间时长');
            $table->unsignedInteger('count_comments')->default(0)->comment('总评论数');
            $table->unsignedInteger('count_joins')->default(0)->comment('总加入次数');
            $table->unsignedInteger('count_leaves')->default(0)->comment('总离开次数');
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
        Schema::dropIfExists('live_users');
    }
}
