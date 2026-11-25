
<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';

echo "BOOTSTRAP OK\n";

try {
    $status = $app->handleCommand(
        new Symfony\Component\Console\Input\ArgvInput([
            'artisan',
            '--version'
        ])
    );

    echo "\nCONSOLE STATUS: " . $status . "\n";

} catch (\Throwable $e) {
    echo "\nERROR CLASS: " . get_class($e);
    echo "\nERROR MESSAGE: " . $e->getMessage();
    echo "\nERROR FILE: " . $e->getFile();
    echo "\nERROR LINE: " . $e->getLine();
    echo "\nSTACK TRACE:\n" . $e->getTraceAsString();
}
