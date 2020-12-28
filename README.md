<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://yiisoft.github.io/docs/images/yii_logo.svg" height="100px">
    </a>
    <h1 align="center">Yii Cache Library - WinCache Handler</h1>
    <br>
</p>

[![Latest Stable Version](https://poser.pugx.org/yiisoft/cache-wincache/v/stable.png)](https://packagist.org/packages/yiisoft/cache-wincache)
[![Total Downloads](https://poser.pugx.org/yiisoft/cache-wincache/downloads.png)](https://packagist.org/packages/yiisoft/cache-wincache)
[![Build status](https://github.com/yiisoft/cache-wincache/workflows/build/badge.svg)](https://github.com/yiisoft/cache-wincache/actions?query=workflow%3Abuild)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/cache-wincache/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/cache-wincache/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yiisoft/cache-wincache/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/cache-wincache/?branch=master)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Fcache-wincache%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/cache-wincache/master)
[![static analysis](https://github.com/yiisoft/cache-wincache/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/cache-wincache/actions?query=workflow%3A%22static+analysis%22)
[![type-coverage](https://shepherd.dev/github/yiisoft/cache-wincache/coverage.svg)](https://shepherd.dev/github/yiisoft/cache-wincache)


This package uses the PHP [Windows Cache](https://sourceforge.net/projects/wincache/)
([see also](https://www.php.net/manual/book.wincache.php)) extension and implements
[PSR-16](https://www.php-fig.org/psr/psr-16/) cache.

## Installation

The package could be installed with composer:

```
composer install yiisoft/cache-wincache
```

## General usage

The package does not contain any additional functionality for interacting with the cache,
except those defined in the [PSR-16](https://www.php-fig.org/psr/psr-16/) interface.

```php
$cache = new \Yiisoft\Cache\WinCache\WinCache();
$parameters = ['user_id' => 42];
$key = 'demo';

// try retrieving $data from cache
$data = $cache->get($key);

if ($data === null) {
    // $data is not found in cache, calculate it from scratch
    $data = calculateData($parameters);
    
    // store $data in cache for an hour so that it can be retrieved next time
    $cache->set($key, $data, 3600);
}

// $data is available here
```

In order to delete value you can use:

```php
$cache->delete($key);
// Or all cache
$cache->clear();
```

To work with values in a more efficient manner, batch operations should be used:

- `getMultiple()`
- `setMultiple()`
- `deleteMultiple()`

This package can be used as a cache handler for the [Yii Caching Library](https://github.com/yiisoft/cache).

### Unit testing

The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```shell
./vendor/bin/phpunit
```

### Mutation testing

The package tests are checked with [Infection](https://infection.github.io/) mutation framework. To run it:

```shell
./vendor/bin/infection
```

### Static analysis

The code is statically analyzed with [Psalm](https://psalm.dev/). To run static analysis:

```shell
./vendor/bin/psalm
```

### Support the project

[![Open Collective](https://img.shields.io/badge/Open%20Collective-sponsor-7eadf1?logo=open%20collective&logoColor=7eadf1&labelColor=555555)](https://opencollective.com/yiisoft)

### Follow updates

[![Official website](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)
[![Twitter](https://img.shields.io/badge/twitter-follow-1DA1F2?logo=twitter&logoColor=1DA1F2&labelColor=555555?style=flat)](https://twitter.com/yiiframework)
[![Telegram](https://img.shields.io/badge/telegram-join-1DA1F2?style=flat&logo=telegram)](https://t.me/yii3en)
[![Facebook](https://img.shields.io/badge/facebook-join-1DA1F2?style=flat&logo=facebook&logoColor=ffffff)](https://www.facebook.com/groups/yiitalk)
[![Slack](https://img.shields.io/badge/slack-join-1DA1F2?style=flat&logo=slack)](https://yiiframework.com/go/slack)

## License

The Yii Cache Library - WinCache Handler is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more incache-wincacheation.

Maintained by [Yii Software](https://www.yiiframework.com/).
