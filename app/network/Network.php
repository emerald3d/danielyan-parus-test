<?php

namespace Danielyan\Parus\App\Network;

use Exception;

class Network
{
    /**
     * @throws Exception 'Problems with API / connection to API / internet connection.'
     */
    public static function fetchData(string $json): string
    {
        $curlOptions = [
            CURLOPT_URL => $_ENV['API_URL'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            )
        ];

        try {
            $curl = curl_init();
            curl_setopt_array($curl, $curlOptions);
            $response = curl_exec($curl);
            curl_close($curl);
        } catch (Exception $exception) {
            throw new Exception('Problems with API / connection to API / internet connection: '.$exception);
        }

        return $response;
    }
}