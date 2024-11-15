<?php
namespace Modules\Auth\Http;

use App\Http\BaseHandler;
use Illuminate\Http\Request;
use Illuminate\Database\Capsule\Manager as DB;
use Models\User;
use Repository\AuthRepository;
use Repository\UserRepository;

class AuthHandler extends BaseHandler
{
    private $userRepository;
    private $authRepository;

    public function __construct(UserRepository $userRepository, AuthRepository $authRepository) {
        parent::__construct();

        $this->userRepository = $userRepository;
        $this->authRepository = $authRepository;

    }
    public function register(Request $request)
    {
        $rules = yaml_request_validator("Auth", "register");

        $this->validate($request->all(), $rules);

        $emailCheck = User::where("email", $request->email)->exists();

        if($emailCheck){

        }
        // $emailCheck = DB::table("public.users")->get();

        $repo = new UserRepository();

        $save = $repo->save($request->all());

        $data = [
            "data" => $save,
            "meta" => null,
            "error" => null
        ];

        return $data;
    }

    public function token(Request $request)
    {
        $rules = yaml_request_validator("Auth", "token");

        $this->validate($request->all(), $rules);

        // $authRepo = new AuthRepository();
        // $userRepo = new UserRepository();

        if(!$this->userRepository->isEmailExists($request->email)){
            return response_error("User not found");
        }

        $user = $this->authRepository->check($request->email, $request->password);



        $data = [
            "data" => array_merge($user->only(["email", "avatar"]), $this->authRepository->generateTokenUser($user)),
            "meta" => null,
            "error" => null
        ];

        return $data;
    }

    public function profile(Request $request)
    {
        return response_success(auth());
    }
}
