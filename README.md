# Next2

[![phpunit](https://github.com/alexpts/php-next2/actions/workflows/phpunit.yml/badge.svg?branch=master)](https://github.com/alexpts/php-next2/actions/workflows/phpunit.yml)
[![codecov](https://codecov.io/gh/alexpts/php-next2/branch/master/graph/badge.svg?token=9yjCKeTLkN)](https://codecov.io/gh/alexpts/php-next2)

High performance mico framework


### Install

`composer require alexpts/next2`

### Docs

[http://alexpts.github.io/php-next2-docs/](http://alexpts.github.io/php-next2-docs/)

#### Hello World

```php
<?php

use PTS\Next2\Context\ContextInterface;
use PTS\Next2\MicroApp;
use PTS\Psr7\Response\JsonResponse;
use PTS\Psr7\ServerRequest;
use PTS\Psr7\Uri;

require_once './vendor/autoload.php';

$psr7Request = new ServerRequest('GET', new Uri('/'));

$app = new MicroApp;

$app->store->get('/', function(ContextInterface $ctx) {
    $ctx->response = new JsonResponse(['message' => 'Hello World!']);
});

$psr7Response = $app->handle($psr7Request); // psr-15 runner

```