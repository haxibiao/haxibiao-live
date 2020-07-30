<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLiveActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('live_actions');
        Schema::create('live_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('live_id')->index()->comment('直播秀id');
            $table->unsignedInteger('user_id')->index()->comment('用户id');
            $table->nullableMorphs('actionable');
            $table->unsignedInteger('action_at')->comment('行为发生时间（秒）');
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
        Schema::dropIfExists('live_actions');
    }
}
