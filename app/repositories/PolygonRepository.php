<?php

namespace Danielyan\Parus\App\Repositories;

use Danielyan\Parus\App\Services\SettingsDBService;
use Danielyan\Parus\App\Models\Polygon;
use SafeMySQL;

class PolygonRepository
{
    private static $connection;
    private static $table = 'polygons';
    private static $fields = ['county', 'district', 'quarter', 'plot', 'polygon'];
    
    public static function connection()
    {
        self::$connection = new SafeMySQL(SettingsDBService::get());
    }

    public static function closeConnection()
    {
        self::$connection = null;
    }

    /**
     * Функция добавления/обновления полигона в БД.
     * $valuesSQL содержит строку с данными полигона в формате
     * 'county','district','quarter','plot','polygon'
     * последний в формате функии ST_geomFromText.
     * $polygonGeom содержит полигон в формате вышеупомянутой
     * функции для его обновления при дублировании кадастрового
     * номера.
     * Функция возвращает массив с информацией:
     * [success] - bool была ли ошибка с записью полигона в базу
     * [exception] - string какая была ошибка, иначе null
     * [cadastral_number] - string кадастровый номер полигона
     */
    public static function add(Polygon $polygon): array
    {
        $success = true;
        $exceptionMessage = null;

        $sql = "INSERT INTO ?n (?p) VALUES (?p) ON DUPLICATE KEY UPDATE polygon=?p";

        $valuesSQL = $polygon->toValuesSQL();
        $polygonGeom = $polygon->toGeomSQL();
        $fields = implode(",",self::$fields);

        try {
            self::$connection->query(
                $sql, self::$table, $fields, $valuesSQL, $polygonGeom
            );

        } catch (\Exception $exception) {
            $success = false;
            $exceptionMessage = $exception->getMessage();
        }

        return [
            'success' => $success,
            'exception' => $exceptionMessage,
            'cadastral_number' => $polygon->getCadastralNumber()
        ];
    }

    /**
     * Функция добавления/обновления массива полигонов в БД.
     * Возвращает массив с информацией, который позволяет отследить
     * были ли проблемы при передаче данных в базу.
     * [success] bool - успешность всей передачи
     * [successes] array - успешность каждого полигона из передачи,
     * содержит возможные ошибки. Формат описан в функции add()
     */
    public static function addAll(array $polygons): array
    {
        $success = true;
        $successes = [];

        foreach ($polygons as $polygon) {
            $polygonSuccess = self::add($polygon);

            $success *= $polygonSuccess['success'];
            $successes[] = $polygonSuccess;
        }

        return [
            'success' => $success,
            'successes' => $successes
        ];
    }
}
