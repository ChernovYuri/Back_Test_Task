<?php

namespace App\Http\Controllers;

use App\Http\Responses\XmlResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

trait ResponseTrait
{
    protected function respond($data, $status = 200): JsonResponse|Response
    {
        $format = request()->get('format', 'json');

        if ($format === 'xml') {
            return XmlResponse::make($data, $status);
        }

        return response()->json($data, $status);
    }
}
