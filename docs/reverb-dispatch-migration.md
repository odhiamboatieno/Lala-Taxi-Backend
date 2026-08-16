# Reverb Dispatch Migration

## Runtime

- Set `BROADCAST_CONNECTION=reverb`
- Set `QUEUE_CONNECTION=redis`
- Run `php artisan migrate`
- Run queue worker: `php artisan queue:work --queue=default --tries=3`
- Run Reverb locally: `php artisan reverb:start --host=0.0.0.0 --port=8080`

## Reverse Proxy

Nginx example:

```nginx
location /app {
    proxy_http_version 1.1;
    proxy_set_header Host $host;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    proxy_pass http://127.0.0.1:8080;
}
```

Apache example:

```apache
ProxyPass "/app"  "ws://127.0.0.1:8080/app"
ProxyPassReverse "/app"  "ws://127.0.0.1:8080/app"
```

## Mobile Subscriptions

Driver:

- `private-driver.{driverId}`
- `private-trip.{tripId}` when assigned
- `presence-company.{companyId}.drivers`

Rider:

- `private-trip.rider.{riderId}`
- `private-trip.{tripId}` during active trip

Company dashboard:

- `private-company.{companyId}.dispatch`

## Fallback

- Reconnect WebSocket automatically.
- Refresh `GET /api/trips/current` after reconnect.
- Driver location should keep posting via `POST /api/driver/location/update`.

## Firebase Removal Checklist

- Keep Firebase only for offline push notifications.
- Disable Firebase Realtime Database trip request writes after driver app is verified on Reverb.
- Disable Firebase rider trip listeners after rider app is verified on Reverb.
- Remove GeoFire-based nearby lookup after Laravel-side matching is stable in production.
- Remove map marker Firebase subscriptions after dispatch dashboard uses Reverb events.
