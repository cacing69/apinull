<?php

namespace Modules\Test\Http;

use App\Http\BaseHandler;

class TestHandler extends BaseHandler
{
    public function test()
    {
        $data = [
            "ping" => "test",
        ];

        return $data;
    }
}
