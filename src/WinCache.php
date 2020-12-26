<?php

declare(strict_types=1);

namespace Yiisoft\Cache\WinCache;

use DateInterval;
use DateTime;
use Psr\SimpleCache\CacheInterface;
use Traversable;

use function array_fill_keys;
use function array_flip;
use function array_keys;
use function array_map;
use function gettype;
use function is_array;
use function is_iterable;
use function is_string;
use function iterator_to_array;
use function strpbrk;
use function wincache_ucache_clear;
use function wincache_ucache_delete;
use function wincache_ucache_exists;
use function wincache_ucache_get;
use function wincache_ucache_set;

/**
 * WinCache provides Windows Cache caching in terms of an application component.
 *
 * To use this application component, the [WinCache PHP extension](https://sourceforge.net/projects/wincache/)
 * must be loaded. Also note that "wincache.ucenabled" should be set to "1" in your php.ini file.
 *
 * See {@see \Psr\SimpleCache\CacheInterface} for common cache operations that are supported by WinCache.
 */
final class WinCache implements CacheInterface
{
    private const TTL_INFINITY = 0;
    private const TTL_EXPIRED = -1;

    public function get($key, $default = null)
    {
        $this->validateKey($key);
        $value = wincache_ucache_get($key, $success);
        return $success ? $value : $default;
    }

    public function set($key, $value, $ttl = null): bool
    {
        $this->validateKey($key);
        $ttl = $this->normalizeTtl($ttl);

        if ($ttl <= self::TTL_EXPIRED) {
            return $this->delete($key);
        }

        return wincache_ucache_set($key, $value, $ttl);
    }

    public function delete($key): bool
    {
        $this->validateKey($key);
        return wincache_ucache_delete($key);
    }

    public function clear(): bool
    {
        return wincache_ucache_clear();
    }

    public function getMultiple($keys, $default = null): iterable
    {
        $keys = $this->iterableToArray($keys);
        $this->validateKeys($keys);
        $valuesFromCache = $this->normalizeWinCacheOutput(wincache_ucache_get($keys));
        $values = array_fill_keys($keys, $default);

        foreach ($values as $key => $value) {
            $values[$key] = $valuesFromCache[$key] ?? $value;
        }

        return $values;
    }

    public function setMultiple($values, $ttl = null): bool
    {
        $values = $this->iterableToArray($values);
        $this->validateKeysOfValues($values);
        $ttl = $this->normalizeTtl($ttl);

        return wincache_ucache_set($values, null, $ttl) === [];
    }

    public function deleteMultiple($keys): bool
    {
        $keys = $this->iterableToArray($keys);
        $this->validateKeys($keys);
        $deleted = wincache_ucache_delete($keys);

        if (!is_array($deleted)) {
            return false;
        }

        $deleted = array_flip($deleted);

        foreach ($keys as $expectedKey) {
            if (!isset($deleted[$expectedKey])) {
                return false;
            }
        }

        return true;
    }

    public function has($key): bool
    {
        $this->validateKey($key);
        return wincache_ucache_exists($key);
    }

    /**
     * Normalizes cache TTL handling `null` value, strings and {@see DateInterval} objects.
     *
     * @param DateInterval|int|string|null $ttl The raw TTL.
     *
     * @return int TTL value as UNIX timestamp.
     */
    private function normalizeTtl($ttl): int
    {
        if ($ttl === null) {
            return self::TTL_INFINITY;
        }

        if ($ttl instanceof DateInterval) {
            return (new DateTime('@0'))->add($ttl)->getTimestamp();
        }

        $ttl = (int) $ttl;
        return $ttl > 0 ? $ttl : self::TTL_EXPIRED;
    }

    /**
     * Converts iterable to array. If provided value is not iterable it throws an InvalidArgumentException.
     *
     * @param mixed $iterable
     *
     * @return array
     */
    private function iterableToArray($iterable): array
    {
        if (!is_iterable($iterable)) {
            throw new InvalidArgumentException('Iterable is expected, got ' . gettype($iterable));
        }

        return $iterable instanceof Traversable ? iterator_to_array($iterable) : (array) $iterable;
    }

    /**
     * @param mixed $key
     */
    private function validateKey($key): void
    {
        if (!is_string($key) || $key === '' || strpbrk($key, '{}()/\@:')) {
            throw new InvalidArgumentException('Invalid key value.');
        }
    }

    /**
     * @param array $keys
     */
    private function validateKeys(array $keys): void
    {
        foreach ($keys as $key) {
            $this->validateKey($key);
        }
    }

    /**
     * @param array $values
     */
    private function validateKeysOfValues(array $values): void
    {
        $keys = array_map('strval', array_keys($values));
        $this->validateKeys($keys);
    }

    /**
     * Normalizes keys returned from wincache_ucache_get in multiple mode. If one of the keys is an integer (123) or a
     * string representation of an integer ('123') the returned key from the cache doesn't equal neither to an integer
     * nor a string ($key !== 123 and $key !== '123'). Coping element from the returned array one by one to the new
     * array fixes this issue.
     *
     * @param array $values
     *
     * @return array
     */
    private function normalizeWinCacheOutput(array $values): array
    {
        $normalizedValues = [];

        foreach ($values as $key => $value) {
            $normalizedValues[$key] = $value;
        }

        return $normalizedValues;
    }
}
