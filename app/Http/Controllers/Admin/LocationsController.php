<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Start\Helpers;
use App\Models\Location;
use App\Models\ManageFare;
use App\Services\LocationExtraFareService;
use Validator;
use DB;

class LocationsController extends Controller
{
    protected $helper;  // Global variable for instance of Helpers
    protected $extraFareService;

    public function __construct(LocationExtraFareService $extraFareService)
    {
        $this->helper = new Helpers;
        $this->extraFareService = $extraFareService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Class $dataTable instance of LocationsDatatable
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('admin.locations.page', $this->pageData($request));
    }

    /**
     * Add new Location
     *
     * @param array $request  Input values
     * @return \Illuminate\Http\Response
     */
    public function add(Request $request)
    {
    	if(!$_POST) {
            return view('admin.locations.page', $this->pageData($request));
        }
        else if($request->submit) {
            $rules = [
                    'name'              => 'required|unique:locations',
                    'coordinates'       => 'required',
                    'status'            => 'required'
                    ];

            $niceNames = [
                        'name'          => 'Name',
                        'coordinates'   => 'Coordinates',
                        'status'        => 'Status'
                        ];
            $message = [
                        'coordinates.required'   => 'Please mark any location.',
                        ];

            $validator = Validator::make($request->all(), $rules,$message);
            $validator->setAttributeNames($niceNames); 

            if ($validator->fails()) {
                $formatted_cords = $this->helper->getFormattedCoordinates($request->coordinates);
                // Form calling with Errors and Input values
                return back()->withErrors($validator)->withInput()->withInput(['formatted_coords' => $formatted_cords]);
            }
            else {
                $formatted_cords = $this->helper->getFormattedCoordinates($request->coordinates);
                // $is_already = $this->validate_cords($formatted_cords);
                // if($is_already) {
                //     return back()->withInput()->withInput(['formatted_coords' => $formatted_cords,'location_set' => 'Location already selected']);
                // }
                $location = new Location;
                $location->name         = $request->name;
                $location->coordinates  = 'POLYGON((' . $request->coordinates . '))';
                $location->status       = $request->status;
                $location->extra_fare_enabled = false;
                $location->extra_fare_percentage = 0;
                $location->extra_fare_reason = null;

                $location->save();

                $this->helper->flash_message('success', 'Location Added Successfully'); // Call flash message function

                return redirect('admin/locations');
            }
        }
        else{
            return redirect('admin/locations');
        }
    }

