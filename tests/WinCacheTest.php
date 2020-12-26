<?php

declare(strict_types=1);

namespace Yiisoft\Cache\WinCache\Tests;

use ArrayIterator;
use DateInterval;
use Exception;
use IteratorAggregate;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;
use ReflectionObject;
use stdClass;
use Yiisoft\Cache\WinCache\WinCache;

use function array_keys;
use function array_map;
use function extension_loaded;
use function ini_get;
use function is_array;
use function is_object;

final class WinCacheTest extends TestCase
{
    private WinCache $cache;

    public function setUp(): void
    {
        $this->cache = new WinCache();
    }

    public static function setUpBeforeClass(): void
    {
        if (!extension_loaded('wincache')) {
            self::markTestSkipped('Required extension "wincache" is not loaded');
        }

        if (!ini_get('wincache.enablecli')) {
            self::markTestSkipped('Wincache is installed but not enabled. Enable with "wincache.enablecli" from php.ini. Skipping.');
        }

        if (!ini_get('wincache.ucenabled')) {
            self::markTestSkipped('Wincache user cache disabled. Enable with "wincache.ucenabled" from php.ini. Skipping.');
        }
    }

    public function dataProvider(): array
    {
        $object = new stdClass();
        $object->test_field = 'test_value';

        return [
            'integer' => ['test_integer', 1],
            'double' => ['test_double', 1.1],
            'string' => ['test_string', 'a'],
            'boolean_true' => ['test_boolean_true', true],
            'boolean_false' => ['test_boolean_false', false],
            'object' => ['test_object', $object],
            'array' => ['test_array', ['test_key' => 'test_value']],
            'null' => ['test_null', null],
            'supported_key_characters' => ['AZaz09_.', 'b'],
            '64_characters_key_max' => ['bVGEIeslJXtDPrtK.hgo6HL25_.1BGmzo4VA25YKHveHh7v9tUP8r5BNCyLhx4zy', 'c'],
            'string_with_number_key' => ['111', 11],
            'string_with_number_key_1' => ['022', 22],
        ];
    }

    /**
     * @dataProvider dataProvider
     *
     * @param $key
     * @param $value
     *
     * @throws InvalidArgumentException
     */
    public function testSet($key, $value): void
    {
        for ($i = 0; $i < 2; $i++) {
            $this->assertTrue($this->cache->set($key, $value));
        }
    }

    /**
     * @dataProvider dataProvider
     *
     * @param $key
     * @param $value
     *
     * @throws InvalidArgumentException
     */
    public function testGet($key, $value): void
    {
        $this->cache->set($key, $value);
        $valueFromCache = $this->cache->get($key, 'default');

        $this->assertSameExceptObject($value, $valueFromCache);
    }

    /**
     * @dataProvider dataProvider
     *
     * @param $key
     * @param $value
     *
     * @throws InvalidArgumentException
     */
    public function testValueInCacheCannotBeChanged($key, $value): void
    {
        $this->cache->set($key, $value);
        $valueFromCache = $this->cache->get($key, 'default');

        $this->assertSameExceptObject($value, $valueFromCache);

        if (is_object($value)) {
            $originalValue = clone $value;
            $valueFromCache->test_field = 'changed';
            $value->test_field = 'changed';
            $valueFromCacheNew = $this->cache->get($key, 'default');
            $this->assertSameExceptObject($originalValue, $valueFromCacheNew);
        }
    }

    /**
     * @dataProvider dataProvider
     *
     * @param $key
     * @param $value
     *
     * @throws InvalidArgumentException
     */
    public function testHas($key, $value): void
    {
        $this->cache->set($key, $value);

        $this->assertTrue($this->cache->has($key));
        // check whether exists affects the value
        $this->assertSameExceptObject($value, $this->cache->get($key));

        $this->assertTrue($this->cache->has($key));
        $this->assertFalse($this->cache->has('not_exists'));
    }

    public function testGetNonExistent(): void
    {
        $this->assertNull($this->cache->get('non_existent_key'));
    }

