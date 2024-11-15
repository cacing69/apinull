<?php
namespace Modules\User\Http;

use App\Http\BaseHandler;
// use App\Kernel\DB;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Validator;
// use Illuminate\Validation\Factory as ValidatorFactory;
use Models\User;
use Symfony\Component\Yaml\Yaml;
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
        $db = DB::table("public.configs")->get();

        $data = [
            "ping" => "pong",
            "env" => [
                // getenv("APP_TEST"),
                $_ENV["APP_TEST"],
                // $_SERVER["APP_TEST"],
            ],
            "data" => $db
            // "user" => $db
        ];

        // dd($data);

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
        $user = User::all();

        return $user;
    }

    public function formValidate(Request $request)
    {
        // Inisialisasi ValidatorFactory
        // $validatorFactory = new ValidatorFactory();

        // Definisikan aturan validasi
        // $rules = [
        //     'username' => 'required|string|max:255',
        //     'email' => 'required|email|max:255|unique:users,email',
        //     'password' => 'required|string|min:8|confirmed',
        // ];

        // dd(__DIR__);

        $rulesYaml = Yaml::parseFile(__DIR__."/Validate/user.create.yaml") ?? [];

        $rules = convert_yaml_to_validate_laravel_rules($rulesYaml);
        // $rules['email'] = str_replace(':id', $userId, $rules['email']); // Ganti :id dengan userId saat ini

        // Lakukan validasi
        // $validator = Validator::make($request->all(), $rules);

        // Cek jika ada kesalahan
        $this->validate($request->all(), $rules);
        // if (
        //     {
        // //     return response()->json([
        // //         'errors' => $validator->errors(),
        // //     ], 400);
        // }

        // Jika validasi berhasil, lanjutkan dengan logika penyimpanan data
        // ...
    }
}
