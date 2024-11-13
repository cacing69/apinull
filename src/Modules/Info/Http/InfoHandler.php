<?php
    namespace Modules\Info\Http;

    use App\Http\BaseHandler;
    use Illuminate\Http\Request;
    use Illuminate\Database\Capsule\Manager as DB;

    class InfoHandler extends BaseHandler
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
    }
