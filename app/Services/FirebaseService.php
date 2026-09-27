<?php

/**
 * Firebase Service
 *
 * @package     LalaTaxi
 * @subpackage  Services
 * @category    Service
 * @author      Wilaso
 * @version     4.0.0
 * @link        https://Wilaso.com
*/

namespace App\Services;

use App\Models\User;
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Firebase\Messaging\MessageData;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Exception\FirebaseException;
use Throwable;

class FirebaseService
{

	protected $messaging;
	protected $firebase;
	protected $database;
    protected $databaseDisabled = false;

	/**
	 * Constructor
	 * 
	 */
	public function __construct()
	{
		$filePath = base_path() . api_credentials("service_account", "Firebase");
		try {

			$this->firebase = (new Factory())->withServiceAccount($filePath)->withDatabaseUri(api_credentials("database_url", "Firebase"));
			$this->database = $this->firebase->createDatabase();
			$this->messaging =  $this->firebase->createMessaging();
		} catch (FirebaseException $e) {
			\Log::error('Firebase SDK bootstrap failed', [
                'message' => $e->getMessage(),
                'service_account' => $filePath,
            ]);
            $this->firebase = $this->database = $this->messaging = null;
		} catch (Throwable $e) {
			\Log::error('Firebase bootstrap failed', [
                'message' => $e->getMessage(),
                'service_account' => $filePath,
            ]);
			$this->firebase = $this->database = null;
            $this->messaging = null;
		}
	}

	/**
	 * get Database With Reference
	 *
	 * @param String $[reference] [reference path]
	 * @return reference
	 */
	public function getDatabaseWithReference($reference)
	{
		if (!isset($this->database) || $this->databaseDisabled) {
			return null;
		}
		$base_path = env('FIREBASE_PREFIX', 'live') . '/';
		return $this->database->getReference($base_path . $reference);
	}

	/**
	 * Update Reference in Database
	 *
	 * @param String $[reference] [reference path]
	 * @param Json $[data]
	 * @return reference
	 */
	public function updateReference($reference, $data)
	{
		$databaseReference = $this->getDatabaseWithReference($reference);
		if (!isset($databaseReference)) {
			return null;
		}
		try {
			return $databaseReference->set($data);
		} catch (Throwable $e) {
			\Log::warning('Firebase reference update failed', [
				'reference' => $reference,
				'message' => $e->getMessage(),
			]);
            if ($this->shouldDisableRealtimeDatabase($e)) {
                $this->databaseDisabled = true;
            }
			return null;
		}
	}

	/**
	 * Delete Reference from Database
	 *
	 * @param String $[reference] [reference path]
	 * @return reference
	 */
	public function deleteReference($reference)
	{
		$reference = $this->getDatabaseWithReference($reference);
		if (!isset($reference)) {
			\Log::error('notification can\'t be deleted' . $reference);
			return null;
		}
		\Log::error('notification deleted' . $reference);
		return $reference->set(null);
	}

	/**
	 * Create Custom Token
	 *
	 * Returns null when Firebase is unavailable. The SDK bootstrap in the
	 * constructor already degrades to null when the service account is
	 * missing, so callers must not treat this as a fatal condition: push
	 * notification tokens are an enhancement, not a ride-workflow
	 * dependency.
	 *
	 * @param string $user_id
	 * @return string|null
	 */
	public function createCustomToken($user_id)
	{
		if ($this->firebase === null) {
			\Log::warning('Firebase unavailable; skipping custom token creation');
			return null;
		}

		try {
			$auth = $this->firebase->createAuth();
			$customToken = $auth->createCustomToken($user_id);
			return $customToken->toString();
		} catch (Throwable $e) {
			\Log::error('Failed to create Firebase custom token', [
				'message' => $e->getMessage(),
			]);
			return null;
		}
	}


	public function vertifyToken($idTokenString)
	{
		try {
			$auth = $this->firebase->createAuth();
			$verifiedIdToken = $auth->verifyIdToken($idTokenString);
		} catch (FailedToVerifyToken $e) {
			echo 'The token is invalid: ' . $e->getMessage();
		}

		$uid = $verifiedIdToken->claims()->get('sub');

		$user = $auth->firebase->getUser($uid);
		return $user;
	}


