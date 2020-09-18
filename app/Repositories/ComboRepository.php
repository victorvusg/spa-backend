<?php

namespace App\Repositories;

use App\Combo;
use App\Customer;
use App\Employee;
use App\Helper\Translation;
use App\Order;
use App\Service;
use App\User;
use App\Variant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class ComboRepository implements ComboRepositoryInterface
{

    public function create(array $attributes = [])
    {
        DB::beginTransaction();
        try {
            $return = $this->save($attributes, false);
            DB::commit();
            return $return;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function save($data, $is_update, $id = null)
    {
        if ($is_update) {
            // Activate combo
            $combo = Combo::with('service')->find($id);
            if ($combo->is_valid) {
                throw new \Exception(Translation::$COMBO_ALREADY_VALID);
            }

            if (isset($data['is_valid'])) {
                // Calc price
                $total_price = ($combo->service->price * $combo->amount) / $combo->service->combo_ratio;
                $combo->is_valid = $data['is_valid'];

                // Add Expired Date
                $now = Carbon::now();
                $combo->expiry_date = date('Y-m-d H:m:s', strtotime("+3 months", strtotime($now)));

                // Store Price to combo in case service price change
                $variant = Variant::with('service')->find($combo->variant_id);
                $combo->total_price = $total_price;
                $combo->sale_commission = $total_price * $variant->service->combo_commission / 100;
            } else {
                throw new \Exception("Please pass is_valid value");
            }

        } else {
            $userId = $data['user_id'];
            $employee = Employee::where('user_id', $userId)->first();
            unset($data['user_id']);

            // Create Combo
            $combo = new Combo();
            foreach ($data as $key => $value) {
                $combo->$key = $value;
            }
            $combo->employee_id = $employee->id;
        }

        if ($combo->save()) {
            if ($id) {
                return Combo::find($id);
            } else {
                return Combo::find($combo->id);
            }
        } else {
            return false;
        }
    }

    public function get(array $condition = [])
    {
        $variantId = isset($condition['variant_id']) ? $condition['variant_id'] : null;
        $customerId = isset($condition['customer_id']) ? $condition['customer_id'] : null;
        $employee_id = isset($condition['employee_id']) ? $condition['employee_id'] : null;
        $isValid = isset($condition['is_valid']) ? $condition['is_valid'] : null;

        $perPage = isset($condition['per_page']) ? $condition['per_page'] : 10;
        $page = isset($condition['page']) ? $condition['page'] : 1;

        $query = new Combo();

        if ($variantId) {
            $query = $query->where('variant_id', '=', $variantId);
        }
        if ($customerId) {
            $query = $query->where('customer_id', '=', $customerId);
        }
        if ($employee_id) {
            $query = $query->where('employee_id', '=', $employee_id);
        }
        if ($isValid) {
            $query = $query->where('is_valid', '=', $isValid);
        }

        $combos = $query->with(['service', 'customer', 'orders' => function ($query) {
            $query->whereHas('intake', function ($query) {
                $query->where('is_valid', 1);
            });
        }])
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();

        return [
            "Data" => $combos,
            "Pagination" => [
                "CurrentPage" => $page,
                "PerPage" => $perPage,
                "TotalItems" => $query->count()
            ]
        ];
    }

    public function getOneBy($by, $value)
    {
        return Combo::where($by, $value)->with(['orders' => function ($query) {
            $query->whereHas('intake', function ($query) {
                $query->where('is_valid', 1);
            });
        }, 'service'])->first();
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
        $combo = Combo::find($id);
        if ($combo !== null) {
            if ($combo->is_valid) {
                throw new \Exception('Can not delete valid combo');
            } else {
                DB::beginTransaction();
                try {
                    $destroy = Combo::destroy($id);
                    DB::commit();
                    return $destroy;
                } catch (\Exception $exception) {
                    DB::rollBack();
                    throw $exception;
                }
            }
        } else {
            throw new \Exception('No Combo Found');
        }

    }
}
