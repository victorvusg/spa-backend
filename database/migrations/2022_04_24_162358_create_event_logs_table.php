<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Constants\EventLog;

class CreateEventLogsTable extends Migration
{
    private string $tableName = 'event_logs';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('entity_id'); // Used for entity reference
            $table->enum('event_type', [
                EventLog::CUSTOMER_POINT_DEDUCTED,
                EventLog::CUSTOMER_REWARD_REMAINING_POINT_DEDUCTED
            ]);
            $table->text('message');
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
        Schema::dropIfExists($this->tableName);
    }
}