    /**
     * @dataProvider dataProvider
     *
     * @param $key
     * @param $value
     *
     * @throws InvalidArgumentException
     */
    public function testDelete($key, $value): void
    {
        $this->cache->set($key, $value);

        $this->assertSameExceptObject($value, $this->cache->get($key));
        $this->assertTrue($this->cache->delete($key));
        $this->assertNull($this->cache->get($key));
    }

    /**
     * @dataProvider dataProvider
     *
     * @param $key
     * @param $value
     *
     * @throws InvalidArgumentException
     */
    public function testClear($key, $value): void
    {
        foreach ($this->dataProvider() as $datum) {
            $this->cache->set($datum[0], $datum[1]);
        }

        $this->assertTrue($this->cache->clear());
        $this->assertNull($this->cache->get($key));
    }

    /**
     * @dataProvider dataProviderSetMultiple
     *
     * @param int|null $ttl
     *
     * @throws InvalidArgumentException
     */
    public function testSetMultiple(?int $ttl): void
    {
        $data = $this->getDataProviderData();
        $this->cache->setMultiple($data, $ttl);

        foreach ($data as $key => $value) {
            $this->assertSameExceptObject($value, $this->cache->get((string) $key));
        }
    }

    /**
     * @return array testing multiSet with and without expiry
     */
    public function dataProviderSetMultiple(): array
    {
        return [
            [null],
            [2],
        ];
    }

    public function testGetMultiple(): void
    {
        $data = $this->getDataProviderData();
        $keys = array_map('strval', array_keys($data));

        $this->cache->setMultiple($data);

        $this->assertSameExceptObject($data, $this->cache->getMultiple($keys));
    }

    public function testGetMultipleWithKeysNotExist(): void
    {
        $this->assertSameExceptObject(
            ['key-1' => null, 'key-2' => null],
            $this->cache->getMultiple(['key-1', 'key-2']),
        );
    }

    public function testDeleteMultiple(): void
    {
        $data = $this->getDataProviderData();
        $keys = array_map('strval', array_keys($data));

        $this->cache->setMultiple($data);

        $this->assertSameExceptObject($data, $this->cache->getMultiple($keys));

        $this->cache->deleteMultiple($keys);

        $emptyData = array_map(static fn ($v) => null, $data);

        $this->assertSameExceptObject($emptyData, $this->cache->getMultiple($keys));
    }

    public function testNegativeTtl(): void
    {
        $this->cache->setMultiple(['a' => 1, 'b' => 2]);

        $this->assertTrue($this->cache->has('a'));
        $this->assertTrue($this->cache->has('b'));

        $this->cache->set('a', 11, -1);

        $this->assertFalse($this->cache->has('a'));
    }

    /**
     * @dataProvider dataProviderNormalizeTtl
     *
     * @param mixed $ttl
     * @param mixed $expectedResult
     *
     * @throws ReflectionException
     */
    public function testNormalizeTtl($ttl, $expectedResult): void
    {
        $reflection = new ReflectionObject($this->cache);
        $method = $reflection->getMethod('normalizeTtl');
        $method->setAccessible(true);
        $result = $method->invokeArgs($this->cache, [$ttl]);
        $method->setAccessible(false);

        $this->assertSameExceptObject($expectedResult, $result);
    }

    /**
     * Data provider for {@see testNormalizeTtl()}
     *
     * @throws Exception
     *
     * @return array test data
     */
    public function dataProviderNormalizeTtl(): array
    {
        return [
            [123, 123],
            ['123', 123],
            ['', -1],
            [null, 0],
            [0, -1],
            [new DateInterval('PT6H8M'), 6 * 3600 + 8 * 60],
            [new DateInterval('P2Y4D'), 2 * 365 * 24 * 3600 + 4 * 24 * 3600],
        ];
    }

    /**
     * @dataProvider iterableProvider
     *
     * @param array $array
     * @param iterable $iterable
     *
     * @throws InvalidArgumentException
     */
    public function testValuesAsIterable(array $array, iterable $iterable): void
    {
        $this->cache->setMultiple($iterable);

        $this->assertSameExceptObject($array, $this->cache->getMultiple(array_keys($array)));
    }

