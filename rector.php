<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths(
        [
            __DIR__ . '/app',
            __DIR__ . '/www',
        ]
    )
    // uncomment to reach your current PHP version
    // ->withPhpSets()
    ->withPhpSets(php84: true,)
    // doctrine
    ->withPreparedSets(
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        naming: true,
        instanceOf: true,
        doctrineCodeQuality: true,

    )
    ->withAttributesSets(
        doctrine: true,
    )
    ->withComposerBased(
        doctrine: true,
        netteUtils: true,

    )
    ->withDeadCodeLevel(10);
