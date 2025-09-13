<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/config',
        __DIR__ . '/public',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $rectorConfig->skip([
        __DIR__ . '/var',
        __DIR__ . '/vendor',
    ]);

    // Set your target PHP version
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_84, // Adjust to your PHP version
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::EARLY_RETURN,
        SetList::TYPE_DECLARATION,
    ]);

    $rectorConfig::configure()->withComposerBased(symfony: true);

    // Cache directory
    $rectorConfig->cacheDirectory(__DIR__ . '/var/rector');

    // Import names optimization
    $rectorConfig->importNames();
    $rectorConfig->importShortClasses(false);
};