	/**
	 * Create Android noftication
	 *
	 * @param User unique id
	 * @return token
	 */
	public function sendAndroidNotification($push_tittle, $data, $user_type, $device_id, $change_title = 0, $firebase_data = [])
	{
		try {
            if (!$this->messaging || empty($device_id)) {
                return false;
            }

			$data = $data + $firebase_data;
            $isIncomingRide = ($data['notification_type'] ?? null) === 'trip_request'
                || isset($data['trip_request'])
                || isset($data['trip_request_uuid']);

			//userId vai ser o topic (TODO: mudar para token)
			$message = CloudMessage::withTarget('token', $device_id)
				->withData(['custom' => json_encode($data)])
				->withDefaultSounds()
				->withAndroidConfig(AndroidConfig::fromArray([
					'ttl' => $isIncomingRide ? '0s' : '15s',
					'priority' => 'high',
                    'notification' => [
                        'channel_id' => $isIncomingRide ? 'lalataxi_incoming_ride' : 'lalataxi_driver_updates',
                        'notification_priority' => $isIncomingRide ? 'PRIORITY_MAX' : 'PRIORITY_HIGH',
                        'default_sound' => true,
                        'default_vibrate_timings' => true,
                        'visibility' => 'PUBLIC',
                    ],
				]));

			return  $this->messaging->send($message);
		} catch (\Throwable $e) {
            $this->handleMessagingFailure($device_id, $e, 'android');
            return false;
		}
	}




	/**
	 * Create Apple noftication
	 *
	 * @param User unique id
	 * @return token
	 */
	public function sendAppleNotification($push_tittle, $data, $user_type, $device_id, $change_title = 0, $firebase_data = [])
	{
		try {
            if (!$this->messaging || empty($device_id)) {
                return false;
            }
			$title = $user_type;
			if ($change_title) {
				if (isset($data['custom_message'])) {
					$title = $data['custom_message']['push_title'];
					unset($data['custom_message']['push_title']);
				}

				if (isset($data['chat_notification'])) {
					$title = $push_tittle;
					$push_tittle = $data['chat_notification']['title'];
					unset($data['chat_notification']['title']);
				}
			}
			//merge firbase data 
			$data = $data + $firebase_data;
            $isIncomingRide = ($data['notification_type'] ?? null) === 'trip_request'
                || isset($data['trip_request'])
                || isset($data['trip_request_uuid']);

			//userId vai ser o topic (TODO: mudar para token)
			$message = CloudMessage::withTarget('token', $device_id)
				->withData(['custom' => json_encode($data)])
				->withNotification(['title' => 	$title, 'body' => $push_tittle])
				->withDefaultSounds()
				->withApnsConfig(ApnsConfig::fromArray([
					'headers' => [
						'apns-priority' => '10',
                        'apns-push-type' => 'alert',
					],
                    'payload' => [
                        'aps' => [
                            'content-available' => 1,
                            'sound' => 'default',
                            'interruption-level' => $isIncomingRide ? 'time-sensitive' : 'active',
                        ],
                    ],
				]));

			return  $this->messaging->send($message);
		} catch (\Throwable $e) {
            $this->handleMessagingFailure($device_id, $e, 'ios');
            return false;
		}
	}

    protected function shouldDisableRealtimeDatabase(Throwable $e): bool
    {
        $message = strtolower($e->getMessage());

        return str_contains($message, 'unauthorized request')
            || str_contains($message, 'permission_denied')
            || str_contains($message, 'permission denied');
    }

    protected function handleMessagingFailure(string $deviceId, Throwable $e, string $platform): void
    {
        $message = $e->getMessage();

        logger("push {$platform} notification error : " . $message);

        if (!$this->shouldForgetDeviceToken($message)) {
            return;
        }

        User::where('device_id', $deviceId)->update([
            'device_id' => '',
        ]);

        \Log::warning('Firebase device token cleared after messaging failure', [
            'platform' => $platform,
            'device_id_suffix' => substr($deviceId, -12),
            'message' => $message,
        ]);
    }

    protected function shouldForgetDeviceToken(string $message): bool
    {
        $normalized = strtolower($message);

        return str_contains($normalized, 'senderid mismatch')
            || str_contains($normalized, 'registration token is not a valid fcm registration token')
            || str_contains($normalized, 'requested entity was not found')
            || str_contains($normalized, 'registration-token-not-registered')
            || str_contains($normalized, 'not found');
    }
}
