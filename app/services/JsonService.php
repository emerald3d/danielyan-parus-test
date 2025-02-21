<?php

namespace Danielyan\Parus\App\Services;

use Danielyan\Parus\App\Data\PolygonData;
use Danielyan\Parus\App\Enums\Category;
use Danielyan\Parus\App\Enums\Geometry;
use Danielyan\Parus\App\Models\Polygon;
use Exception;

abstract class JsonService
{
    private static function categoriesToJson(): string
    {
        $categories = array_column(Category::cases(), 'value');

        $json = "";
        foreach ($categories as $category) {
            $json .= '{"id": '.$category.'},';
        }
        $json = trim($json, ',');

        return '['.$json.']';
    }

    /**
     * Переводит строковый ввод в подходящий формат строкового массива для его преобразования в JSON.
     * При необходимости можно легко изменить или добавить поддержку нового формата вводимых значений.
     *
     * @throws Exception 'Invalid polygon coordinates format given!'
     */
    static function polygonAsStringToPolygonAsStringArray(string $input): array
    {
        return match (true) {
            str_contains($input, ', ') => explode(", ", $input),
            str_contains($input, ',') => explode(",", $input),
            default => throw new Exception('Invalid polygon coordinates format given!')
        };
    }

    /**
     * Получает на вход полигон в виде массива строк и преобразует его в полигон вида JSON.
     */
    private static function polygonAsStringArrayToPolygonAsJson(array $polygonAsStringArray): string
    {
        $json = [];
        foreach ($polygonAsStringArray as $coordinates) {
            $coordinatesString = explode(" ", $coordinates);
            $json[] = [floatval($coordinatesString[0]), floatval($coordinatesString[1])];
        }

        return '['.json_encode($json).']';
    }

    /**
     * На вход получает консольный ввод. Отдает строку c заполненным для отправки к API json-шаблоном.
     * POLYGON_PLACE - путь для вставки полученного из консольного ввода полигона,
     * EPSG_PLACE - путь для вставки типа EPSG нужного для успешной передачи полигона,
     * CATEGORIES_PLACE - путь для вставки выбранных категорий из класса Category.
     *
     * @throws Exception 'Invalid polygon coordinates format given!'
     */
    public static function post(string $input): string
    {
        $polygonAsArray = self::polygonAsStringToPolygonAsStringArray($input);

        $EPSG_PLACE = 191;
        $POLYGON_PLACE = 111;
        $CATEGORIES_PLACE = 19;

        $json = file_get_contents('app/data/json/request.json');

        $json = substr_replace(
            $json,
            $_ENV['EPSG'], $EPSG_PLACE,
            0);

        $json = substr_replace(
            $json,
            self::polygonAsStringArrayToPolygonAsJson($polygonAsArray),
            $POLYGON_PLACE,
            0
        );

        return substr_replace(
            $json,
            self::categoriesToJson(),
            $CATEGORIES_PLACE,
            0
        );
    }

    /**
     * Преобразует все полигоны из полученного от API json массива в массив объектов типа (Multi)Polygon
     * сохраняя их оригинальный id, кадастровый номер и тип Polygon/Multipolygon.
     * Проверяет не пустой ли ответ, есть ли в нем геометрические фигуры и являются ли они полигонами/мультиполигонами.
     * Функция возвращает массив с двумя элементами. В первом массиве ['verified'] успешно полученные полигоны, если
     * есть проблемы с частью полученных полигонов, их json данные будут сохранены в возвращаемом массиве ['trouble']
     * для возможного анализа.
     *
     * @throws Exception 'Empty data received. Problems with internet connection or connection to API, API updated and
     * this json format outdated.'
     * @throws Exception 'Problems with API, API updated and this json format outdated.'
     * @throws Exception 'Empty polygons received or problems with API, API updated and this json format outdated.'
     * @throws Exception 'Invalid geometry type! Founded: *'
     */
    public static function responseToPolygonArray(string $response): array
    {
        if ($response == null) {
            throw new Exception('Empty data received. Problems with internet connection or connection to API, '
                . 'API updated and this json format outdated.');
        }

        $responseArray = json_decode($response, true);

        if ($responseArray['type'] !== 'FeatureCollection') {
            throw new Exception('Problems with API, API updated and this json format outdated.');
        }

        $hasAnyPolygons = isset(current($responseArray['features'])['geometry']);
        if (!$hasAnyPolygons) {
            throw new Exception('Empty polygons received from entered into console data or problems with API, '
                . 'API updated and this json format outdated.');
        }

        $polygons = [];
        $polygonsWithProblems = [];
        foreach ($responseArray['features'] as $feature) {
            if (isset($feature['geometry']['type'])) {
                $featureType = $feature['geometry']['type'];
            } else {
                $polygonsWithProblems[] = $feature;
                continue;
            }

            $type = match ($featureType) {
                Geometry::Polygon->name => Geometry::Polygon,
                Geometry::MultiPolygon->name => Geometry::MultiPolygon,
                default => throw new Exception('Invalid geometry type! Founded: ' . $featureType)
            };

            if (
                isset($feature['properties']['externalKey']) &
                preg_match('/\d+:\d+:\d+:\d+/', $feature['properties']['externalKey']) &
                isset($feature['geometry']['coordinates']) &
                count($feature['geometry']['coordinates']) != 0 &
                !preg_match('/[a-z]/iu', json_encode($feature['geometry']['coordinates']))
            ) {
                $data = new PolygonData(
                    $feature['properties']['externalKey'],
                    $feature["geometry"]["coordinates"],
                    $type,
                );

                $polygons[] = new Polygon($data);
            } else {
                $polygonsWithProblems[] = $feature;
            }
        }

        return [
            'verified' => $polygons,
            'trouble' => $polygonsWithProblems
        ];
    }
}