<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://yiisoft.github.io/docs/images/yii_logo.svg" height="100px">
    </a>
    <h1 align="center">Yii Cache Library - WinCache Handler</h1>
    <br>
</p>

[![Latest Stable Version](https://poser.pugx.org/yiisoft/cache-wincache/v/stable.png)](https://packagist.org/packages/yiisoft/cache-wincache)
[![Total Downloads](https://poser.pugx.org/yiisoft/cache-wincache/downloads.png)](https://packagist.org/packages/yiisoft/cache-wincache)
[![Build Status](https://ci.appveyor.com/api/projects/status/qh46x5yf67k3i9hc/branch/master?svg=true)](https://ci.appveyor.com/project/samdark/cache-wincache)

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

### Support the project

[![Open Collective](https://img.shields.io/badge/Open%20Collective-sponsor-7eadf1?logo=open%20collective&logoColor=7eadf1&labelColor=555555)](https://opencollective.com/yiisoft)

### Follow updates

[![Official website](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)
[![Twitter](https://img.shields.io/badge/twitter-follow-1DA1F2?logo=twitter&logoColor=1DA1F2&labelColor=555555?style=flat)](https://twitter.com/yiiframework)
[![Telegram](https://img.shields.io/badge/telegram-join-1DA1F2?style=flat&logo=telegram)](https://t.me/yii3en)
[![Facebook](https://img.shields.io/badge/facebook-join-1DA1F2?style=flat&logo=facebook&logoColor=ffffff)](https://www.facebook.com/groups/yiitalk)
[![Slack](https://img.shields.io/badge/slack-join-1DA1F2?style=flat&logo=slack)](https://yiiframework.com/go/slack)

## License

The Yii Logging Library is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.

Maintained by [Yii Software](https://www.yiiframework.com/).
