<?php

namespace Modules\User\Http;

use App\Core\BaseHandler;
use Illuminate\Http\Request;
use Modules\User\UserTable;
// use Symfony\Component\HttpFoundation\Request;
// use Symfony\Component\HttpFoundation\Response;

class UserHandler extends BaseHandler
{
    // private $logger;

    // public function __construct(ServiceContainer $container)
    public function __construct()
    {
        parent::__construct();
        // Inisialisasi logger
        // $logManager = new LogManager();
        // $this->logger = $logManager->getLogger();
    }
    public function ping(Request $request)
    {
        // $this->logger->info('UserHandler: ping method called');

        $data = [
            "ping" => "pong"
        ];

        // return $data;
        return $data;
    }
    public function profile()
    {
        $data = [
            "username" => "cacing69"
        ];

        return $data;
    }

    public function check(Request $request, $id)
    {
        // Validasi bahwa id harus integer
        if (!filter_var($id, FILTER_VALIDATE_INT)) {
            // Validasi bahwa id harus integer
            if (!filter_var($id, FILTER_VALIDATE_INT)) {
                // Mengembalikan respons dengan kode 400 dan pesan error
                // return new Response(
                //     json_encode(['error' => 'Invalid ID, must be an integer']),
                //     Response::HTTP_BAD_REQUEST,
                //     ['Content-Type' => 'application/json']
                // );
            }
        }
        // dd($this->request);
        // $request = Request::createFromGlobals();

        // $request->request->all();

        // dd($this->request);

        // var_dump($request->query->get('id'));
        // die();
        // Logika untuk mengambil pengguna berdasarkan ID
        return ['id' => $id, "params" => $request->query->get('id')];
    }

    public function checkDb()
    {
        $user = UserTable::all();

        return $user;
    }
}
