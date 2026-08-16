<?php

return [
    'request_timeout' => 120,
    'platform_fee_percent' => 10,
    'base_fare' => [
        'small' => 150,
        'large' => 300,
        'truck' => 800,
    ],
    'category_multipliers' => [
        'small' => 1.0,
        'large' => 1.5,
        'truck' => 2.5,
    ],
    'categories' => [
        ['key' => 'small', 'title' => 'Small Delivery', 'description' => 'Documents, gifts, and light packages (up to 5kg)', 'icon' => 'package_small'],
        ['key' => 'large', 'title' => 'Large Delivery', 'description' => 'Boxes, shopping bags, and larger items (5-25kg)', 'icon' => 'package_large'],
        ['key' => 'truck', 'title' => 'Truck Delivery', 'description' => 'Bulky or heavy delivery needs (25kg+)', 'icon' => 'truck'],
    ],
    'quick_tasks' => [
        ['key' => 'documents', 'title' => 'Documents'],
        ['key' => 'groceries', 'title' => 'Groceries'],
        ['key' => 'clothes', 'title' => 'Clothes'],
        ['key' => 'electronics', 'title' => 'Electronics'],
        ['key' => 'food', 'title' => 'Food'],
        ['key' => 'furniture', 'title' => 'Furniture'],
    ],
    'proof_of_delivery' => [
        'signature_required' => true,
        'photo_required' => true,
    ],
];
