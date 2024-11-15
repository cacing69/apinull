<?php
namespace Repository;

use DateTime;
use DateTimeZone;
use Models\User;
use ParagonIE\Paseto\Builder;
use ParagonIE\Paseto\Keys\Version4\AsymmetricSecretKey;
use ParagonIE\Paseto\Protocol\Version4;
use ParagonIE\Paseto\Purpose;
class AuthRepository
{
    public function check($email, $password) : User
    {
        $user = User::where("email", $email)->first();

        if (!password_verify($password, $user->password)) {
            throw new \Exception("Wrong user credentials", 400);
        }

        return $user;
    }

    public function generateTokenUser(User $user)
    {
        $key = new AsymmetricSecretKey(base64_decode($_ENV["PASETO_SECRET_KEY"]));

        $iat = time();

        $expireTime = $iat + (3600 * 24 * 30) ; // streo 3600 to .env


        $timestamp = new DateTime();
        $timestamp->setTimezone(new DateTimeZone("Asia/Jakarta"));
        $timestamp->setTimestamp($expireTime);

        $payload = [
            "email" => $user->email,
            "iat" => $iat,
            "exp" => $timestamp->format(DateTime::ISO8601),
        ];

        $token = (new Builder())
            ->setKey($key)
            ->setExpiration($timestamp)
            ->setVersion(new Version4())
            ->setPurpose(Purpose::public())
            ->setClaims($payload)
            ->toString();

        return [
            "accessToken" => $token,
            "expiredAt" => $timestamp->format(DateTime::ISO8601)
        ];
    }
}
