<?php
namespace Modules\User\Handlers;
use App\Core\BaseHandler;
use Symfony\Component\HttpFoundation\Request;

class UserHandler extends BaseHandler
{
    // private $logger;

    // public function __construct()
    // {

    //     // Inisialisasi logger
    //     $logManager = new LogManager();
    //     $this->logger = $logManager->getLogger();
    // }
    public function ping()
    {
        $this->logger->info('UserHandler: ping method called');

        $data = [
            "ping" => "pong"
        ];

        return $data;
    }
    public function profile()
    {
        $data = [
            "username" => "cacing69"
        ];

        return $data;
    }

    public function check($id)
    {
        // dd($this->request);
        // $request = Request::createFromGlobals();

        // $request->request->all();

        // dd($this->request);

        // var_dump($request->query->get('id'));
        // die();
        // Logika untuk mengambil pengguna berdasarkan ID
        return ['id' => $id];
    }

    public function submitForm(Request $request)
{
    // Mengambil data dari form
    $formData = $request->request->all();
    $name = $request->request->get('name');

    return [
        'form_data' => $formData,
        'name' => $name,
    ];
}
}
