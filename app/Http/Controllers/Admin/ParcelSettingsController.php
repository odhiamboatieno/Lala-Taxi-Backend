<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ParcelSetting;
use Validator;

class ParcelSettingsController extends Controller
{
    public function index()
    {
        $data['settings'] = ParcelSetting::all()->pluck('value', 'key')->toArray();
        $data['types'] = ParcelSetting::all()->pluck('type', 'key')->toArray();
        return view('admin.parcel_settings.index', $data);
    }

    public function update(Request $request)
    {
        if ($request->submit) {
            $settings = [
                'platform_fee_percent' => ['type' => 'int', 'description' => 'Platform service fee percentage applied to parcel fares'],
                'request_timeout' => ['type' => 'int', 'description' => 'Time in seconds before a parcel request times out'],
                'signature_required' => ['type' => 'bool', 'description' => 'Whether a signature is required for proof of delivery'],
                'photo_required' => ['type' => 'bool', 'description' => 'Whether a photo is required for proof of delivery'],
                'max_weight_kg' => ['type' => 'int', 'description' => 'Maximum allowed package weight in kilograms'],
                'driver_commission_percent' => ['type' => 'int', 'description' => 'Driver commission percentage for parcel deliveries'],
            ];

            foreach ($settings as $key => $config) {
                $value = $request->input($key);

                if ($config['type'] === 'bool') {
                    $value = $value ? 'true' : 'false';
                }

                $setting = ParcelSetting::where('key', $key)->first();
                if ($setting) {
                    $setting->value = $value;
                    $setting->save();
                } else {
                    ParcelSetting::create([
                        'key' => $key,
                        'value' => $value,
                        'type' => $config['type'],
                        'description' => $config['description'],
                    ]);
                }
            }

            flashMessage('success', 'Parcel Settings Updated Successfully');
        }

        return redirect('admin/parcel_settings');
    }
}
