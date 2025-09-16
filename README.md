# Liquid Framework

Liquid framework/boilerplate for rapid development.

## Develop

```bash
npm run dev
```

Available on part depending on .env

## Deploy

```bash
npm run build
```

## Run PHP tests

Single class:

```bash
  docker-compose run phpunit -c /app/phpunit.xml --filter RouterTest --debug
```

## Generate sitemap

```bash
php cli.php seo:generate-sitemap
```

## Analyse webpack

```bash
npm run webpack-analyse
```

## Update packages

```bash
docker run -it -v ${PWD}:/app -w /app --rm attlaz/php:8.4 composer update --ignore-platform-req=ext-redis
```

## (re) build dev environment

````bash
docker-compose build
````

## Custom html tags

```html

<copy-block title="Title of block" icon="icon path">
    Content of block
</copy-block>
```
