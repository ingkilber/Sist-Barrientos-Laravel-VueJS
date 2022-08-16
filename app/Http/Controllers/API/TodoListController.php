<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\TodoList;

class TodoListController extends Controller
{
    public function store(Request $request)
    {
        $userId = Auth()->user()->id;
        $data = [
            'title' => $request->title,
            'completed' => $request->completed,
            'completed_date' => $request->completed_date,
            'due_date' => $request->due_date,
            'user_id' => $userId
        ];
        $output = TodoList::store($data);
        if ($output) {
            return [
                'status' => 201,
                'success' => true,
                'message' => 'ok'
            ];
        }
        return [
            'status' => 403,
            'success' => false,
            'message' => 'something went worong'
        ];
    }
    public function getTodoData(Request $request)
    {
        $sortBy = $request->sortBy;
        $orderBy = ($sortBy == 'most_recent') ? 'DESC' : 'ASC';

        $data = TodoList::getSortedLists($sortBy, $orderBy);
        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'ok',
            'data' => $data
        ]);
    }
    public function upDateStatus(Request $request)
    {
        $todoId = $request->todo_id;
        $data = [
            'completed_date' => $request->completed_date,
            'completed' => !$request->completed,
        ];
        $output = TodoList::updateData($todoId, $data);

        if ($output) {
            return [
                'status' => 201,
                'success' => true,
                'message' => 'updated'
            ];
        }
        return [
            'status' => 403,
            'success' => false,
            'message' => 'something went worong'
        ];
    }

    public function setDueDate(Request $request)
    {
        $todoId = $request->todo_id;
        $data = [
            'due_date' => $request->due_date,
        ];
        $output = TodoList::updateData($todoId, $data);

        if ($output) {
            return [
                'status' => 201,
                'success' => true,
                'message' => 'updated'
            ];
        }
        return [
            'status' => 403,
            'success' => false,
            'message' => 'something went worong'
        ];
    }

    public function deleteData(Request $request)
    {
        $todoId = $request->todo_id;
        $output = TodoList::deleteData($todoId);
        if ($output) {
            return [
                'status' => 200,
                'success' => true,
                'message' => 'deleted'
            ];
        }
        return [
            'status' => 403,
            'success' => false,
            'message' => 'something went worong'
        ];
    }
}
