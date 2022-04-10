<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Class Address
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Customer extends Model
{
    protected $table = 'customers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','phone', 'email', 'points', 'is_active', 'gender'
    ];

    public function rewardRule() {
        return $this->belongsTo(RewardRule::class, 'reward_rule_id', 'id');
    }

    public function package()
    {
        return $this->hasMany('App\Package');
    }

    public function invoice()
    {
        return $this->hasMany('App\Invoice');
    }
    public function intakes()
    {
        return $this->hasMany('App\Intake');
    }
    public function orders()
    {
        return $this->hasMany('App\Order');
    }
}
