<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    new Dotenv()->bootEnv(dirname(__DIR__) . '/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0o000);
}

//// Load fixtures once before all tests
//// This ensures data exists before doctrine-test-bundle starts transaction wrapping
//if ($_SERVER['APP_ENV'] === 'test' && !isset($_ENV['FIXTURES_LOADED'])) {
//    echo "Loading fixtures for test database...\n";
//
//    passthru(sprintf(
//        'APP_ENV=test php "%s/../bin/console" doctrine:database:drop --force --if-exists --quiet',
//        __DIR__
//    ));
//
//    passthru(sprintf(
//        'APP_ENV=test php "%s/../bin/console" doctrine:database:create --if-not-exists --quiet',
//        __DIR__
//    ));
//
//    passthru(sprintf(
//        'APP_ENV=test php "%s/../bin/console" doctrine:migrations:migrate --no-interaction --quiet',
//        __DIR__
//    ));
//
//    passthru(sprintf(
//        'APP_ENV=test php "%s/../bin/console" doctrine:fixtures:load --no-interaction --quiet',
//        __DIR__
//    ));
//
//    $_ENV['FIXTURES_LOADED'] = true;
//
//    echo "Fixtures loaded successfully!\n";
//}
