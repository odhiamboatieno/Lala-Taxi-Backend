<?php

return [

    'Rider'     => [
        'environment' =>'development',
        'certificate' =>app_path().'/CabmeRiderDev.pem',
        'passPhrase'  =>'password',
        'service'     =>'apns'
    ],
    'Driver'     => [
        'environment' =>'development',
        'certificate' => app_path().'/CabmeDriverDev.pem',
        'passPhrase'  =>'password',
        'service'     =>'apns'
    ]

];



?>