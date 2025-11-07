# GitHub Secrets Configuration

Para que los workflows de despliegue funcionen, necesitas configurar los siguientes secrets en GitHub:

## Secrets Requeridos

### Secrets por Environment (guiamaestra-dev / guiamaestra-prod)
- `BRANCH_NAME` - Nombre de la rama (dev / main)
- `COMPOSE_FILE` - Nombre del archivo docker-compose (ej: `docker-compose.yml`)
- `INFRASTRUCTURE_PATH` - Ruta donde está el docker-compose (ej: `/home/user/guiamaestra/docker`)
- `REPO_PATH` - Ruta del repositorio en el servidor (ej: `/home/user/guiamaestra/repos/dev/moodle-guiamaestra`)
- `SERVER_HOST` - IP o dominio del servidor
- `SERVER_USER` - Usuario SSH del servidor
- `SERVER_SSH_KEY` - Clave privada SSH para acceso al servidor
- `SERVER_SSH_PASSPHRASE` - Passphrase de la clave SSH (si aplica)
- `SERVICE_NAME` - Nombre del servicio en docker-compose (ej: `moodle`)

## Cómo configurar los secrets

1. Ve a tu repositorio en GitHub
2. Settings → Secrets and variables → Actions
3. Click en "New repository secret"
4. Añade cada secret con su valor correspondiente

## Notas

- Los workflows se ejecutan automáticamente en push a `dev` o `main`
- También pueden ejecutarse manualmente desde la pestaña Actions
- Las imágenes se suben a Docker Hub con tags:
  - Dev: `moodle-guiamaestra:dev` y `moodle-guiamaestra:dev-{sha}`
  - Prod: `moodle-guiamaestra:latest` y `moodle-guiamaestra:{sha}`
