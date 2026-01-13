<?php

declare(strict_types=1);

namespace Yiisoft\Cache\WinCache;

use Psr\SimpleCache\InvalidArgumentException as PsrInvalidArgumentException;
use RuntimeException;

final class InvalidArgumentException extends RuntimeException implements PsrInvalidArgumentException {}
