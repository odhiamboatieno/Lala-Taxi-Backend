<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Start\Helpers;
use App\Models\AppVersion;
use Validator;
use DB;
use Illuminate\Validation\Rule;

class AppVersionController extends Controller
{
    protected $helper;  // Global variable for instance of Helpers

    public function __construct()
    {
        $this->helper = new Helpers;
    }

   
    public function index(Request $request)
    {
        $filters = [
            'search' => trim((string) $request->get('search', '')),
            'device_type' => trim((string) $request->get('device_type', '')),
            'user_type' => trim((string) $request->get('user_type', '')),
            'force_update' => trim((string) $request->get('force_update', '')),
        ];

        $versions = AppVersion::query();

        if ($filters['search'] !== '') {
            $versions->where('version', 'like', '%' . $filters['search'] . '%');
        }

        if ($filters['device_type'] !== '') {
            $versions->where('device_type', $filters['device_type']);
        }

        if ($filters['user_type'] !== '') {
            $versions->where('user_type', $filters['user_type']);
        }

        if ($filters['force_update'] !== '') {
            $versions->where('force_update', $filters['force_update']);
        }

        $versions = $versions->orderByDesc('id')->paginate(15)->withQueryString();

        return view('admin.app_version.view', compact('versions', 'filters'));
    }

   /*Add new Mobile App Version*/
    public function add(Request $request){
        
    	if(!$_POST) {
            return view('admin.app_version.add');
        }
        else if($request->submit) {
            $rules = [
                    'version'              => [
                        'required',
                        Rule::unique('app_version')->where(function ($query) use($request) {
                            return $query->where('version', $request->version)
                            ->where('device_type', $request->device_type)
                            ->where('user_type', $request->user_type);
                        })
                    ],
                    'device_type'       => 'required',
                    'user_type'       => 'required',
                    'force_update'            => 'required',
                    ];

            $niceNames = [
                        'version'          => 'Version',
                        'device_type'   => 'Device Type',
                        'user_type'        => 'User Type',
                        'force_update'        => 'Force Update',
                        ];            

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames); 

            if ($validator->fails()) {             
                return back()->withErrors($validator)->withInput(); 
            }
            else {

                $version = new AppVersion;
                $version->version         = $request->version;
                $version->device_type         = $request->device_type;
                $version->user_type         = $request->user_type;
                $version->force_update         = $request->force_update;
               
                $version->save();

                $this->helper->flash_message('success', 'Mobile App Version Added Successfully'); // Call flash message function

                return redirect('admin/mobile_app_version');
            }
        }
        else{
            return redirect('admin/mobile_app_version');
        }
    }

   
      /* Update MobileApp Version Details */
    

    public function update(Request $request)
    {
    	if(!$_POST) {
            $data['result'] = AppVersion::where('id',$request->id)->first();

            if(!$data['result']) {
            // Call flash message function
                $this->helper->flash_message('danger', 'Invalid ID');

                return redirect('admin/mobile_app_version');
            }

            return view('admin.app_version.edit', $data);
        }
        else if($request->submit) {

             $rules = [
                    'version'              => [
                        'required',
                        Rule::unique('app_version')->where(function ($query) use($request) {
                            return $query->where('version', $request->version)
                            ->where('device_type', $request->device_type)
                            ->where('user_type', $request->user_type)
                            ->where('id','!=',$request->id);
                        })
                    ],
                    'device_type'       => 'required',
                    'user_type'       => 'required',
                    'force_update'            => 'required'
                    ];

            $niceNames = [
                        'version'          => 'Version',
                        'device_type'   => 'Device Type',
                        'user_type'        => 'User Type',
                        'force_update'        => 'Force Update',
                        ];    

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames); 

            if ($validator->fails()) {

                return back()->withErrors($validator)->withInput(); // Form calling with
            }
            else {
              
                $version = AppVersion::where('id',$request->id)->first();
                $version->version         = $request->version;
                $version->device_type         = $request->device_type;
                $version->user_type         = $request->user_type;
                $version->force_update         = $request->force_update;  
                $version->save();

                $this->helper->flash_message('success', 'Mobile App Version Updated Successfully'); // Call flash message function

                return redirect('admin/mobile_app_version');
            }
        }
        else {
            return redirect('admin/mobile_app_version');
        }
    }

   // Remove the Mobile App Version
    public function delete(Request $request)
    {
    	$version = AppVersion::where('id',$request->id);
        $version->delete();
        $this->helper->flash_message('success', 'Mobile App Version successfully deleted');
        return redirect('admin/mobile_app_version');
    }

    
}
