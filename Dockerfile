# Dockerfile para Moodle

FROM bitnami/moodle:latest

# Copiar customizaciones
COPY ./custom-plugins /bitnami/moodle/

# Configurar permisos
USER root
RUN chown -R daemon:daemon /bitnami/moodle
USER daemon

EXPOSE 8080 8443