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
    public function register(Request $request)
    {
        $rules = yaml_validator("Auth", "register");

        $this->validate($request->all(), $rules);

        $emailCheck = User::where("email", $request->email)->count();

        if($emailCheck > 0){

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
        $rules = yaml_validator("Auth", "token");

        $this->validate($request->all(), $rules);

        $authRepo = new AuthRepository();
        $userRepo = new UserRepository();

        if($userRepo->countUserByEmail($request->email) === 0){
            return response_error("user not found");
        }

        $user = $authRepo->check($request->email, $request->password);



        $data = [
            "data" => array_merge($user->only(["email", "avatar"]), $authRepo->generateTokenUser($user)),
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
