# Financial Events Service

```
docker compose build --no-cache
docker compose up -d --force-recreate


php bin/console make:migration

php bin/console doctrine:migrations:migrate
```
