# Reverb `localhost:8080` Fix

Use this when trip dispatch fails with an error like:

```text
Pusher error: cURL error 7: Failed to connect to localhost port 8080
```

This means Laravel is trying to broadcast through Reverb, but the Reverb server is not reachable on the backend server.

## 1. Go to the backend directory

```bash
cd /www/wwwroot/taxi.lalacabs.co.ke
```

## 2. Check the current broadcast / Reverb values

```bash
grep -E "^(APP_URL|BROADCAST_CONNECTION|BROADCAST_DRIVER|QUEUE_CONNECTION|CACHE_STORE|REDIS_HOST|REDIS_PORT|REDIS_USERNAME|REDIS_PASSWORD|REVERB_APP_ID|REVERB_APP_KEY|REVERB_APP_SECRET|REVERB_HOST|REVERB_PORT|REVERB_SCHEME|REVERB_SERVER_HOST|REVERB_SERVER_PORT)=" .env
```

## 3. Use production-safe Reverb values

Open `.env`:

```bash
nano .env
```

Set these values:

```env
APP_URL=https://taxi.lalacabs.co.ke

BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=redis
CACHE_STORE=redis

REVERB_APP_ID=taxi-dispatch
REVERB_APP_KEY=taxi-dispatch-key
REVERB_APP_SECRET=taxi-dispatch-secret

REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080
```

Notes:

- `REVERB_HOST=127.0.0.1` is correct for Laravel broadcasting to the local Reverb process on the same server.
- Your mobile apps should still connect through your public domain / websocket proxy if you use one.
- If Redis uses auth, make sure `REDIS_USERNAME` and `REDIS_PASSWORD` are correct.

## 4. Clear cached config

```bash
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
```

## 5. Make sure Redis is running

```bash
redis-cli ping
```

Expected result:

```text
PONG
```

If your Redis has a password:

```bash
redis-cli -a "YOUR_REDIS_PASSWORD" ping
```

## 6. Start Reverb manually for testing

```bash
php artisan reverb:start --host=0.0.0.0 --port=8080
```

Keep that terminal open and, in another SSH terminal, test the port:

```bash
curl http://127.0.0.1:8080
```

If Reverb is up, you should no longer get `connection refused`.

## 7. Start the queue worker

In another terminal:

```bash
cd /www/wwwroot/taxi.lalacabs.co.ke
php artisan queue:work --tries=3 --timeout=120
```

Trip dispatch depends on the queue worker.

## 8. If dispatch still fails, inspect the effective config

```bash
php artisan tinker
```

Then run:

```php
config('broadcasting.default');
config('broadcasting.connections.reverb');
config('reverb.apps.apps.0.options');
```

You should see:

- broadcast default = `reverb`
- host = `127.0.0.1`
- port = `8080`
- scheme = `http`

Exit tinker:

```php
exit
```

## 9. Keep Reverb and queue worker running with Supervisor

Create Reverb supervisor config:

```bash
nano /etc/supervisor/conf.d/lalataxi-reverb.conf
```

Paste:

```ini
[program:lalataxi-reverb]
directory=/www/wwwroot/taxi.lalacabs.co.ke
command=/usr/bin/php artisan reverb:start --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
user=root
redirect_stderr=true
stdout_logfile=/www/wwwroot/taxi.lalacabs.co.ke/storage/logs/reverb.log
stopwaitsecs=3600
```

Create queue worker supervisor config:

```bash
nano /etc/supervisor/conf.d/lalataxi-queue.conf
```

Paste:

```ini
[program:lalataxi-queue]
directory=/www/wwwroot/taxi.lalacabs.co.ke
command=/usr/bin/php artisan queue:work --tries=3 --timeout=120
autostart=true
autorestart=true
numprocs=1
user=root
redirect_stderr=true
stdout_logfile=/www/wwwroot/taxi.lalacabs.co.ke/storage/logs/queue-worker.log
stopwaitsecs=3600
```

Reload supervisor:

```bash
supervisorctl reread
supervisorctl update
supervisorctl restart lalataxi-reverb
supervisorctl restart lalataxi-queue
supervisorctl status
```

## 10. Quick smoke test after restart

```bash
tail -f storage/logs/laravel.log
```

Then request a trip again from the rider app.

The previous error:

```text
Failed to connect to localhost port 8080
```

should be gone.

## 12. If queue output keeps showing `App\\Events\\... FAIL`

This means the queue worker is alive, but broadcasting is still failing or it is retrying already-failed event jobs.

### Inspect the newest application error

```bash
tail -n 100 storage/logs/laravel.log
```

### Check failed jobs

```bash
php artisan queue:failed
```

### Retry failed jobs after Reverb is fixed

```bash
php artisan queue:retry all
```

### If the worker is stuck on bad attempts, restart it

```bash
php artisan queue:restart
```

Then start the worker again if needed:

```bash
php artisan queue:work --tries=3 --timeout=120
```

### If you want to remove old failed broadcast jobs entirely

Use this only after confirming Reverb is healthy:

```bash
php artisan queue:flush
```

### Recommended recovery order

```bash
php artisan optimize:clear
php artisan queue:restart
php artisan queue:failed
php artisan queue:retry all
```

## 11. If you use Nginx websocket proxy for mobile clients

Laravel server-side broadcasting can stay on:

```env
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
```

while mobile clients use your public websocket domain through Nginx.

That is a normal production setup.
