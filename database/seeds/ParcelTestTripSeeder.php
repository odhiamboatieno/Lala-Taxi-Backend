<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\CarType;
use App\Models\ParcelCategory;
use App\Models\ParcelTask;

class ParcelTestTripSeeder extends Seeder
{
    public function run()
    {
        $rider = User::where('user_type', 'Rider')->first();
        $driver = User::where('user_type', 'Driver')->first();
        $carType = CarType::where('status', 'Active')->first();
        $smallCategory = ParcelCategory::where('key', 'small')->first();
        $largeCategory = ParcelCategory::where('key', 'large')->first();
        $docTask = \App\Models\ParcelTask::where('key', 'documents')->first();
        $groceryTask = \App\Models\ParcelTask::where('key', 'groceries')->first();

        if (!$rider || !$driver || !$carType || !$smallCategory || !$largeCategory) {
            $this->command->warn('Skipping ParcelTestTripSeeder: missing required data. Run other seeders first.');
            return;
        }

        $testTrips = [
            [
                'status' => 'Pending',
                'pickup_location' => 'Westlands, Nairobi',
                'drop_location' => 'Kilimani, Nairobi',
                'pickup_latitude' => -1.2621,
                'pickup_longitude' => 36.8076,
                'drop_latitude' => -1.2921,
                'drop_longitude' => 36.7876,
                'parcel_category_key' => 'small',
                'parcel_task_key' => 'documents',
                'recipient_name' => 'Jane Smith',
                'recipient_phone' => '+254798765432',
                'recipient_address' => 'Kilimani Road, Nairobi',
                'parcel_description' => 'Legal documents in sealed envelope',
                'estimated_weight' => '0.5',
            ],
            [
                'status' => 'In Transit',
                'pickup_location' => 'CBD, Nairobi',
                'drop_location' => 'Karen, Nairobi',
                'pickup_latitude' => -1.2864,
                'pickup_longitude' => 36.8249,
                'drop_latitude' => -1.3129,
                'drop_longitude' => 36.6978,
                'parcel_category_key' => 'large',
                'parcel_task_key' => 'groceries',
                'recipient_name' => 'John Doe',
                'recipient_phone' => '+254712345678',
                'recipient_address' => 'Karen Road, Nairobi',
                'parcel_description' => 'Weekly grocery shopping',
                'estimated_weight' => '8.0',
            ],
            [
                'status' => 'Completed',
                'pickup_location' => 'Airport, Nairobi',
                'drop_location' => 'Westlands, Nairobi',
                'pickup_latitude' => -1.3192,
                'pickup_longitude' => 36.9278,
                'drop_latitude' => -1.2621,
                'drop_longitude' => 36.8076,
                'parcel_category_key' => 'small',
                'parcel_task_key' => 'packages',
                'recipient_name' => 'Alice Brown',
                'recipient_phone' => '+254723456789',
                'recipient_address' => 'Westlands Mall, Nairobi',
                'parcel_description' => 'Online shopping package',
                'estimated_weight' => '2.0',
            ],
            [
                'status' => 'Cancelled',
                'pickup_location' => 'Thika Road, Nairobi',
                'drop_location' => 'Mombasa Road, Nairobi',
                'pickup_latitude' => -1.2200,
                'pickup_longitude' => 36.8900,
                'drop_latitude' => -1.3100,
                'drop_longitude' => 36.8900,
                'parcel_category_key' => 'large',
                'parcel_task_key' => 'electronics',
                'recipient_name' => 'Bob Wilson',
                'recipient_phone' => '+254734567890',
                'recipient_address' => 'Mombasa Road, Nairobi',
                'parcel_description' => 'Laptop computer',
                'estimated_weight' => '3.5',
            ],
            [
                'status' => 'Completed',
                'pickup_location' => 'Lavington, Nairobi',
                'drop_location' => 'Parklands, Nairobi',
                'pickup_latitude' => -1.2750,
                'pickup_longitude' => 36.7750,
                'drop_latitude' => -1.2550,
                'drop_longitude' => 36.8150,
                'parcel_category_key' => 'small',
                'parcel_task_key' => 'pharmacy',
                'recipient_name' => 'Carol Davis',
                'recipient_phone' => '+254745678901',
                'recipient_address' => 'Parklands Avenue, Nairobi',
                'parcel_description' => 'Prescription medication',
                'estimated_weight' => '0.3',
            ],
        ];

        foreach ($testTrips as $index => $tripData) {
            $category = $tripData['parcel_category_key'] === 'small' ? $smallCategory : $largeCategory;
            $task = \App\Models\ParcelTask::where('key', $tripData['parcel_task_key'])->first();

            $request = \App\Models\Request::create([
                'user_id' => $rider->id,
                'driver_id' => $driver->id,
                'pickup_latitude' => $tripData['pickup_latitude'],
                'pickup_longitude' => $tripData['pickup_longitude'],
                'drop_latitude' => $tripData['drop_latitude'],
                'drop_longitude' => $tripData['drop_longitude'],
                'pickup_location' => $tripData['pickup_location'],
                'drop_location' => $tripData['drop_location'],
                'car_id' => $carType->id,
                'payment_mode' => 'Cash',
                'service_category' => 'parcel',
                'status' => $tripData['status'],
                'estimated_fare' => $category->base_fare + (5 * 15),
                'timezone' => 'Africa/Nairobi',
                'parcel_category_id' => $category->id,
                'parcel_task_id' => $task?->id,
            ]);

            \App\Models\ParcelRequest::create([
                'request_id' => $request->id,
                'parcel_category_id' => $category->id,
                'parcel_task_id' => $task?->id,
                'category' => $category->key,
                'task_type' => $task?->key ?? 'other',
                'recipient_name' => $tripData['recipient_name'],
                'recipient_phone' => $tripData['recipient_phone'],
                'recipient_phone_dial_code' => '+254',
                'recipient_phone_iso_code' => 'KE',
                'recipient_address' => $tripData['recipient_address'],
                'parcel_description' => $tripData['parcel_description'],
                'estimated_weight' => $tripData['estimated_weight'],
                'special_instructions' => null,
                'pickup_contact_name' => 'Sender ' . ($index + 1),
                'pickup_contact_phone' => '+25470000000' . $index,
                'proof_of_delivery_required' => true,
            ]);

            if (in_array($tripData['status'], ['In Transit', 'Completed'])) {
                \App\Models\Trips::create([
                    'request_id' => $request->id,
                    'user_id' => $rider->id,
                    'driver_id' => $driver->id,
                    'car_id' => $carType->id,
                    'pickup_latitude' => $tripData['pickup_latitude'],
                    'pickup_longitude' => $tripData['pickup_longitude'],
                    'drop_latitude' => $tripData['drop_latitude'],
                    'drop_longitude' => $tripData['drop_longitude'],
                    'pickup_location' => $tripData['pickup_location'],
                    'drop_location' => $tripData['drop_location'],
                    'status' => $tripData['status'],
                    'payment_mode' => 'Cash',
                    'payment_status' => $tripData['status'] === 'Completed' ? 'Completed' : 'Pending',
                    'service_category' => 'parcel',
                    'parcel_category_id' => $category->id,
                    'parcel_task_id' => $task?->id,
                    'fare' => $category->base_fare + (5 * 15),
                    'base_fare' => $category->base_fare,
                    'distance' => 5.0,
                    'duration' => 15,
                    'otp' => rand(1000, 9999),
                    'timezone' => 'Africa/Nairobi',
                    'parcel_picked_up_at' => $tripData['status'] === 'In Transit' ? now()->subMinutes(30) : now()->subHours(2),
                    'parcel_delivered_at' => $tripData['status'] === 'Completed' ? now()->subHour() : null,
                ]);
            }

            $this->command->info("Created test parcel trip #{$index}: {$tripData['status']} - {$tripData['parcel_category_key']}");
        }
    }
}
