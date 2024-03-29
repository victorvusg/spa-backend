<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Invoice;
use Illuminate\Support\Facades\DB;

class ChangeTypeColumnInvoiceTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Invoice::where('type', 'deduction')
			->update([
				'type' => 'withdraw'
			]);
		Invoice::where('type', 'topup')
			->update([
				'type' => 'deposit'
			]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Invoice::where('type', 'withdraw')
			->update([
				'type' => 'deduction'
			]);
		Invoice::where('type', 'deposit')
			->update([
				'type' => 'topup'
			]);
	}
}
