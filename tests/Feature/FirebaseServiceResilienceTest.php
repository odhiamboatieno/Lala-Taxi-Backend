<?php

namespace Tests\Feature;

use App\Services\FirebaseService;
use Tests\TestCase;
use Throwable;

class FirebaseServiceResilienceTest extends TestCase
{
    private function serviceWithoutConstructor(): FirebaseService
    {
        $reflection = new \ReflectionClass(FirebaseService::class);
        $service = $reflection->newInstanceWithoutConstructor();

        $property = $reflection->getProperty('firebase');
        $property->setAccessible(true);
        $property->setValue($service, null);

        return $service;
    }

    public function test_create_custom_token_returns_null_when_firebase_is_not_bootstrapped(): void
    {
        $service = $this->serviceWithoutConstructor();

        $this->assertNull($service->createCustomToken('rider@example.com - Rider'));
    }

    public function test_create_custom_token_returns_null_when_the_sdk_throws(): void
    {
        $reflection = new \ReflectionClass(FirebaseService::class);
        $service = $reflection->newInstanceWithoutConstructor();

        $firebase = new class {
            public function createAuth()
            {
                throw new \RuntimeException('credentials unavailable');
            }
        };

        $property = $reflection->getProperty('firebase');
        $property->setAccessible(true);
        $property->setValue($service, $firebase);

        $this->assertNull($service->createCustomToken('rider@example.com - Rider'));
    }

    public function test_create_custom_token_returns_null_when_auth_handles_fail(): void
    {
        $reflection = new \ReflectionClass(FirebaseService::class);
        $service = $reflection->newInstanceWithoutConstructor();

        $auth = new class {
            public function createCustomToken($uid)
            {
                throw new \RuntimeException('signing failed');
            }
        };

        $firebase = new class($auth) {
            private $auth;

            public function __construct($auth)
            {
                $this->auth = $auth;
            }

            public function createAuth()
            {
                return $this->auth;
            }
        };

        $property = $reflection->getProperty('firebase');
        $property->setAccessible(true);
        $property->setValue($service, $firebase);

        $this->assertNull($service->createCustomToken('rider@example.com - Rider'));
    }

    public function test_create_custom_token_never_throws_for_any_throwable(): void
    {
        $reflection = new \ReflectionClass(FirebaseService::class);
        $service = $reflection->newInstanceWithoutConstructor();

        $firebase = new class {
            public function createAuth()
            {
                throw new \Error('null dereference simulation');
            }
        };

        $property = $reflection->getProperty('firebase');
        $property->setAccessible(true);
        $property->setValue($service, $firebase);

        $result = null;
        $threw = false;

        try {
            $result = $service->createCustomToken('rider@example.com - Rider');
        } catch (Throwable $e) {
            $threw = true;
        }

        $this->assertFalse($threw, 'createCustomToken must never propagate an Error.');
        $this->assertNull($result);
    }
}
