<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Validator;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'search' => trim((string) $request->get('search', '')),
            'type' => trim((string) $request->get('type', '')),
            'status' => trim((string) $request->get('status', '')),
        ];

        $banners = Banner::query();

        if ($filters['search'] !== '') {
            $banners->where(function ($query) use ($filters) {
                $query->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('title', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('subtitle', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('link', 'like', '%' . $filters['search'] . '%');
            });
        }

        if ($filters['type'] !== '') {
            $banners->where('type', $filters['type']);
        }

        if ($filters['status'] !== '') {
            $banners->where('status', $filters['status']);
        }

        $banners = $banners->orderByDesc('id')->paginate(15)->withQueryString();

        return view('admin/banner/view', compact('banners', 'filters'));
    }

    public function add(Request $request)
    {
        if ($request->isMethod('GET')) {
            return view('admin/banner/add');
        }

        if ($request->submit) {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'type' => 'required|in:Ride,Parcel',
                'title' => 'required',
                'subtitle' => 'required',
                'status' => 'required',
                'image' => 'required|image',
                'link' => 'nullable',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $imageUploader = resolve(\App\Contracts\ImageHandlerInterface::class);
            $targetDir = '/images/banner';
            $fileName = '';

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $extension = $image->getClientOriginalExtension();
                $fileName = 'banner-'.time().'.'.$extension;
                $options = ['target_dir' => $targetDir, 'file_name' => $fileName];
                $uploadResult = $imageUploader->upload($image, $options);
                if (!$uploadResult['status']) {
                    flashMessage('danger', $uploadResult['status_message']);
                    return back();
                }
            }

            Banner::create([
                'name' => $request->name,
                'type' => $request->type,
                'title' => $request->title,
                'subtitle' => $request->subtitle,
                'link' => $request->link ?? '',
                'status' => $request->status,
                'image' => $fileName,
            ]);

            flashMessage('success', 'Added Successfully');
        }

        return redirect('admin/banner');
    }

    public function update(Request $request)
    {
        if ($request->isMethod('GET')) {
            $data['result'] = Banner::find($request->id);
            if ($data['result']) {
                $data['editable'] = '';
                return view('admin/banner/edit', $data);
            }
            flashMessage('danger', 'Invalid ID');
        }

        if ($request->submit) {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'type' => 'required|in:Ride,Parcel',
                'title' => 'required',
                'subtitle' => 'required',
                'status' => 'required',
                'link' => 'nullable',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $banner = Banner::find($request->id);
            if (!$banner) {
                flashMessage('danger', 'Invalid ID');
                return redirect('admin/banner');
            }

            if ($request->hasFile('image')) {
                $imageUploader = resolve(\App\Contracts\ImageHandlerInterface::class);
                $targetDir = '/images/banner';
                $image = $request->file('image');
                $extension = $image->getClientOriginalExtension();
                $fileName = 'banner-'.time().'.'.$extension;
                $options = ['target_dir' => $targetDir, 'file_name' => $fileName];
                $uploadResult = $imageUploader->upload($image, $options);
                if (!$uploadResult['status']) {
                    flashMessage('danger', $uploadResult['status_message']);
                    return back();
                }
                $banner->image = $fileName;
            }

            $banner->name = $request->name;
            $banner->type = $request->type;
            $banner->title = $request->title;
            $banner->subtitle = $request->subtitle;
            $banner->link = $request->link ?? '';
            $banner->status = $request->status;
            $banner->save();

            flashMessage('success', 'Updated Successfully');
        }

        return redirect('admin/banner');
    }

    public function delete(Request $request)
    {
        $banner = Banner::find($request->id);
        if (!$banner) {
            flashMessage('danger', 'Invalid ID');
            return redirect('admin/banner');
        }

        if ($banner->image) {
            $deleteImage = resolve(\App\Contracts\ImageHandlerInterface::class);
            $deleteImage->delete($banner->image, ['file_path' => '/images/banner/']);
        }
        $banner->delete();
        flashMessage('success', 'Deleted Successfully');
        return redirect('admin/banner');
    }
}
