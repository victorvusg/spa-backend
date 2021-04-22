<?php

namespace App\Repositories;

use App\Customer;
use App\User;
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
            // TODO: Improve
        } catch (QueryException $e) {
            $errorCode = $e->errorInfo[1];
            if ($errorCode == 1062) {
                DB::rollBack();
                throw new QueryException();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
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

        try {
            $customer->save();
            return Customer::find($id ? $id : $customer->id);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function get(array $condition = [])
    {
        $phone = isset($condition['phone']) ? $condition['phone'] : null;
        $name = isset($condition['name']) ? $condition['name'] : null;
        $perPage = isset($condition['per_page']) ? $condition['per_page'] : 10;
        $page = isset($condition['page']) ? $condition['page'] : 1;

        $query = new Customer();


        if ($phone) {
            $query = $query::where('phone', 'LIKE', '%' . $phone . '%');
        }

        if ($name) {
            $query = $query::where('name', 'LIKE', '%' . $name . '%');
        }

        $customer = $query->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->withCount([
                'package AS packages_spend'=> function ($query) {
                    $query->where('is_valid', '=', 1)
                        ->select(DB::raw("SUM(total_price)"));
                }])
    
            ->withCount([
                'invoice AS coin_spend'=> function ($query) {
                    $query->where('type', '=', 'topup')->where('status', '=', 'paid')
                        ->select(DB::raw("SUM(amount)"));
                }])
    
            ->withCount([
                'intakes AS intakes_spend'=> function ($query) {
                    $query->where('is_valid', '=', 1)->where('payment_type', '=', 'cash')
                        ->select(DB::raw("SUM(final_price)"));
                },])
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($item) {
                $item['total_spend'] =  $item['intakes_spend'] + $item['packages_spend'] + $item['coin_spend'];
                return $item;
            })
            ->toArray();
        
        return [
            "Data" => $customer,
            "Pagination" => [
                "CurrentPage" => $page,
                "PerPage" => $perPage,
                "TotalItems" => $query->count()
            ]
        ];
    }

    public function getOneBy($by, $value)
    {
        $customer = Customer::where($by, '=', $value)
                ->withCount([
                    'package AS packages_spend'=> function ($query) {
                        $query->where('is_valid', '=', 1)
                            ->select(DB::raw("SUM(total_price)"));
                    }])

                ->withCount([
                    'invoice AS coin_spend'=> function ($query) {
                        $query->where('type', '=', 'topup')->where('status', '=', 'paid')
                            ->select(DB::raw("SUM(amount)"));
                    }])

                ->withCount([
                    'intakes AS intakes_spend'=> function ($query) {
                        $query->where('is_valid', '=', 1)->where('payment_type', '=', 'cash')
                            ->select(DB::raw("SUM(final_price)"));
                    },])
                ->first();
        $customer['total_spend'] =  $customer['intakes_spend'] + $customer['packages_spend'] + $customer['coin_spend'];
        return $customer;
    }

    public function update($id, array $attributes = [])
    {
        DB::beginTransaction();
        try {
            $return = $this->save($attributes, true, $id);
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
        }
    }

    public function delete($id)
    {
        return User::destroy($id);
    }
}
