<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if (!isset($_SERVER['WKHTMLTOPDF_PATH'])) {
    $wkhtmltopdfPath = __DIR__ . '/bin/wkhtmltopdf';
    $_SERVER['WKHTMLTOPDF_PATH'] = $wkhtmltopdfPath;
    $_ENV['WKHTMLTOPDF_PATH'] = $wkhtmltopdfPath;
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
