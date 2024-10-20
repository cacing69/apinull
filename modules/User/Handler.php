<?php
namespace Modules\User;
use App\Core\BaseHandler;
use Symfony\Component\HttpFoundation\Request;

class Handler extends BaseHandler
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

    public function check(Request $request, $id)
    {
        // Validasi bahwa id harus integer
        if (!filter_var($id, FILTER_VALIDATE_INT)) {
            return [
                'error' => 'Invalid ID, must be an integer',
            ];
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
