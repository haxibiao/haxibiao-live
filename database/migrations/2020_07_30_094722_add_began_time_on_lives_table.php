<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBeganTimeOnLivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lives', function (Blueprint $table) {
            if (!Schema::hasColumn('lives', 'began_time')) {
                $table->timestamp('begen_time')->comment('直播开始时间')->nullable()->useCurrent();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
