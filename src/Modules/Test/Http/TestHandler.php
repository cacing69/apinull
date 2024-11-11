<?php

namespace Modules\Test\Http;

use App\Http\BaseHandler;
use Illuminate\Http\Request;

class TestHandler extends BaseHandler
{
    public function test(Request $request)
    {

        $this->logger->warning('client_ip', [
            'client_ip' => $request->getClientIp(),
        ]);

        header('Location: https://iili.io/2xzzEcG.jpg');
        die();
    }
}
