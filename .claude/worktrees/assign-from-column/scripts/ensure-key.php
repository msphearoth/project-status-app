<?php

/**
 * Generates APP_KEY only if it's missing, so re-running `make setup` on a
 * machine that's already configured doesn't rotate the key and invalidate
 * existing sessions / encrypted cookies.
 */
$envPath = __DIR__.'/../.env';
$contents = file_get_contents($envPath);

if (preg_match('/^APP_KEY=.+$/m', $contents)) {
    echo "APP_KEY already set, skipping.\n";
    exit(0);
}

passthru('php '.escapeshellarg(__DIR__.'/../artisan').' key:generate --ansi', $exitCode);
exit($exitCode);
