<?php

namespace App\Http\Responses;

use Illuminate\Http\Response;

class XmlResponse
{
    public static function make($data, $status = 200)
    {
        $xml = new \SimpleXMLElement('<response/>');
        self::arrayToXml(is_array($data) ? $data : $data->toArray(), $xml);

        return new Response($xml->asXML(), $status, ['Content-Type' => 'application/xml']);
    }

    private static function arrayToXml(array $data, \SimpleXMLElement &$xml)
    {
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $key = 'item'.$key; // Нейминг для числовых ключей
            }
            if (is_array($value)) {
                $subnode = $xml->addChild($key);
                self::arrayToXml($value, $subnode);
            } else {
                $xml->addChild($key, htmlspecialchars((string) $value));
            }
        }
    }
}
