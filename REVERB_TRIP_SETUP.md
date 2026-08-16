## Laravel Reverb Trip Flow Setup

This project already contains the Reverb-based trip dispatch flow on the backend.

### 1. Confirm environment values

Set these in `backend/.env`:

```env
QUEUE_CONNECTION=redis
BROADCAST_CONNECTION=reverb
CACHE_STORE=redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_USERNAME=default
REDIS_PASSWORD=your-redis-password
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1

REVERB_APP_ID=taxi-dispatch
REVERB_APP_KEY=taxi-dispatch-key
REVERB_APP_SECRET=taxi-dispatch-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080
```

For production, replace `localhost`, keys, and secrets with real values and switch to `https` / TLS.

Production example for a public domain:

```env
APP_URL=https://witaxi.wilaso.com

REVERB_APP_ID=taxi-dispatch
REVERB_APP_KEY=taxi-dispatch-key
REVERB_APP_SECRET=taxi-dispatch-secret

REVERB_HOST=witaxi.wilaso.com
REVERB_PORT=443
REVERB_SCHEME=https
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080
```

Important:

- `REVERB_HOST=localhost` is not valid for mobile clients in production.
- `REVERB_PORT=8080` is only appropriate if that port is publicly exposed to the internet.
- If Nginx or Apache terminates TLS and proxies websocket traffic to Reverb, mobile apps should use the public domain on `443` with `https`.

### 2. Make sure broadcasting auth is enabled

This codebase already registers both default and API broadcast auth routes in:

- `app/Providers/BroadcastServiceProvider.php`
- `routes/channels.php`

The mobile apps authenticate private channels through:

- `POST /api/broadcasting/auth`

Protected with:

- `jwt.verify`

### 3. Confirm the realtime trip routes are available

The Reverb trip flow depends on these API endpoints in `routes/api.php`:

- `POST /api/trips/request`
- `POST /api/trips/{trip}/cancel`
- `GET /api/trips/current`
- `GET /api/trips/{trip}`
- `POST /api/driver/online`
- `POST /api/driver/offline`
- `POST /api/driver/location/update`
- `GET /api/driver/current-request`
- `POST /api/trip-requests/{tripRequest}/accept`
- `POST /api/trip-requests/{tripRequest}/reject`
- `POST /api/trips/{trip}/arrived`
- `POST /api/trips/{trip}/start`
- `POST /api/trips/{trip}/complete`
- `POST /api/trips/{trip}/cancel-by-driver`

### 4. Start required backend services

From `backend` run:

```powershell
php artisan optimize:clear
php artisan migrate --force
php artisan reverb:start --host=0.0.0.0 --port=8080
php artisan queue:work
```

Also ensure Redis is running before `queue:work` and Reverb start, and that the `REDIS_USERNAME` / `REDIS_PASSWORD` values match the Redis server ACL or `requirepass` configuration.

### 5. Mobile app Reverb values must match the server

The Flutter apps read these Dart defines:

- `WITAXI_API_BASE_URL`
- `WITAXI_REVERB_KEY`
- `WITAXI_REVERB_CLUSTER`
- `WITAXI_REVERB_HOST`
- `WITAXI_REVERB_WS_PORT`
- `WITAXI_REVERB_WSS_PORT`
- `WITAXI_REVERB_SCHEME`

Required match:

- `WITAXI_REVERB_KEY == REVERB_APP_KEY`
- host/port/scheme must point to the Reverb server reachable by the device
- `WITAXI_API_BASE_URL` must expose `/api/broadcasting/auth`

### 6. Channel names used by this codebase

Rider app:

- `private-trip.rider.{riderId}`

Driver app:

- `private-driver.{driverId}`

Trip/company backend events may also broadcast to:

- `private-trip.{tripId}`
- `private-company.{companyId}.dispatch`

### 7. Expected event flow

1. Rider calls `POST /api/trips/request`
2. Backend creates trip with `searching_driver`
3. `DispatchTripRequestJob` sends `NewTripRequest` to nearby drivers
4. Driver accepts
5. Backend emits:
   - `TripDriverAssigned`
   - `TripStatusUpdated`
6. Driver location updates emit `DriverLocationUpdated`
7. Driver marks arrived, starts trip, completes trip
8. Backend emits `TripStatusUpdated`, then `TripCompleted`

### 8. Smoke test checklist

1. Rider logs in and requests a trip
2. Driver is online and receives `NewTripRequest`
3. Driver accepts within expiry window
4. Rider receives assigned driver details
5. Driver location update hits `POST /api/driver/location/update`
6. Rider receives status changes through Reverb
7. Completion updates trip status and clears driver availability

### 9. Important operational notes

- Queue worker is required because trip dispatch uses queued jobs.
- Reverb broadcast auth depends on valid JWT tokens.
- Redis is effectively required for the current queue/cache setup.
- If mobile clients cannot subscribe, test `/api/broadcasting/auth` first.

### 10. Known parity gaps to verify manually

The backend realtime stack is present, but the Flutter apps do not yet fully match the old Kotlin trip flow in a few areas:

- rider realtime driver-location handling is incomplete
- rider completed-trip invoice/payment flow is incomplete
- driver completed-trip payment/cash-collection flow is incomplete
- pending driver request restoration after app restart is limited
