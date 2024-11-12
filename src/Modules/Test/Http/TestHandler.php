<?php

namespace Modules\Test\Http;

use App\Http\BaseHandler;
use Illuminate\Http\Request;

class TestHandler extends BaseHandler
{
    public function test(Request $request)
    {
        phpinfo(INFO_ALL & ~INFO_ENVIRONMENT & ~INFO_CONFIGURATION & ~INFO_VARIABLES);
        die();

        $data = [
            "ping" => "pong",
            // "user" => $db
        ];

        return $data;
    }
}
