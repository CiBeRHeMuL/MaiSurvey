#!/bin/bash

cd /home/a_gostev/app/api.mai-survey.ru && \
mkdir -p dump >>/home/a_gostev/app/api.mai-survey.ru/var/log/cron.log 2>&1 && \
touch /home/a_gostev/app/api.mai-survey.ru/dump/dump_$(date +%Y-%m-%d_%H:%i).sql >>/home/a_gostev/app/api.mai-survey.ru/var/log/cron.log 2>&1 && \
docker exec -it mai-survey-postgresql-container pg_dump --dbname=ms --file="/app/dump/dump_$(date +%Y-%m-%d_%H:%i).sql" --format=c --create --clean --if-exists --verbose -U ms >>/home/a_gostev/app/api.mai-survey.ru/var/log/cron.log 2>&1