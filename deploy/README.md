# LalaTaxi Backend — VPS deployment

The GitHub Actions pipeline builds the app image and pushes it to
**GHCR** (`ghcr.io/odhiamboatieno/lala-taxi-backend`). This folder contains
everything you need to run it on your own VPS with Docker Compose.

## What's in here

| File              | Purpose                                             |
|-------------------|-----------------------------------------------------|
| `docker-compose.yml` | Orchestrates app, nginx, mysql, redis, queue, reverb |
| `.env.example`    | Copy to `.env` and fill in real secrets             |
| `nginx.conf`      | Web server config (mounts into the nginx container) |
| `entrypoint.sh`   | Runtime bootstrap (storage, migrations) — baked into the image |

## Deploy on the VPS

```bash
# 1. Install Docker + compose plugin if not already installed
curl -fsSL https://get.docker.com | sh

# 2. Create a directory and copy the three files
mkdir -p /opt/lalataxi && cd /opt/lalataxi
#   upload docker-compose.yml, nginx.conf and .env.example

# 3. Create your real .env
cp .env.example .env
#   -> set APP_URL, DB_PASSWORD, MYSQL_ROOT_PASSWORD,
#      REVERB keys, JWT_SECRET, and generate APP_KEY/JWT_SECRET
#      (see "Generate secrets" below)

# 4. Start the stack
docker compose up -d

# 5. Watch the logs / check health
docker compose ps
docker compose logs -f app

# 6. Import your existing database dump (optional, fresh DB)
docker compose exec -T mysql sh -c 'exec mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" lalataxi' < lalataxi.sql
```

## Generate secrets

Run these inside a running app container:

```bash
docker compose exec app php artisan key:generate --force
docker compose exec app php artisan jwt:secret --force
docker compose exec app php artisan tinker --execute="echo Str::random(32).PHP_EOL;"   # reverb key
docker compose exec app php artisan tinker --execute="echo Str::random(32).PHP_EOL;"   # reverb secret
```

Then put the generated values into `.env` and `docker compose up -d` again.

## Updating after a new CI build

```bash
cd /opt/lalataxi
docker compose pull
docker compose up -d
```

## Realtime / Reverb notes

- Reverb listens on **0.0.0.0:8080** and is published on the VPS at `8080:8080`.
- Set `REVERB_HOST` to your server IP/domain, `REVERB_PORT=8080`,
  `REVERB_SCHEME=ws` for a plain WS connection, and match these in the mobile
  apps' `AppConstants` (reverbHost, reverbWsPort, reverbWssPort).
- For production TLS, terminate HTTPS in front (e.g. Caddy/Nginx/certbot),
  proxy `/app` and `/apps` to `reverb:8080`, then set `REVERB_PORT=443`,
  `REVERB_SCHEME=https`.

## Rolling back

```bash
docker compose pull ghcr.io/odhiamboatieno/lala-taxi-backend:<previous-tag>
```
Then set the image tag in `docker-compose.yml` to that tag and `docker compose up -d`.
