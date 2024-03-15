# Liquid Framework

Liquid framework/boilerplate for rapid development.

## Develop

```
npm run dev
```

Available on part depending on .env

## Deploy

```
npm run build
```

## Run PHP tests

Single class:

```
  docker-compose run phpunit -c /app/phpunit.xml --filter RouterTest --debug
```

## Generate sitemap

```
php cli.php seo:generate-sitemap
```

## Analyse webpack

```
npm run webpack-analyse
```

## Update packages

```
docker run -it -v ${PWD}:/app -w /app --rm attlaz/php:8.2 composer update --ignore-platform-req=ext-redis
```

## Custom html tags

```html

<copy-block title="Title of block" icon="icon path">
    Content of block
</copy-block>
```
