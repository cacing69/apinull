<?php

namespace Modules\Test\Http;

use App\Http\BaseHandler;
use Illuminate\Http\Request;

class TestHandler extends BaseHandler
{
    public function test(Request $request)
    {
        $data = [
            "ping" => "pong",
            // "user" => $db
        ];

        return $data;
    }

    public function error(Request $request)
    {
        $data = [
            "ping" => "pong",
            // "user" => $db
        ];

        return response()->json($data, 400);
    }
}
