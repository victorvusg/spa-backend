<?php

namespace App\Repositories;

use App\Package;
use App\Customer;
use App\Employee;
use App\Combo;
use App\Helper\Translation;
use App\Helper\Common;
use App\Order;
use App\Service;
use App\User;
use App\Variant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class PackageRepository implements PackageRepositoryInterface
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
			throw new \Exception($exception->getMessage());
		}
	}

	public function save($data, $is_update, $id = null)
	{
		if ($is_update) {
			//Approve package

			$package = Package::with(['combos' => function ($comboQuery) {
				$comboQuery->with(['variant' => function ($variantQuery) {
					$variantQuery->with(['service' => function ($serviceQuery) {
						$serviceQuery->with('serviceCategory');
					}]);
				}]);
			}])->find($id);

			if (isset($data['payment_method_id'])) {
				$package->payment_method_id = $data['payment_method_id'];
			}
			if (isset($data['is_valid'])) {
				if ($package->is_valid) {
					throw new \Exception(Translation::$COMBO_ALREADY_VALID);
				}
				$total_price = 0;
				$sale_commission = 0;
				$combos = $package->combos->toArray();
				foreach ($combos as $key => $combo) {
					if (!$combo['is_promotion_combo']) {
						$sale_commission_rate = $combo['variant']['service']['combo_commission'] / 100;
						$sale_commission += $sale_commission_rate * $combo['total_price'];
						$total_price +=  $combo['total_price'];
					}
				}
				$package->total_price = $total_price;
				$package->sale_commission = $sale_commission;
				$package->is_valid = $data['is_valid'];
				if ($package->total_price > 0 && $package->customer_id !== null) {
					$customer = Customer::find($package->customer_id);
					// Plus customer point
					//                $customer->points = $customer->points + (int)($totalPrice / env('MONEY_POINT_RATIO'));\
					// Currently 50k VND = 1 point
					// $customer->points = $customer->points + (int)($package->total_price / 100);
					$customer->save();
				}
				$combo_amount = count($combos);
				$now = Carbon::now();
				if ($combo_amount < 10) {
					$package->expiry_date = date('Y-m-d H:m:s', strtotime("+3 months", strtotime($now)));
				} else {
					$now = Carbon::now();
					$package->expiry_date = date('Y-m-d H:m:s', strtotime("+6 months", strtotime($now)));
				}
			}
			$package->save();

			//TODO: UP RANK
			$up_rank = false;
			if (!empty($customer)) {
				$up_rank = Common::upRank($customer);
			}
			$package['up_rank_result'] = $up_rank;
			return $package;
		} else {
			// Create package
			$userId = $data['user_id'];
			$employee = Employee::where('user_id', $userId)->first();
			unset($data['user_id']);
			$combos = $data['combos'];

			unset($data['combos']);
			// Create Package
			$package = new Package();
			foreach ($data as $key => $value) {
				$package->$key = $value;
			}

			$package->employee_id = $employee->id;
			if ($package->save()) {
				foreach ($combos as $key => $combo) {
					$combos[$key]['package_id'] = $package->id;
					$combos[$key]['created_at'] = Carbon::now();
					$combos[$key]['updated_at'] = Carbon::now();
					$combos[$key]['number_used'] = 0;
					$variant_price = Variant::find($combos[$key]['variant_id'])->price;
					$combos[$key]['total_price'] = $combos[$key]['amount'] * $variant_price;
				}

				Combo::insert($combos);
				// Return Intake with order
				return Package::with('combos')->find($package->id);
			} else {
				return false;
			}
		}
	}

	public function get(array $condition = [])
	{
		$variantId = isset($condition['variant_id']) ? $condition['variant_id'] : null;
		$customerId = isset($condition['customer_id']) ? $condition['customer_id'] : null;
		$employee_id = isset($condition['employee_id']) ? $condition['employee_id'] : null;
		$isValid = isset($condition['is_valid']) ? $condition['is_valid'] : null;
		$exclude_expired = isset($condition['exclude_expired']) ? $condition['exclude_expired'] : null;

		$fromDate = isset($condition['from_date']) ? $condition['from_date'] : null;
		$toDate = isset($condition['to_date']) ? $condition['to_date'] : null;

		$perPage = isset($condition['per_page']) ? $condition['per_page'] : 10;
		$page = isset($condition['page']) ? $condition['page'] : 1;

		$query = new Package();

		if ($exclude_expired) {
			$date = Carbon::now()->setTimezone('Asia/Ho_Chi_Minh');
			$toDay = $date->toDateString();
			$query = $query->where('expiry_date', '>=', $toDay);
		}

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
		if ($fromDate) {
			$query = $query->where('created_at', '>=', $fromDate);
		}

		if ($toDate) {
			$query = $query->where('created_at', '<=', $toDate);
		}
		// 'customer', 'orders' => function ($query) {
		//     $query->whereHas('intake', function ($query) {
		//         $query->where('is_valid', 1);
		//     });
		// }
		$packages = $query->with(['combos' => function ($vQuery) {
			$vQuery->with(['orders' => function ($query) {
				$query->whereHas('intake', function ($query) {
					$query->where('is_valid', 1);
				});
			}]);
			$vQuery->with(['variant' => function ($sQuery) {
				$sQuery->with(['service' => function ($cQuery) {
					$cQuery->with('serviceCategory');
				}]);
			}]);
		}, 'customer'])
			->orderBy('id', 'desc')
			->paginate($perPage, ['*'], 'page', $page);

		return [
			"Data" => $packages->items(),
			"Pagination" => [
				"CurrentPage" => $page,
				"PerPage" => $perPage,
				"TotalItems" => $packages->total()
			]
		];
	}

	public function getOneBy($by, $value)
	{
		return Package::where($by, $value)->with(['combos' => function ($vQuery) {
			$vQuery->with(['orders' => function ($query) {
				$query->whereHas('intake', function ($query) {
					$query->where('is_valid', 1);
				});
			}]);
			$vQuery->with(['variant' => function ($sQuery) {
				$sQuery->with(['service' => function ($cQuery) {
					$cQuery->with('serviceCategory');
				}]);
			}]);
		}, 'customer'])->first();
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
		$package = Package::where('is_valid', false)->find($id);
		if ($package) {
			$package->delete();
		} else {
			throw new \Exception(Translation::$NO_INTAKE_FOUND);
		}
	}
}
