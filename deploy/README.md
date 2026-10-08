# LalaTaxi Backend — VPS deployment

Use [CORPORATE_RELEASE.md](CORPORATE_RELEASE.md) for the required backup/restore, immutable-image, code audit, controlled startup and acceptance gates. These scripts do not enable bookings or perform financial operations. Do not deploy by blindly running `compose up` against an existing database.

The GitHub Actions pipeline builds the app image and pushes it to
**GHCR** (`ghcr.io/odhiamboatieno/lala-taxi-backend`). This folder contains
everything you need to run it on your own VPS with Docker Compose.

## What's in here

| File              | Purpose                                             |
|-------------------|-----------------------------------------------------|
| `docker-compose.yml` | App, nginx, mysql, redis, queue, reverb and scheduler |
| `.env.example`    | Copy to `.env` and fill in real secrets             |
| `nginx.conf`      | Web server config (mounts into the nginx container) |
| `entrypoint.sh`   | Runtime bootstrap (storage, migrations) — baked into the image |
| `preflight.sh` / `release.sh` | Read-only validation / backup-gated controlled release |
| `release-manifest.json` | CI-packaged tested commit and immutable image digest |

## Deploy on the VPS

```bash
# 1. Install Docker + compose plugin if not already installed
curl -fsSL https://get.docker.com | sh

# 2. For a FIRST installation, create a directory and copy the complete CI bundle.
# For an upgrade, use the existing Compose directory/project and volumes.
mkdir -p /opt/lalataxi && cd /opt/lalataxi
#   upload the complete lalataxi-deploy-bundle artifact

# 3. Create your real .env
cp .env.example .env
#   -> set APP_URL, DB_PASSWORD, MYSQL_ROOT_PASSWORD,
#      REVERB keys, JWT_SECRET, APP_KEY and SMTP through credential management
#      (retain all existing keys and DB credentials on an upgrade)

# 4. Set LALATAXI_IMAGE to the tested digest and validate without starting services
sh preflight.sh --config-only

# 5. Watch the logs / check health
docker compose ps
docker compose logs -f app

# 6. Follow CORPORATE_RELEASE.md; verify backup/restore before sh release.sh.
# Never import a dump over an existing production database.
```

## Secrets

For a first isolated installation, provision application, JWT, database and realtime keys through credential management before booting. Never regenerate APP_KEY or JWT_SECRET during a routine upgrade. Do not print secrets into deployment logs or commit operator `.env` files.

## Updating after a new CI build

```bash
sh preflight.sh --config-only
# Supply verified BACKUP_REFERENCE and RESTORE_EVIDENCE before this command:
sh release.sh
```

## Realtime / Reverb notes

- Reverb listens on **0.0.0.0:8080** and is published on the VPS at `8080:8080`.
- Backend server-side broadcasting uses the internal `REVERB_HOST=reverb`,
  `REVERB_PORT=8080`, `REVERB_SCHEME=http`. Do not replace these with mobile client settings.
- For production TLS, terminate HTTPS in front and proxy `/app` and `/apps`
  to `reverb:8080`. Configure mobile clients with the public host and WSS/443;
  restrict the published internal port according to existing ingress policy.

## Rolling back

Use the recovery section of CORPORATE_RELEASE.md: pin the previous tested digest, stop readers, retain financial schema/history and verify compatibility. No destructive database rollback is implied.
