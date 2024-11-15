<?php
namespace Modules\Home\Http;

use App\Http\BaseHandler;
use Illuminate\Http\Request;
use Illuminate\Database\Capsule\Manager as DB;

class HomeHandler extends BaseHandler
{
    public function index(Request $request)
    {
        $data = [
            "data" => null,
            "meta" => [
                "message" => "welcome to apinull"
            ],
            "error" => null
        ];

        return $data;
    }
}
