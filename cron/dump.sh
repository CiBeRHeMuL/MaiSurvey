#!/bin/bash

LOG_DIR="/home/a_gostev/app/api.mai-survey.ru/var/log"
DUMP_DIR="/home/a_gostev/app/api.mai-survey.ru/dump"
CONTAINER_NAME="mai-survey-postgresql-container"
DB_NAME="ms"
USER_NAME="ms"

mkdir -p "$DUMP_DIR"

DUMP_FILE="dump_$(date +\%Y-\%m-\%d_\%H:\%M).sql"

/usr/bin/docker exec "$CONTAINER_NAME" pg_dump \
    --dbname="$DB_NAME" \
    --file="/app/dump/$DUMP_FILE" \
    --format=c \
    --create \
    --clean \
    --if-exists \
    -U "$USER_NAME" >> "${LOG_DIR}/cron.log" 2>&1