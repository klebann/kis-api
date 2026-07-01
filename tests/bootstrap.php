<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

$projectDir = dirname(__DIR__);
$console = $projectDir.'/bin/console';

$commands = [
    sprintf('%s %s doctrine:database:create --if-not-exists --env=test --no-interaction', PHP_BINARY, escapeshellarg($console)),
    sprintf('%s %s doctrine:schema:drop --force --full-database --env=test --no-interaction', PHP_BINARY, escapeshellarg($console)),
    sprintf('%s %s doctrine:schema:create --env=test --no-interaction', PHP_BINARY, escapeshellarg($console)),
];

foreach ($commands as $command) {
    $output = [];
    $exitCode = 0;
    exec($command.' 2>&1', $output, $exitCode);

    if ($exitCode !== 0) {
        fwrite(STDERR, "Bootstrap command failed: {$command}\n".implode("\n", $output)."\n");
        exit(1);
    }
}
