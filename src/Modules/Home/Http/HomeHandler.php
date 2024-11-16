<?php
namespace Modules\Home\Http;

use App\Http\BaseHandler;
use App\Http\Route;
use Illuminate\Http\Request;
use Illuminate\Database\Capsule\Manager as DB;

class HomeHandler extends BaseHandler
{
    public function index(Request $request)
    {
        $data = [
            "data" => null,
            "meta" => [
                "message" => "Welcome to apinull"
            ],
            "error" => null
        ];

        return $data;
    }
    public function path(Request $request, $id)
    {
        $data = [
            "data" => [
                "id" => $id
            ],
            "meta" => [
                "message" => "Welcome to path apinull"
            ],
            "error" => null
        ];

        return $data;
    }

    public function dump(Request $request)
    {
        $data = [
            "data" => null,
            "meta" => [
                "message" => "Welcome to dump apinull"
            ],
            "error" => null
        ];

        return $data;
    }

    #[Route(path: '/check', methods: ['GET', 'POST'], middleware: ['auth'])]
    public function check(Request $request)
    {
        $data = [
            "data" => null,
            "meta" => [
                "message" => "apinull check"
            ],
            "error" => null
        ];

        return $data;
    }
}