    public function iterableProvider(): array
    {
        return [
            'array' => [
                ['a' => 1, 'b' => 2,],
                ['a' => 1, 'b' => 2,],
            ],
            'ArrayIterator' => [
                ['a' => 1, 'b' => 2,],
                new ArrayIterator(['a' => 1, 'b' => 2,]),
            ],
            'IteratorAggregate' => [
                ['a' => 1, 'b' => 2,],
                new class() implements IteratorAggregate {
                    public function getIterator()
                    {
                        return new ArrayIterator(['a' => 1, 'b' => 2,]);
                    }
                },
            ],
            'generator' => [
                ['a' => 1, 'b' => 2,],
                (static function () {
                    yield 'a' => 1;
                    yield 'b' => 2;
                })(),
            ],
        ];
    }

    public function testSetWithDateIntervalTtl(): void
    {
        $this->cache->set('a', 1, new DateInterval('PT1H'));
        $this->assertSameExceptObject(1, $this->cache->get('a'));

        $this->cache->setMultiple(['b' => 2]);
        $this->assertSameExceptObject(['b' => 2], $this->cache->getMultiple(['b']));
    }

    public function invalidKeyProvider(): array
    {
        return [
            'int' => [1],
            'float' => [1.1],
            'null' => [null],
            'bool' => [true],
            'object' => [new stdClass()],
            'callable' => [fn () => 'key'],
            'psr-reserved' => ['{}()/\@:'],
            'empty-string' => [''],
        ];
    }

    /**
     * @dataProvider invalidKeyProvider
     *
     * @param mixed $key
     */
    public function testGetThrowExceptionForInvalidKey($key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->cache->get($key);
    }

    /**
     * @dataProvider invalidKeyProvider
     *
     * @param mixed $key
     */
    public function testSetThrowExceptionForInvalidKey($key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->cache->set($key, 'value');
    }

    /**
     * @dataProvider invalidKeyProvider
     *
     * @param mixed $key
     */
    public function testDeleteThrowExceptionForInvalidKey($key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->cache->delete($key);
    }

    /**
     * @dataProvider invalidKeyProvider
     *
     * @param mixed $key
     */
    public function testGetMultipleThrowExceptionForInvalidKeys($key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->cache->getMultiple([$key]);
    }

    /**
     * @dataProvider invalidKeyProvider
     *
     * @param mixed $key
     */
    public function testGetMultipleThrowExceptionForInvalidKeysNotIterable($key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->cache->getMultiple($key);
    }

    /**
     * @dataProvider invalidKeyProvider
     *
     * @param mixed $key
     */
    public function testSetMultipleThrowExceptionForInvalidKeysNotIterable($key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->cache->setMultiple($key);
    }

    /**
     * @dataProvider invalidKeyProvider
     *
     * @param mixed $key
     */
    public function testDeleteMultipleThrowExceptionForInvalidKeys($key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->cache->deleteMultiple([$key]);
    }

    /**
     * @dataProvider invalidKeyProvider
     *
     * @param mixed $key
     */
    public function testDeleteMultipleThrowExceptionForInvalidKeysNotIterable($key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->cache->deleteMultiple($key);
    }

    /**
     * @dataProvider invalidKeyProvider
     *
     * @param mixed $key
     */
    public function testHasThrowExceptionForInvalidKey($key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->cache->has($key);
    }

    private function getDataProviderData(): array
    {
        $dataProvider = $this->dataProvider();
        $data = [];

        foreach ($dataProvider as $item) {
            $data[$item[0]] = $item[1];
        }

        return $data;
    }

    private function assertSameExceptObject($expected, $actual): void
    {
        // assert for all types
        $this->assertEquals($expected, $actual);

        // no more asserts for objects
        if (is_object($expected)) {
            return;
        }

        // asserts same for all types except objects and arrays that can contain objects
        if (!is_array($expected)) {
            $this->assertSame($expected, $actual);
            return;
        }

        // assert same for each element of the array except objects
        foreach ($expected as $key => $value) {
            if (!is_object($value)) {
                $this->assertSame($expected[$key], $actual[$key]);
            } else {
                $this->assertEquals($expected[$key], $actual[$key]);
            }
        }
    }
}
