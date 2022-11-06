<?php

namespace App\Repositories;

use App\Customer;
use App\Employee;
use App\Order;
use App\Review;
use App\User;
use App\TaskHistory;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Mockery\Exception;

class EmployeeRepository implements EmployeeRepositoryInterface
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
			//            $user = User::find($id);
		} else {
			$user = new User();
			$user->email = $data['username'];
			$user->password = Hash::make($data['password'], [
				'rounds' => 12,
			]);

			if ($user->save()) {
				$employee = new Employee();
				$employee->name = $data['name'];
				$employee->role_id = $data['role_id'];
				$employee->phone = $data['phone'];
				$employee->gender = $data['gender'];
				$employee->user_id = $user->id;

				if ($employee->save()) {
					return Employee::with('user')->find($employee->id);
				} else {
					throw new Exception("Error when storing employee");
				}
			} else {
				throw new Exception("Error when storing user");
			}
		}
	}

	public function get(array $condition = [])
	{
		$roleId = isset($condition['roleId']) ? $condition['roleId'] : null;
		$perPage = isset($condition['per_page']) ? $condition['per_page'] : 10;
		$page = isset($condition['page']) ? $condition['page'] : 1;

		$query = new Employee();

		if ($roleId) {
			$query = $query::where('role_id', $roleId);
		}
		$query->with(['user' => function ($uQuery) {
			$uQuery->where('is_active', 1);
		}]);
		$date = Carbon::now()->setTimezone('Asia/Ho_Chi_Minh');
		$date->settings([
			'monthOverflow' => false,
		]);
		// Get this month commissions
		$this_month_from =  $date->copy()
			->startOfMonth()
			->setTimezone('UTC')
			->toDateTimeString();
		$this_month_to = $date->copy()
			->endOfMonth()
			->setTimezone('UTC')
			->toDateTimeString();

		// Get last month commissions
		$last_month_from =  $date->copy()
			->subMonths(1)
			->startOfMonth()
			->setTimezone('UTC')
			->toDateTimeString();
		$last_month_to = $date->copy()
			->subMonths(1)
			->endOfMonth()
			->setTimezone('UTC')
			->toDateTimeString();

		// Working commissions
		$query->withCount(['order AS working_commission' => function ($query) use ($this_month_from, $this_month_to) {
			$query->whereBetween('created_at', [$this_month_from, $this_month_to])
				->select(DB::raw("SUM(working_commission)"));
		}]);

		$query->withCount(['order AS working_commission_prev' => function ($query) use ($last_month_from, $last_month_to) {
			$query->whereBetween('created_at', [$last_month_from, $last_month_to])
				->select(DB::raw("SUM(working_commission)"));
		}]);


		// Sale commissions
		$query->withCount(['package AS sale_commission' => function ($query) use ($this_month_from, $this_month_to) {
			$query->whereBetween('created_at', [$this_month_from, $this_month_to])
				->select(DB::raw("SUM(sale_commission)"));
		}]);

		$query->withCount(['package AS sale_commission_prev' => function ($query) use ($last_month_from, $last_month_to) {
			$query->whereBetween('created_at', [$last_month_from, $last_month_to])
				->select(DB::raw("SUM(sale_commission)"));
		}]);

		// Topup commissions
		$query->withCount(['invoice AS topup_commission' => function ($query) use ($this_month_from, $this_month_to) {
			$query->whereBetween('created_at', [$this_month_from, $this_month_to])
				->select(DB::raw("SUM(topup_commission)"));
		}]);

		$query->withCount(['invoice AS topup_commission_prev' => function ($query) use ($last_month_from, $last_month_to) {
			$query->whereBetween('created_at', [$last_month_from, $last_month_to])
				->select(DB::raw("SUM(topup_commission)"));
		}]);

		// attendance
		$query->withCount(['judgements AS attendance' => function ($query) use ($this_month_from, $this_month_to) {
			$query->whereBetween('created_at', [$this_month_from, $this_month_to])
				->select(DB::raw("SUM(`point`)"));
		}]);

		$query->withCount(['judgements AS attendance_prev' => function ($query) use ($last_month_from, $last_month_to) {
			$query->whereBetween('created_at', [$last_month_from, $last_month_to])
				->select(DB::raw("SUM(point)"));
		}]);

		// Count Orders
		$query->withCount(['order AS total_sales' => function ($query) use ($this_month_from, $this_month_to) {
			$query->whereBetween('created_at', [$this_month_from, $this_month_to])->whereHas('intake', function ($iQuery) {
				$iQuery->where('is_valid', '=', 1);
			})->select(DB::raw("SUM(price)"));;
		}]);

		$query->withCount(['order AS total_sales_prev' => function ($query) use ($last_month_from, $last_month_to) {
			$query->whereBetween('created_at', [$last_month_from, $last_month_to])->whereHas('intake', function ($iQuery) {
				$iQuery->where('is_valid', '=', 1);
			})->select(DB::raw("SUM(price)"));;
		}]);

		// With points
		$query->withCount(['order AS attitude_point' => function ($query) {
			$query->withCount(['review AS attitude_point' => function ($subQuery) {
				$subQuery->select(DB::raw("SUM(attitude)"));
			}]);
		}]);
		$query->withCount(['order AS skill_point' => function ($query) {
			$query->withCount(['review AS skill_point' => function ($subQuery) {
				$subQuery->select(DB::raw("SUM(skill)"));
			}]);
		}]);

		$query->withCount(['judgements AS attendance' => function ($query) {
			$query->select(DB::raw("SUM(point)"));
		}]);


		// $query->select(DB::raw("SUM(point) as total_point"))
		//     ->withCount(['taskHistories AS task_history' => function ($query) {
		//         $query->whereMonth('created_at', Carbon::now()->month)
		//             ->whereBetween('created_at', [
		//                 Carbon::now()->startOfYear(),
		//                 Carbon::now()->endOfYear()
		//             ]);
		//         }
		//     ]
		// );

		// $prev_month = Carbon::now()->month - 1;

		// if ($prev_month <= 0) {
		//     $prev_month = 12;
		//     $start = Carbon::now()->year - 1;
		//     $end = Carbon::now()->year - 1;
		// } else {
		//     $start = Carbon::now()->startOfYear();
		//     $end = Carbon::now()->endOfYear();
		// }

		// $query->withCount(['taskHistories AS task_history' => function ($query) {
		//     $query->select(DB::raw("SUM(point) as total_point"))
		//         ->whereMonth('created_at', $prev_month)
		//         ->whereBetween('created_at', [
		//             $start,
		//             $end,
		//         ]);
		// }]);

		$users = $query->offset(($page - 1) * $perPage)
			->limit($perPage)
			->orderBy('id', 'desc')
			->get()
			->toArray();

		return [
			"Data" => $users,
			"Pagination" => [
				"CurrentPage" => $page,
				"PerPage" => $perPage,
				"TotalItems" => $query->count()
			]
		];
	}

	public function getOneBy($by, $value, $config)
	{
		$date = Carbon::now()->setTimezone('Asia/Ho_Chi_Minh');
		$date->settings([
			'monthOverflow' => false,
		]);
		$day = $date->shortEnglishDayOfWeek;
		$query = Employee::where($by, '=', $value)->with('role')->with(['TaskAssignments' => function ($taskQuery) use ($day) {
			$taskQuery->where('type', '=', 'reminder')
				->orWhere(function ($query) use ($day) {
					$query->where(strtolower($day), 1)->where('type', '=', 'chore');
				});
		}]);
		if (isset($config['show_commission']) && $config['show_commission'] == 1) {


			// Get this month commissions
			$this_month_from =  $date->copy()
				->startOfMonth()
				->setTimezone('UTC')
				->toDateTimeString();
			$this_month_to = $date->copy()
				->endOfMonth()
				->setTimezone('UTC')
				->toDateTimeString();

			// Get last month commissions
			$last_month_from =  $date->copy()
				->subMonths(1)
				->startOfMonth()
				->setTimezone('UTC')
				->toDateTimeString();
			$last_month_to = $date->copy()
				->subMonths(1)
				->endOfMonth()
				->setTimezone('UTC')
				->toDateTimeString();

			// Working commission
			$query->withCount(['order AS working_commission' => function ($query) use ($this_month_from, $this_month_to) {
				$query->whereBetween('created_at', [$this_month_from, $this_month_to])
					->select(DB::raw("SUM(working_commission)"));
			}]);

			$query->withCount(['order AS working_commission_prev' => function ($query) use ($last_month_from, $last_month_to) {
				$query->whereBetween('created_at', [$last_month_from, $last_month_to])
					->select(DB::raw("SUM(working_commission)"));
			}]);
			// Sale commission
			$query->withCount(['package AS sale_commission' => function ($query) use ($this_month_from, $this_month_to) {
				$query->whereBetween('created_at', [$this_month_from, $this_month_to])
					->select(DB::raw("SUM(sale_commission)"));
			}]);

			$query->withCount(['package AS sale_commission_prev' => function ($query) use ($last_month_from, $last_month_to) {
				$query->whereBetween('created_at', [$last_month_from, $last_month_to])
					->select(DB::raw("SUM(sale_commission)"));
			}]);

			// Topup commission
			$query->withCount(['invoice AS topup_commission' => function ($query) use ($this_month_from, $this_month_to) {
				$query->whereBetween('created_at', [$this_month_from, $this_month_to])
					->select(DB::raw("SUM(`topup_commission`)"));
			}]);

			$query->withCount(['invoice AS topup_commission_prev' => function ($query) use ($last_month_from, $last_month_to) {
				$query->whereBetween('created_at', [$last_month_from, $last_month_to])
					->select(DB::raw("SUM(topup_commission)"));
			}]);

			// attendance
			$query->withCount(['judgements AS attendance' => function ($query) use ($this_month_from, $this_month_to) {
				$query->whereBetween('created_at', [$this_month_from, $this_month_to])
					->select(DB::raw("SUM(`point`)"));
			}]);

			$query->withCount(['judgements AS attendance_prev' => function ($query) use ($last_month_from, $last_month_to) {
				$query->whereBetween('created_at', [$last_month_from, $last_month_to])
					->select(DB::raw("SUM(point)"));
			}]);
		}
		// Judgements
		$query->with('judgements');

		$employee = $query->first();

		if (isset($config['show_point']) && $config['show_point'] == 1) {
			// Attitude
			$attitude_point = Review::whereHas('order', function ($q) use ($employee) {
				$q->where('employee_id', $employee->id);
			})->avg('attitude');

			// Skill
			$skill_point = Review::whereHas('order', function ($q) use ($employee) {
				$q->where('employee_id', $employee->id);
			})->avg('skill');
			$employee->attitude_point = $attitude_point;
			$employee->skill_point = $skill_point;
		}

		// Current month task point
		// $current_task_point = TaskHistory::select(DB::raw("SUM(point) as total_point"))
		//     ->where('employee_id', $employee->id)
		//     ->whereMonth('created_at', Carbon::now()->month)
		//     ->whereBetween('created_at', [
		//         Carbon::now()->startOfYear(),
		//         Carbon::now()->endOfYear(),
		//     ])
		//     ->get();

		$employee->current_month_task_point = $current_task_point ?? null;

		// Previous month task point
		$prev_month = Carbon::now()->month - 1;

		if ($prev_month <= 0) {
			$prev_month = 12;
			$start = Carbon::now()->year - 1;
			$end = Carbon::now()->year - 1;
		} else {
			$start = Carbon::now()->startOfYear();
			$end = Carbon::now()->endOfYear();
		}

		// $current_task_point = TaskHistory::select(DB::raw("SUM(point) AS total_point"))
		//     ->where('employee_id', $employee->id)
		//     ->whereMonth('created_at', $prev_month)
		//     ->whereBetween('created_at', [
		//         $start,
		//         $end,
		//     ])
		//     ->get();

		$employee->prev_month_task_point = $prev_task_point ?? null;

		return $employee;
	}


	// Not working now
	public function update($id, array $attributes = [])
	{
		return $this->save($attributes, true, $id);
	}

	// Not working now
	public function delete($id)
	{
		//        return Employee::destroy($id);
	}
}
