<?php

return [
    // Never used for production identities; the provisioning command enforces
    // both the environment and the exact isolated database name before writing.
    'password' => env('LALATAXI_ACCEPTANCE_PASSWORD'),
];
