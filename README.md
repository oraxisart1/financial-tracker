# Local development

```
docker run --rm \                                                                                    at 08:30:34 AM
    -u "$(id -u):$(id -g)" \
    -v $(pwd):/var/www/html \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs
```

```
./vendor/bin/sail artisan key:generate
```

```
./vendor/bin/sail migrate:fresh [--seed]
```

```
./vendor/bin/sail npm install && ./vendor/bin/sail npm run dev
```
