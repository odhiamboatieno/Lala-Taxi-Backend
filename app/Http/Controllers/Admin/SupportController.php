<?php

namespace App\Http\Controllers\Admin;

use App\Models\Support;
use App\Models\SupportDriver;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class SupportController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'search' => trim((string) $request->get('search', '')),
            'status' => trim((string) $request->get('status', '')),
            'owner' => trim((string) $request->get('owner', '')),
        ];

        $supports = Support::query();

        if ($filters['search'] !== '') {
            $supports->where(function ($query) use ($filters) {
                $query->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('link', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('status', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('Owner', 'like', '%' . $filters['search'] . '%');
            });
        }

        if ($filters['status'] !== '') {
            $supports->where('status', $filters['status']);
        }

        if ($filters['owner'] !== '') {
            $supports->where('Owner', $filters['owner']);
        }

        $supports = $supports->orderByDesc('id')->paginate(15)->withQueryString();

        return view('admin/support/view', compact('supports', 'filters'));
    }

    public function add(Request $request)
    {
        if ($request->isMethod('GET')) {
            return view('admin/support/add');
        }

        if ($request->submit) {
            $rules = [
                'name' => 'required',
                'link' => 'required',
                'status' => 'required',
                'image' => 'required',
                'owner' => 'required|in:Rider,Driver,All',
            ];

            $support_validation = false;

            if (filter_var($request->link, FILTER_VALIDATE_URL)) {
                $support_validation = true;
            } else {
                $check_spl_char = preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $request->link);
                $check_number = preg_match("/^\d+$/", $request->link);

                if ($check_spl_char && $check_number) {
                    $support_validation = true;
                }
            }

            if (!$support_validation) {
                $rules['link'] = 'required|integer';
            }

            $messages = [
                'link.integer' => 'Please provide a valid link',
                'owner.in' => 'The owner must be one of: Rider, Driver, All',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $image_uploader = resolve(\App\Contracts\ImageHandlerInterface::class);
            $target_dir = '/images/support';

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $extension = $image->getClientOriginalExtension();
                $file_name = "category-image" . time() . '.' . $extension;
                $options = compact('target_dir', 'file_name');
                $upload_result = $image_uploader->upload($image, $options);
                if (!$upload_result['status']) {
                    flashMessage('danger', $upload_result['status_message']);
                    return back();
                }
            }

            $support = new Support;
            $support->name = $request->name;
            $support->link = $request->link;
            $support->status = $request->status;
            $support->image = $file_name;
            $support->Owner = $request->owner; // Use Owner
            $support->save();

            flashMessage('success', 'Added Successfully');
        }

        return redirect('admin/support');
    }

    public function update(Request $request)
    {
        if ($request->isMethod('GET')) {
            $data['result'] = Support::find($request->id);
            if ($data['result']) {
                \Log::info('Owner value for Support ID ' . $request->id . ': ' . ($data['result']->Owner ?? 'Not set')); // Use Owner
                $data['editable'] = ($request->id == '1' || $request->id == '2') ? 'readonly' : '';
                return view('admin/support/edit', $data);
            }
            flashMessage('danger', 'Invalid ID');
            return redirect('admin/support');
        }

        if ($request->submit) {
            $rules = [
                'name' => 'required',
                'link' => 'required',
                'status' => 'required',
                'owner' => 'required|in:Rider,Driver,All',
            ];

            $support_validation = false;

            if (filter_var($request->link, FILTER_VALIDATE_URL)) {
                $support_validation = true;
            } else {
                $check_spl_char = preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $request->link);
                $check_number = preg_match("/^\d+$/", $request->link);

                if ($check_spl_char && $check_number) {
                    $support_validation = true;
                }
            }

            if (!$support_validation) {
                $rules['link'] = 'required|integer';
            }

            $messages = [
                'link.integer' => 'Please provide a valid link',
                'owner.in' => 'The owner must be one of: Rider, Driver, All',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $support = Support::find($request->id);
            if (!$support) {
                flashMessage('danger', 'Invalid ID');
                return redirect('admin/support');
            }

            if ($request->hasFile('image')) {
                $image_uploader = resolve(\App\Contracts\ImageHandlerInterface::class);
                $target_dir = '/images/support';
                $image = $request->file('image');
                $extension = $image->getClientOriginalExtension();
                $file_name = "category-image" . time() . '.' . $extension;
                $options = compact('target_dir', 'file_name');
                $upload_result = $image_uploader->upload($image, $options);
                if (!$upload_result['status']) {
                    flashMessage('danger', $upload_result['status_message']);
                    return back();
                }
                $support->image = $file_name;
            }

            $support->name = $request->name;
            $support->link = $request->link;
            $support->status = $request->status;
            $support->Owner = $request->owner; // Use Owner
            $support->save();

            flashMessage('success', 'Updated Successfully');
        }

        return redirect('admin/support');
    }

    public function delete(Request $request)
    {
        if ($request->id == '1' || $request->id == '2') {
            flashMessage('danger', "This is a required record and cannot be deleted");
            return redirect('admin/support');
        }

        $support = Support::find($request->id);
        if ($support) {
            if ($support->image) {
                $delete_image = resolve(\App\Contracts\ImageHandlerInterface::class);
                $delete_image->delete($support->image, ['file_path' => '/images/support/']);
            }
            $support->delete();
            flashMessage('success', 'Deleted Successfully');
        } else {
            flashMessage('danger', 'Invalid ID');
        }

        return redirect('admin/support');
    }
}
