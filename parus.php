<?php

namespace Danielyan\Parus;

use Danielyan\Parus\App\Network\Network;
use Danielyan\Parus\App\Repositories\PolygonRepository;
use Danielyan\Parus\App\Services\JsonService;
use Symfony\Component\Dotenv\Dotenv;

require 'vendor/autoload.php';

$config = new Dotenv();
$config->load(__DIR__ . "/.env");

PolygonRepository::connection();

$input = readline('Введите координаты полигона в формате X Y, X Y... или X Y,X Y... :');

$request = JsonService::post($input);

$response = Network::fetchData($request);

$polygons = JsonService::responseToPolygonArray($response);
$verifiedPolygons = $polygons['verified'];
$troublePolygons = $polygons['trouble'];

if (count($troublePolygons) == 0) {
    PolygonRepository::addAll($verifiedPolygons)['success'] ?
        print_r("\nВсе ".count($verifiedPolygons)." полигон(а,ов) успешно загружены или обновлены в БД.") :
        print_r("\nПри передаче данных возникла ошибка(и). Не все полигоны успешно загружены или обновлены в БД.");
} else {
    throw new \Exception('Troubles with some of the received from API polygons, ' .
    'no entry will be made to the database.');
}

PolygonRepository::closeConnection();