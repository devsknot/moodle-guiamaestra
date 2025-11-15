#!/bin/bash
set -e

# Colores para logs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== Iniciando Moodle GuiaMaestra ===${NC}"

# Esperar a que PostgreSQL esté disponible
echo -e "${YELLOW}Esperando a PostgreSQL...${NC}"
until PGPASSWORD=$MOODLE_DB_PASSWORD psql -h "$MOODLE_DB_HOST" -U "$MOODLE_DB_USER" -d "$MOODLE_DB_NAME" -c '\q' 2>/dev/null; do
  echo -e "${YELLOW}PostgreSQL no está listo - esperando...${NC}"
  sleep 2
done
echo -e "${GREEN}✓ PostgreSQL está listo${NC}"

# Verificar si config.php existe
if [ ! -f /var/www/html/public/config.php ]; then
    echo -e "${YELLOW}Creando config.php...${NC}"
    
    cat > /var/www/html/public/config.php <<EOF
<?php
unset(\$CFG);
global \$CFG;
\$CFG = new stdClass();

\$CFG->dbtype    = 'pgsql';
\$CFG->dblibrary = 'native';
\$CFG->dbhost    = '${MOODLE_DB_HOST}';
\$CFG->dbname    = '${MOODLE_DB_NAME}';
\$CFG->dbuser    = '${MOODLE_DB_USER}';
\$CFG->dbpass    = '${MOODLE_DB_PASSWORD}';
\$CFG->prefix    = 'mdl_';
\$CFG->dboptions = array(
    'dbpersist' => false,
    'dbsocket'  => false,
    'dbport'    => '${MOODLE_DB_PORT:-5432}',
);

\$CFG->wwwroot   = '${MOODLE_URL}';
\$CFG->dataroot  = '/var/www/moodledata';
\$CFG->directorypermissions = 02777;
\$CFG->admin = 'admin';

// Configuración de proxy reverso
\$CFG->reverseproxy = false;
\$CFG->sslproxy = ${MOODLE_SSLPROXY:-false};

// Configuración de sesiones
\$CFG->session_handler_class = '\core\session\database';
\$CFG->session_database_acquire_lock_timeout = 120;

require_once(__DIR__ . '/lib/setup.php');
EOF

    chown www-data:www-data /var/www/html/public/config.php
    chmod 640 /var/www/html/public/config.php
    echo -e "${GREEN}✓ config.php creado${NC}"
else
    echo -e "${GREEN}✓ config.php ya existe${NC}"
fi

# Verificar si Moodle está instalado
if [ ! -f /var/www/moodledata/.moodle_installed ]; then
    echo -e "${YELLOW}Instalando Moodle...${NC}"

    if PGPASSWORD=$MOODLE_DB_PASSWORD psql -h "$MOODLE_DB_HOST" -U "$MOODLE_DB_USER" -d "$MOODLE_DB_NAME" -tA -c "SELECT 1 FROM mdl_config LIMIT 1" >/dev/null 2>&1; then
        echo -e "${YELLOW}BD ya contiene tablas de Moodle, se omite instalación CLI${NC}"
        touch /var/www/moodledata/.moodle_installed
        chown www-data:www-data /var/www/moodledata/.moodle_installed
    else
        # Ejecutar instalación CLI de Moodle
        php /var/www/html/public/admin/cli/install_database.php \
            --lang=es \
            --adminuser="${MOODLE_ADMIN:-admin}" \
            --adminpass="${MOODLE_ADMIN_PASSWORD:-Admin123!}" \
            --adminemail="${MOODLE_ADMIN_EMAIL:-admin@example.com}" \
            --fullname="GuiaMaestra LMS" \
            --shortname="GuiaMaestra" \
            --agree-license

        # Marcar como instalado
        touch /var/www/moodledata/.moodle_installed
        chown www-data:www-data /var/www/moodledata/.moodle_installed
        echo -e "${GREEN}✓ Moodle instalado correctamente${NC}"
    fi
else
    echo -e "${GREEN}✓ Moodle ya está instalado${NC}"

    # Ejecutar actualizaciones si las hay
    echo -e "${YELLOW}Verificando actualizaciones...${NC}"
    php /var/www/html/public/admin/cli/upgrade.php --non-interactive || true
fi

# Configurar permisos finales
echo -e "${YELLOW}Configurando permisos...${NC}"
chown -R www-data:www-data /var/www/html
chown -R www-data:www-data /var/www/moodledata
echo -e "${GREEN}✓ Permisos configurados${NC}"

# Limpiar caché
echo -e "${YELLOW}Limpiando caché...${NC}"
php /var/www/html/public/admin/cli/purge_caches.php || true
echo -e "${GREEN}✓ Caché limpiado${NC}"

echo -e "${GREEN}=== Moodle GuiaMaestra iniciado correctamente ===${NC}"
echo -e "${GREEN}URL: ${MOODLE_URL}${NC}"

# Ejecutar el comando principal (Apache)
exec "$@"
