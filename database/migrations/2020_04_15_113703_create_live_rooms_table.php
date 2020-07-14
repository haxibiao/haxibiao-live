<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLiveRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //部分APP已存在live_rooms表, 会导致art migrate报错, 因此引入package/live 首先需要使用 package/live 中的数据库迁移文件
        Schema::dropIfExists('live_rooms');

        Schema::create('live_rooms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->comment('主播id');
            $table->string('cover')->nullable();
            $table->tinyInteger('status')->default(0)->comment('-1:被封 0:正常');
            $table->unsignedTinyInteger('count_exception')->nullable()->comment('异常断流次数');
            $table->unsignedTinyInteger('type')->default(0)->comment('直播间类型: 普通房间（0）答题房间（1）你画我猜房间（2）...等等自定义类型');
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
        Schema::dropIfExists('live_rooms');
    }
}
