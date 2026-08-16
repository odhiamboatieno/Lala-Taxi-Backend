<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Trips;
use App\Models\Request as RideRequest;
use Validator;

class ParcelTripsController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search'));

        $trips = Trips::query()
            ->with(['users', 'driver', 'parcelCategory', 'parcelTask', 'parcelRequest'])
            ->where('service_category', 'parcel')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('id', 'like', '%'.$search.'%')
                        ->orWhere('status', 'like', '%'.$search.'%')
                        ->orWhereHas('users', fn ($rider) => $rider->where('first_name', 'like', '%'.$search.'%')->orWhere('last_name', 'like', '%'.$search.'%'))
                        ->orWhereHas('driver', fn ($driver) => $driver->where('first_name', 'like', '%'.$search.'%')->orWhere('last_name', 'like', '%'.$search.'%'))
                        ->orWhereHas('parcelCategory', fn ($category) => $category->where('title', 'like', '%'.$search.'%'))
                        ->orWhereHas('parcelRequest', fn ($parcelRequest) => $parcelRequest->where('recipient_name', 'like', '%'.$search.'%'));
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.parcel_trips.index', [
            'trips' => $trips,
            'search' => $search,
        ]);
    }

    public function view(Request $request)
    {
        $trip = Trips::with(['users', 'driver', 'car_type', 'parcelRequest', 'parcelCategory', 'parcelTask'])
            ->where('service_category', 'parcel')
            ->find($request->id);

        if (!$trip) {
            flashMessage('danger', 'Trip not found');
            return redirect('admin/parcel_trips');
        }

        $data['trip'] = $trip;
        return view('admin.parcel_trips.view', $data);
    }

    public function cancel(Request $request)
    {
        if ($request->isMethod('GET')) {
            $data['trip'] = Trips::where('service_category', 'parcel')->find($request->id);
            if ($data['trip']) {
                return view('admin.parcel_trips.cancel', $data);
            }
            flashMessage('danger', 'Invalid ID');
        }

        if ($request->submit) {
            $trip = Trips::where('service_category', 'parcel')->find($request->id);
            if ($trip) {
                $trip->status = 'Cancelled';
                $trip->cancel_reason = $request->cancel_reason;
                $trip->save();

                $rideRequest = RideRequest::find($trip->request_id);
                if ($rideRequest) {
                    $rideRequest->status = 'Cancelled';
                    $rideRequest->save();
                }

                flashMessage('success', 'Parcel Trip Cancelled Successfully');
            } else {
                flashMessage('danger', 'Invalid ID');
            }
        }

        return redirect('admin/parcel_trips');
    }

    public function export()
    {
        $trips = Trips::where('service_category', 'parcel')
            ->with(['users', 'driver', 'parcelCategory', 'parcelRequest'])
            ->get();

        $filename = 'parcel_trips_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($trips) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Trip ID', 'Rider', 'Driver', 'Category', 'Recipient', 'Fare', 'Status', 'Date']);

            foreach ($trips as $trip) {
                fputcsv($file, [
                    $trip->id,
                    $trip->users ? $trip->users->first_name . ' ' . $trip->users->last_name : 'N/A',
                    $trip->driver ? $trip->driver->first_name . ' ' . $trip->driver->last_name : 'N/A',
                    $trip->parcelCategory ? $trip->parcelCategory->title : 'N/A',
                    $trip->parcelRequest ? $trip->parcelRequest->recipient_name : 'N/A',
                    $trip->fare ?? 'N/A',
                    $trip->status,
                    $trip->created_at,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
