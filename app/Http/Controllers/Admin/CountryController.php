<?php

/**
 * Country Controller
 *
 * @package     Witaxi
 * @subpackage  Controller
 * @category    Country
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\User;
use App\Http\Start\Helpers;
use Validator;
use Illuminate\Contracts\View\View;

class CountryController extends Controller
{
    protected $helper;  // Global variable for instance of Helpers

    public function __construct()
    {
        $this->helper = new Helpers;
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search'));
        $countries = Country::query();

        if ($search !== '') {
            $countries->where(function ($query) use ($search) {
                $query->where('id', 'like', "%{$search}%")
                    ->orWhere('short_name', 'like', "%{$search}%")
                    ->orWhere('long_name', 'like', "%{$search}%")
                    ->orWhere('iso3', 'like', "%{$search}%")
                    ->orWhere('num_code', 'like', "%{$search}%")
                    ->orWhere('phone_code', 'like', "%{$search}%");
            });
        }

        return view('admin.country.view', [
            'countries' => $countries->orderByDesc('id')->paginate(20)->withQueryString(),
            'search' => $search,
        ]);
    }

    /**
     * Add a New Country
     *
     * @param array $request  Input values
     * @return redirect     to Country view
     */
    public function add(Request $request)
    {
        if(!$_POST)
        {
            return view('admin.country.add');
        }
        else if($request->submit)
        {
            // Add Country Validation Rules
            $rules = [
                    'short_name' => 'required|unique:country',
                    'long_name'  => 'required|unique:country',
                    'phone_code' => 'required'
                    ];

            // Add Country Validation Custom Names
            $niceNames = [
                        'short_name' => 'Short Name',
                        'long_name'  => 'Long Name',
                        'phone_code' => 'Phone Code'
                        ];

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames); 

            if ($validator->fails()) 
            {
                return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
            }
            else
            {
            $country = new Country;

            $country->short_name = $request->short_name;
            $country->long_name  = $request->long_name;
            $country->iso3       = $request->iso3;
            $country->num_code   = $request->num_code;
            $country->phone_code = $request->phone_code;

            $country->save();

            $this->helper->flash_message('success', 'Added Successfully'); // Call flash message function

            return redirect('admin/country');
            }
        }
        else
        {
            return redirect('admin/country');
        }
    }

    /**
     * Update Country Details
     *
     * @param array $request    Input values
     * @return redirect     to Country View
     */
    public function update(Request $request)
    {
        if(!$_POST)
        {
			$data['result'] = Country::find($request->id);

            return view('admin.country.edit', $data);
        }
        else if($request->submit)
        {
            // Edit Country Validation Rules
            $rules = [
                    'short_name' => 'required|unique:country,short_name,'.$request->id,
                    'long_name'  => 'required|unique:country,long_name,'.$request->id,
                    'phone_code' => 'required'
                    ];

            // Edit Country Validation Custom Fields Name
            $niceNames = [
                        'short_name' => 'Short Name',
                        'long_name'  => 'Long Name',
                        'phone_code' => 'Phone Code'
                        ];

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames); 

            if ($validator->fails()) 
            {
                return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
            }
            else
            {
                $country = Country::find($request->id);

			    $country->short_name = $request->short_name;
                $country->long_name  = $request->long_name;
                $country->iso3       = $request->iso3;
                $country->num_code   = $request->num_code;
                $country->phone_code = $request->phone_code;

                $country->save();

                $this->helper->flash_message('success', 'Updated Successfully'); // Call flash message function

                return redirect('admin/country');
            }
        }
        else
        {
            return redirect('admin/country');
        }
    }

    /**
     * Delete Country
     *
     * @param array $request    Input values
     * @return redirect     to Country View
     */
    public function delete(Request $request)
    {
        $country_code = Country::find($request->id);

        $user = User::where('country_code',$country_code->phone_code)->count();
        
        if($user){
            $this->helper->flash_message('error', 'Some User have this Country. So, We cannot delete the country.'); // Call flash message function
        }else{
            Country::find($request->id)->delete();
            $this->helper->flash_message('success', 'Deleted Successfully'); // Call flash message function
        }

        return redirect('admin/country');
    }
}
