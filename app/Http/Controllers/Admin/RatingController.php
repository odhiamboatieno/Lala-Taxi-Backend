<?php

/**
 * Rating Controller
 *
 * @package     Witaxi
 * @subpackage  Controller
 * @category    Rating
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Http\Start\Helpers;
use Illuminate\Support\Facades\DB;
use Validator;
use Auth;

class RatingController extends Controller
{
    protected $helper;  // Global variable for instance of Helpers

    public function __construct()
    {
        $this->helper = new Helpers;
    }

    /**
     * Load Datatable for Rating
     *
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search'));

        $ratings = Rating::query()
            ->where(function($query)  {
                if(LOGIN_USER_TYPE=='company') {
                    $query->whereHas('driver',function($q1){
                        $q1->where('company_id',auth('company')->id());
                    });
                }
            })
            ->join('users', 'users.id', '=', 'rating.user_id')
            ->join('trips', 'trips.id', '=', 'rating.trip_id')
            ->join('car_type', 'car_type.id', '=', 'trips.car_id')
            ->leftJoin('users as u', 'u.id', '=', 'rating.driver_id')
            ->leftJoin('companies', 'companies.id', '=', 'u.company_id')
            ->select([
                'rating.id',
                'rating.trip_id',
                'rating.driver_rating',
                'rating.rider_rating',
                'rating.rider_comments',
                'rating.driver_comments',
                'trips.created_at as date',
                'car_type.car_name as car_name',
                'companies.name as driver_company_name',
                DB::raw("CONCAT(COALESCE(u.first_name,''), ' ', COALESCE(u.last_name,'')) as driver_name"),
                DB::raw("CONCAT(COALESCE(users.first_name,''), ' ', COALESCE(users.last_name,'')) as rider_name"),
            ]);

        if ($search !== '') {
            $ratings->where(function ($query) use ($search) {
                $query->where('rating.trip_id', 'like', "%{$search}%")
                    ->orWhere('u.first_name', 'like', "%{$search}%")
                    ->orWhere('users.first_name', 'like', "%{$search}%")
                    ->orWhere('companies.name', 'like', "%{$search}%")
                    ->orWhere('car_type.car_name', 'like', "%{$search}%");
            });
        }

        $ratings = $ratings->orderByDesc('rating.id')->paginate(20)->withQueryString();

        return view('admin.rating.view', [
            'ratings' => $ratings,
            'search' => $search,
        ]);
    }

   
    /**
     * Delete Rating
     *
     * @param array $request    Input values
     * @return redirect     to Rating View
     */
    public function delete(Request $request)
    {
      
        Rating::where('id',$request->id)
        ->where(function($query)  {
            //For company user login, only get that company's driver rating
            if(LOGIN_USER_TYPE=='company') {
                $query->whereHas('driver',function($q1){
                    $q1->where('company_id',Auth::guard('company')->user()->id);
                });
            }
        })
        ->delete();
        $this->helper->flash_message('success', 'Deleted Successfully'); // Call flash message function
      
        return redirect(LOGIN_USER_TYPE.'/rating');  //redirect depends on login user is admin or company
    }
}
