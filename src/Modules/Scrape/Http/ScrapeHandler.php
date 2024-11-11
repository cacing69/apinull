<?php

namespace Modules\Scrape\Http;

use App\Http\BaseHandler;
use Illuminate\Http\Request;
use ImageKit\ImageKit;

class ScrapeHandler extends BaseHandler
{
    public function igPost(Request $request)
    {
        // dd($request->has("content"));

        preg_match_all('/\[(.*?)\]/', 'GET /articles?include=author&fields[articles]=title,body&fields[people]=name HTTP/1.1', $bracket);
        dd($bracket);

        if($request->has("content")) {
            return response()->json([
                "data" => []
            ], 400);
        }

        $publicKey = "public_zYNahpz5UmA+lO+icYgYIsz+2MM=";
        $privateKey = "private_R+sx/ogCDKyO+NkHQn3b/mhsf1s=";
        $imageKitUrl = "https://ik.imagekit.io/apinull";

        $imageKit = new ImageKit(
            $publicKey,
            $privateKey,
            $imageKitUrl
        );

        $url = "https://www.instagram.com/thriftcap/p/C94DSpBvR8w/?__a=1&__d=dis";  // The target URL

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $cookies =app_path("/data/cookies.txt");

        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);  // Path to the cookie file
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);   // Same path to save any new cookies

        $response = curl_exec($ch);
        curl_close($ch);

        $decodeResponse = json_decode($response, JSON_OBJECT_AS_ARRAY);

        $media = [];

        preg_match('/^https\:\/\/.*\/p\/(.*?)\/\?__a=1&__d=dis$/', $url, $extractPostId);

        foreach ($decodeResponse["items"][0]["carousel_media"] as $carouselMedia) {
            preg_match('/^https\:\/\/.*\/(.*?)\?stp=.*$/', $carouselMedia["image_versions2"]["candidates"][0]["url"], $extractFileName);

            $uploadFile = $imageKit->uploadFile([
                'file' => $carouselMedia["image_versions2"]["candidates"][0]["url"], # required, "binary","base64" or "file url"
                'fileName' => end($extractFileName),  # required
                "tags" => end($extractPostId),
                'isPublished' => true,
            ]);
            $media[] = $uploadFile?->result?->url;
        }

        dd($media);
    }
}
