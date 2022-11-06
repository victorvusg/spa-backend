<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

/**
 * Class Order
 * @package App
 * @mixin Builder
 */
class Order extends BaseModel
{
	protected $table = 'orders';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'variant_id', 'user_id', 'amount', 'note', 'intake_id', 'combo_id'
	];

	public function combo()
	{
		return $this->belongsTo('App\Combo');
	}

	public function intake()
	{
		return $this->belongsTo('App\Intake');
	}

	public function variant()
	{
		return $this->belongsTo('App\Variant');
	}

	public function customer()
	{
		return $this->belongsTo('App\Customer');
	}

	public function employee()
	{
		return $this->belongsTo('App\Employee');
	}

	public function review()
	{
		return $this->hasOne('App\Review');
	}
}
