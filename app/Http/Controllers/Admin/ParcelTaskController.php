<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ParcelTask;
use Validator;

class ParcelTaskController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search'));

        $tasks = ParcelTask::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('key', 'like', '%'.$search.'%')
                        ->orWhere('title', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(15)
            ->withQueryString();

        return view('admin.parcel_task.view', [
            'tasks' => $tasks,
            'search' => $search,
        ]);
    }

    public function add(Request $request)
    {
        if ($request->isMethod('GET')) {
            return view('admin.parcel_task.add');
        }

        if ($request->submit) {
            $rules = [
                'key' => 'required|unique:parcel_tasks,key',
                'title' => 'required|max:120',
                'sort_order' => 'required|integer|min:0',
            ];

            $attributes = [
                'key' => 'Task Key',
                'title' => 'Task Title',
                'sort_order' => 'Sort Order',
            ];

            $validator = Validator::make($request->all(), $rules, [], $attributes);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $task = new ParcelTask;
            $task->key = strtolower($request->key);
            $task->title = $request->title;
            $task->sort_order = $request->sort_order;
            $task->is_active = $request->is_active ? true : false;

            if ($request->hasFile('icon_path')) {
                $image = $request->file('icon_path');
                $extension = $image->getClientOriginalExtension();
                $file_name = 'parcel_task_' . time() . '.' . $extension;
                $target_dir = public_path('images/parcel_icons');
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }
                $image->move($target_dir, $file_name);
                $task->icon_path = 'images/parcel_icons/' . $file_name;
            }

            $task->save();
            flashMessage('success', 'Parcel Task Added Successfully');
        }

        return redirect('admin/parcel_tasks');
    }

    public function update(Request $request)
    {
        if ($request->isMethod('GET')) {
            $data['parcelTask'] = ParcelTask::find($request->id);
            if ($data['parcelTask']) {
                return view('admin.parcel_task.edit', $data);
            }
            flashMessage('danger', 'Invalid ID');
        }

        if ($request->submit) {
            $rules = [
                'key' => 'required|unique:parcel_tasks,key,' . $request->id,
                'title' => 'required|max:120',
                'sort_order' => 'required|integer|min:0',
            ];

            $attributes = [
                'key' => 'Task Key',
                'title' => 'Task Title',
                'sort_order' => 'Sort Order',
            ];

            $validator = Validator::make($request->all(), $rules, [], $attributes);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $task = ParcelTask::find($request->id);
            $task->key = strtolower($request->key);
            $task->title = $request->title;
            $task->sort_order = $request->sort_order;
            $task->is_active = $request->is_active ? true : false;

            if ($request->hasFile('icon_path')) {
                $image = $request->file('icon_path');
                $extension = $image->getClientOriginalExtension();
                $file_name = 'parcel_task_' . time() . '.' . $extension;
                $target_dir = public_path('images/parcel_icons');
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }
                $image->move($target_dir, $file_name);
                $task->icon_path = 'images/parcel_icons/' . $file_name;
            }

            $task->save();
            flashMessage('success', 'Parcel Task Updated Successfully');
        }

        return redirect('admin/parcel_tasks');
    }

    public function delete(Request $request)
    {
        $task = ParcelTask::find($request->id);
        if ($task) {
            $task->delete();
            flashMessage('success', 'Parcel Task Deleted Successfully');
        } else {
            flashMessage('danger', 'Invalid ID');
        }
        return redirect('admin/parcel_tasks');
    }

    public function toggle_status(Request $request)
    {
        $task = ParcelTask::find($request->id);
        if ($task) {
            $task->is_active = !$task->is_active;
            $task->save();
            flashMessage('success', 'Status Updated Successfully');
        } else {
            flashMessage('danger', 'Invalid ID');
        }
        return redirect('admin/parcel_tasks');
    }
}
