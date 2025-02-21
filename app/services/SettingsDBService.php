<?php

namespace Danielyan\Parus\App\Services;

abstract class SettingsDBService
{
    public static function get(): array
    {
        return [
            'host' => $_ENV['DB_HOST'],
            'user' => $_ENV['DB_USERNAME'],
            'pass' => $_ENV['DB_PASSWORD'],
            'db' => $_ENV['DB_DATABASE'],
            'port' => $_ENV['DB_PORT'],
        ];
    }
}