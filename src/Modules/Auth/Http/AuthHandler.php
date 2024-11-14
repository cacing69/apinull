<?php
namespace Modules\Auth\Http;

use App\Http\BaseHandler;
use Illuminate\Http\Request;
use Illuminate\Database\Capsule\Manager as DB;
use Models\User;
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

        dd($data);

        return $data;
    }
}