    /**
     * Update Location Details
     *
     * @param array $request  Input values
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
    	if(!$_POST) {
            $data['result'] = Location::find($request->id);

            if(!$data['result']) {
                // Call flash message function
                $this->helper->flash_message('danger', 'Invalid ID');

                return redirect('admin/locations');
            }

            return view('admin.locations.page', $this->pageData($request, $data['result']));
        }
        else if($request->submit) {
            $rules = [
                    'name'              => 'required',
                    'coordinates'       => 'required',
                    'status'            => 'required'
                    ];

            $niceNames = [
                        'name'          => 'Name',
                        'coordinates'   => 'Coordinates',
                        'status'        => 'Status'
                        ];
            $message = [
                        'coordinates.required'   => 'Please mark any location.',
                        ];

            $validator = Validator::make($request->all(), $rules,$message);
            $validator->setAttributeNames($niceNames); 

            if ($validator->fails()) {
                $formatted_cords = $this->helper->getFormattedCoordinates($request->coordinates);
                // Form calling with Errors and Input values
                return back()->withErrors($validator)->withInput()->withInput(['formatted_coords' => $formatted_cords]);
            }
            else {
                $formatted_cords = $this->helper->getFormattedCoordinates($request->coordinates);
                // $is_already = $this->validate_cords($formatted_cords,$request->id);
                // if($is_already) {
                //     return back()->withInput()->withInput(['formatted_coords' => $formatted_cords,'location_set' => 'Location already selected']);
                // }
                $location = Location::find($request->id);
                $location->name         = $request->name;
                $location->coordinates  = 'POLYGON((' . $request->coordinates . '))';
                $location->status       = $request->status;

                $location->save();

                $this->helper->flash_message('success', 'Location Added Successfully'); // Call flash message function

                return redirect('admin/locations');
            }
        }
        else {
            return redirect('admin/locations');
        }
    }

    /**
     * Remove the Location details
     *
     * @param array $request  Input values 
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $fare_count = ManageFare::where('location_id',$request->id)->get()->count();
    	$location = Location::find($request->id);

        if($fare_count > 0 ) {
            $this->helper->flash_message('danger', 'Sorry this Location has fare details. So cannot delete.');
        }
        else if(!is_null($location)) {
            $location->delete();
            // Call flash message function
            $this->helper->flash_message('success', 'Location successfully deleted');
        }
        else {
            // Call flash message function
            $this->helper->flash_message('danger', 'Location already deleted');
        }

        return redirect('admin/locations');
    }

    public function extraFare(Request $request)
    {
        $location = Location::find($request->id);

        if (! $location) {
            $this->helper->flash_message('danger', 'Invalid ID');
            return redirect('admin/locations');
        }

        if (! $_POST) {
            return view('admin.locations.extra_fare', [
                'location' => $location,
            ]);
        }

        $validator = Validator::make($request->all(), [
            'extra_fare_enabled' => 'nullable|in:0,1',
            'extra_fare_percentage' => 'nullable|numeric|min:0|max:1000',
            'extra_fare_reason' => 'nullable|string|max:191',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $enabled = (bool) $request->boolean('extra_fare_enabled');
        $percentage = $enabled ? round((float) $request->extra_fare_percentage, 2) : 0;

        $location->extra_fare_enabled = $enabled;
        $location->extra_fare_percentage = $percentage;
        $location->extra_fare_reason = $enabled ? trim((string) $request->extra_fare_reason) : null;
        $location->save();

        $this->helper->flash_message('success', 'Zone extra fare updated successfully');

        return redirect('admin/locations');
    }

    public function bulkExtraFare(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'enabled' => 'required|in:0,1',
            'extra_fare_percentage' => 'nullable|numeric|min:0|max:1000',
            'extra_fare_reason' => 'nullable|string|max:191',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $enabled = (bool) $request->boolean('enabled');
        $percentage = $enabled ? round((float) $request->extra_fare_percentage, 2) : 0;
        $reason = $enabled ? trim((string) $request->extra_fare_reason) : null;

        Location::query()->update([
            'extra_fare_enabled' => $enabled,
            'extra_fare_percentage' => $percentage,
            'extra_fare_reason' => $reason,
        ]);

        $this->helper->flash_message('success', $enabled ? 'All-zone extra fare applied successfully' : 'All-zone extra fare disabled successfully');

        return redirect('admin/locations');
    }

    public function toggleExtraFare(Request $request)
    {
        $location = Location::find($request->id);

        if (! $location) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid zone',
            ], 404);
        }

        $enabled = (bool) $request->boolean('enabled');
        $location->extra_fare_enabled = $enabled;
        if (! $enabled) {
            $location->extra_fare_percentage = 0;
            $location->extra_fare_reason = null;
        }
        elseif ((float) $location->extra_fare_percentage <= 0) {
            $location->extra_fare_percentage = 0;
        }
        $location->save();

        return response()->json([
            'status' => true,
            'message' => 'Zone extra fare status updated',
        ]);
    }

    public function toggleStatus(Request $request)
    {
        $location = Location::find($request->id);

        if (! $location) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid zone',
            ], 404);
        }

        $location->status = $request->boolean('enabled') ? 'Active' : 'Inactive';
        $location->save();

        return response()->json([
            'status' => true,
            'message' => 'Zone status updated',
            'location_status' => $location->status,
        ]);
    }

    /**
     * Validate selected location already selected or not
     *
     * @param array $request  Input values 
     * @return Boolean $success 
     */
    public function validate_cords($coordinate_data,$location_id = '')
    {
        $success = false;
        foreach ($coordinate_data[0] as $cord_data) {
            $match_location = Location::select(DB::raw("id,(ST_WITHIN( GeomFromText(
                        'POINT(".$cord_data['lat'].' '.$cord_data['lng'].")'),ST_GeomFromText(coordinates))) as available "))->where('id','<>',$location_id)->having('available','1')->first();
            if($match_location) {
                $success = true;
                return $success;
            }
        }
        return $success;
    }

    protected function pageData(Request $request, ?Location $editingLocation = null): array
    {
        $status = $request->get('status', 'all');
        $search = trim((string) $request->get('search'));

        $locations = Location::query()
            ->when($status === 'active', fn ($query) => $query->where('status', 'Active'))
            ->when($status === 'inactive', fn ($query) => $query->where('status', 'Inactive'))
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('id')
            ->get();

        $locationIds = $locations->pluck('id')->all();
        $tripCounts = empty($locationIds)
            ? collect()
            : DB::table('request')
                ->select('location_id', DB::raw('COUNT(*) as total'))
                ->whereIn('location_id', $locationIds)
                ->groupBy('location_id')
                ->pluck('total', 'location_id');

        $manageFareIds = ManageFare::query()->distinct()->pluck('location_id')->flip();

        $zoneRows = $locations->map(function ($location, $index) use ($tripCounts, $manageFareIds) {
            $tripCount = (int) ($tripCounts[$location->id] ?? 0);

            return [
                'index' => $index + 1,
                'id' => $location->id,
                'name' => $location->name,
                'trip_request_count' => $tripCount,
                'trip_request_volume' => $this->extraFareService->volumeLabel($tripCount),
                'extra_fare_enabled' => (bool) $location->extra_fare_enabled,
                'extra_fare_percentage' => round((float) $location->extra_fare_percentage, 2),
                'extra_fare_reason' => (string) $location->extra_fare_reason,
                'status' => $location->status,
                'can_delete' => ! $manageFareIds->has($location->id),
            ];
        });

        return [
            'editingLocation' => $editingLocation,
            'formatted_coords' => old('formatted_coords', $editingLocation?->co_ordinates ?? []),
            'statusFilter' => $status,
            'search' => $search,
            'zoneRows' => $zoneRows,
            'zoneCount' => $zoneRows->count(),
            'globalExtraFare' => $this->extraFareService->globalSummary(Location::query()->get()),
            'instructionGif' => asset('images/Instruction.gif'),
        ];
    }
}
