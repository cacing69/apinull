<?php
namespace Modules\Dummy\Http;

use App\Http\BaseHandler;
use Illuminate\Http\Request;
use Illuminate\Database\Capsule\Manager as DB;

class DummyHandler extends BaseHandler
{
    public function index(Request $request)
    {
        $data = [
            "data" => null,
            "meta" => null,
            "error" => null
        ];
        return $data;
    }

    public function store(Request $request)
    {
        $data = [
            "data" => null,
            "meta" => null,
            "error" => null
        ];
        return $data;
    }
}
