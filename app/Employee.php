<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\BaseModel;

/**
 * Class Address
 * @package App
 * @mixin Builder
 */
class Employee extends BaseModel
{
	protected $table = 'employees';
	protected $fillable = [
		'name', 'phone', 'email', 'user_id', 'role_id', 'is_active', 'sale_commission', 'working_commission', 'gender'
	];

	public function user()
	{
		return $this->belongsTo('App\User', 'user_id', 'id');
	}

	public function role()
	{
		return $this->belongsTo('App\Role', 'role_id', 'id');
	}

	public function package()
	{
		return $this->hasMany('App\Package');
	}

	public function order()
	{
		return $this->hasMany('App\Order');
	}

	public function invoice()
	{
		return $this->hasMany('App\Invoice');
	}

	public function TaskAssignments()
	{
		return $this->hasMany('App\TaskAssignment', 'employee_id');
	}

	public function taskHistories()
	{
		return $this->hasMany('App\TaskHistory', 'employee_id');
	}

	public function judgements()
	{
		return $this->hasMany('App\Judgement', 'employee_id');
	}
}
