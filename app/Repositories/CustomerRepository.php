<?php

namespace App\Repositories;

use App\Customer;
use App\User;
use Cassandra\Custom;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class CustomerRepository implements CustomerRepositoryInterface
{

    public function create(array $attributes = [])
    {
        DB::beginTransaction();
        try {
            $return = $this->save($attributes, false);
            DB::commit();
            return $return;
        } catch (QueryException $e) {
            $errorCode = $e->errorInfo[1];
            if ($errorCode == 1062) {
                DB::rollBack();
                return 1062;
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }
    }

    public function save($data, $is_update, $id = null)
    {
        if ($is_update) {
            $customer = Customer::find($id);
        } else {
            $customer = new Customer();
        }

        foreach ($data as $key => $value) {
            $customer->$key = $value;
        }

        if ($customer->save()) {
            return $customer;
        } else {
            return false;
        }
    }

    public function get(array $condition = [])
    {
        if (empty($condition)) {
            return Customer::all();
        } else {
            $phone = isset($condition['phone']) ? $condition['phone'] : null;
            $perPage = isset($condition['perPage']) ? $condition['perPage'] : 10;
            $offset = isset($condition['page']) ? $condition['page'] : 1;

            return Customer::where('phone', 'LIKE', $phone . '%')
                ->offset(($offset - 1) * $perPage)
                ->limit($perPage)
                ->orderBy('id', 'desc')
                ->get()
                ->toArray();
        }
    }

    public function getOneBy($by, $value)
    {
        return Customer::where($by, '=', $value)->with(['combos' => function ($query) {
            $query->with('service');
        }])->first();
    }

    public function update($id, array $attributes = [])
    {
        DB::beginTransaction();
        try {
            $return = $this->save($attributes, true, $id);
            DB::commit();
            return $return;
        } catch (\Exception $exception) {
            DB::rollBack();
        }
    }

    public function delete($id)
    {
        return User::destroy($id);
    }
}
