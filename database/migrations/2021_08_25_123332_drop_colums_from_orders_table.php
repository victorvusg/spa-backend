<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropColumsFromOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('base_price');
            $table->dropColumn('discount_percentage');
            $table->dropColumn('discount_note');
            $table->dropColumn('credit_price');
            $table->float('unit_price')->default(0);
            $table->string('discount_description')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->float('base_price')->default(0);
            $table->float('credit_price')->default(0);
            $table->float('discount_percentage')->default(0);
            $table->string('discount_note')->nullable();
            $table->dropColumn('unit_price');
            $table->dropColumn('discount_description');
        });
    }
}
