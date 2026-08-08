<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    ->disableComposerAutoloadPathScan()
    ->setFileExtensions(['php'])
    ->addPathToScan(__DIR__ . '/src', false)
    ->addPathToScan(__DIR__ . '/tests', true)
    // ext-wincache is Windows-only, so it can't be loaded in the analyser's runtime.
    ->ignoreUnknownFunctions([
        'wincache_ucache_clear',
        'wincache_ucache_delete',
        'wincache_ucache_exists',
        'wincache_ucache_get',
        'wincache_ucache_set',
    ])
    ->ignoreErrorsOnExtension('ext-wincache', [ErrorType::UNUSED_DEPENDENCY]);
