<?php

namespace Modules\Test\Http;

use App\Http\BaseHandler;
use Illuminate\Http\Request;

class TestHandler extends BaseHandler
{
    public function test(Request $request)
    {
        phpinfo();
        die();

        $data = [
            "ping" => "pong",
            // "user" => $db
        ];

        return $data;
    }
}
