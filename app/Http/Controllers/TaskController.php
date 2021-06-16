<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helper\Translation;
use App\Repositories\TaskRepository;
use App\Repositories\TaskAssignmentRepository;
use App\Http\HttpResponse;
use App\Task;
use Illuminate\Http\Response as Response;

class TaskController extends Controller
{
    private $taskRepository;

    private $taskAssignmentRepository;

    public function __construct(
        TaskRepository $taskRepository, 
        TaskAssignmentRepository $taskAssignmentRepository)
    {
        $this->taskRepository = $taskRepository;
        $this->taskAssignmentRepository = $taskAssignmentRepository;
    }

    public function getTasks(Request $request)
    {
        $params = $request->all();
        if (empty($params['page'])) {
            throw new \Exception('Page cannot be empty.');
        }

        $per_page = !empty($params['per_page']) ? $params['per_page'] : 10;

        try {
            // Get Tasks
            $tasks = Task::paginate($per_page);
        
            return HttpResponse::toJson(true, Response::HTTP_CREATED, Translation::$GET_TASK_SUCCESSFULLY, $tasks);
        } catch (\Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function getTaskAssignments(Request $request)
    {
        $params = $request->all();

        try {
            // Get Task Assignments
            $task_assignments = $this->taskAssignmentRepository->get();
        
            return HttpResponse::toJson(true, Response::HTTP_CREATED, Translation::$GET_TASK_ASSIGNMENTS_SUCCESSFULLY, $task_assignments);
        } catch (\Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function getReminderAssignments(Request $request)
    {
        $params = $request->all();

        try {
            // Get Task Assignments
            $reminders = $this->taskAssignmentRepository->getReminder();
        
            return HttpResponse::toJson(true, Response::HTTP_CREATED, Translation::$GET_TASK_ASSIGNMENTS_SUCCESSFULLY, $reminders);
        } catch (\Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function createReminder(Request $request)
    {
        $params = $request->all();

        try {
            // Create task history
            $discounts = $this->taskAssignmentRepository->createReminder($params);
            return HttpResponse::toJson(true, Response::HTTP_CREATED, Translation::$DISCOUNT_CREATED, $discounts);
        } catch (\Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }


    public function create(Request $request)
    {
        $params = $request->all();

        Validator::make($params, [
            'name' => 'required',
            'title' => [
                'schedule',
                function ($attribute, $value, $fail) {
                    if (!is_array($value)) {
                        $fail($attribute.' is invalid.');
                    }
                },
            ],
        ]);

        try {
            // 1. Create task
            $task = $this->taskRepository->create($params);
            // 2. Create task assignments
            $params['task_id'] = $task->id;
            $taskAssignmentCreated = $this->taskAssignmentRepository->create($params);
            $task_assignments = $this->taskAssignmentRepository->get();
            return HttpResponse::toJson(true, Response::HTTP_CREATED, Translation::$TASK_CREATED, $task_assignments);
        } catch (\Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function createAssignments(Request $request)
    {
        $params = $request->all();
        try {
            $taskAssignmentCreated = $this->taskAssignmentRepository->create($params);
            $task_assignments = $this->taskAssignmentRepository->get();
            return HttpResponse::toJson(true, Response::HTTP_CREATED, Translation::$TASK_CREATED,$task_assignments);
        } catch (\Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function update(Request $request, $task_id)
    {
        $params = $request->all();

        $validatedData = $request->validate([
            'name' => 'required',
        ]);

        try {
            // Update Task
            $task = $this->taskRepository->save($validatedData, true, $task_id);

            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$TASK_UPDATED, $task);
        } catch (\Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function updateTaskAssignment(Request $request, $task_assignment_id)
    {
        $params = $request->all();

        $validatedData = $request->validate([
            'emp_id' => 'required|integer',
            'day' => 'required',
        ]);

        try {
            // Update Task assignment
            $taskAssignment = $this->taskAssignmentRepository->save($validatedData, $task_assignment_id);

            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$TASK_ASSIGNMENT_UPDATED, $task);
        } catch (\Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function deleteTask($id)
    {
        if (empty($id)) {
            throw new \Exception('Task ID cannot be empty.');
        }

        try {
            $deleted = Task::destroy($id);
            $message = $deleted ? Translation::$DELETE_SUCCESS : Translation::$DELETE_NOTHING;
            $task_assignments = $this->taskAssignmentRepository->get();
            return HttpResponse::toJson(true, Response::HTTP_OK, $message, $task_assignments);
        } catch (\Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }

    public function deleteTaskAssignment(Request $request, $task_assignment_id)
    {
        $params = $request->all();
        try {
            $this->taskAssignmentRepository->delete($task_assignment_id);
            $task_assignments = $this->taskAssignmentRepository->get();
            return HttpResponse::toJson(true, Response::HTTP_OK, Translation::$TASK_ASSIGNMENT_DELETED, $task_assignments);
        } catch (Exception $e) {
            return HttpResponse::toJson(false, Response::HTTP_CONFLICT, $e->getMessage());
        }
    }  
}
