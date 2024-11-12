<?php
namespace Modules\Scrape\Http;

use App\Http\BaseHandler;
use Illuminate\Http\Request;
use ImageKit\ImageKit;
use Instagram\Api;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class ScrapeHandler extends BaseHandler
{
    public function igProfile(Request $request)
    {
        // $proxies = [
        //     "http://proxy1:port",
        //     "http://proxy2:port",
        //     "http://proxy3:port",
        // ];
        // $randomProxy = $proxies[array_rand($proxies)];
        // curl_setopt($ch, CURLOPT_PROXY, $randomProxy);

        // URL of the Instagram profile to scrape
        $url = "https://www.instagram.com/instagram/";  // Replace with any public Instagram profile

        // Set up cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $cookies =app_path("/data/cookies.txt");

        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);  // Path to the cookie file
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);   // Same path to save any new cookies


        // Set headers to appear as a real browser request
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
        ]);

        $response = curl_exec($ch);
        curl_close($ch);



        // Load HTML and parse the JSON data
        if ($response) {
            // Use DOMDocument to find the JSON data in the script tag
            $dom = new \DOMDocument();
            @$dom->loadHTML($response);
            $scripts = $dom->getElementsByTagName('script');

            // dd($scripts->length);
            if (preg_match('/window\._sharedData = (.*);<\/script>/', $response, $matches)) {
                $json = json_decode($matches[1], true);
                print_r($json); // Debug untuk melihat data
            } else {
                echo "Data tidak ditemukan : 1 <br>";
            }

            if (preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/', $response, $matches)) {
                $jsonData = $matches[1];
                $data = json_decode($jsonData, true);
                if ($data) {
                    print_r($data); // Melihat data JSON yang tersedia
                } else {
                    echo "Gagal mengonversi JSON ld+json";
                }
            } else {
                echo "Data tidak ditemukan pada halaman : 2 <br>";
            }

            foreach ($scripts as $script) {
                // Contoh menggunakan preg_match untuk mendapatkan JSON
                if (strpos($script->nodeValue, 'window._sharedData') !== false) {
                    // Extract JSON data
                    $jsonData = trim(str_replace('window._sharedData = ', '', $script->nodeValue), ';');
                    $data = json_decode($jsonData, true);
                    dump($data);

                    // Display some basic profile information
                    echo "Profile Username: " . $data['entry_data']['ProfilePage'][0]['graphql']['user']['username'] . "\n";
                    echo "Full Name: " . $data['entry_data']['ProfilePage'][0]['graphql']['user']['full_name'] . "\n";
                    echo "Bio: " . $data['entry_data']['ProfilePage'][0]['graphql']['user']['biography'] . "\n";
                    echo "Followers: " . $data['entry_data']['ProfilePage'][0]['graphql']['user']['edge_followed_by']['count'] . "\n";
                    echo "Following: " . $data['entry_data']['ProfilePage'][0]['graphql']['user']['edge_follow']['count'] . "\n";
                    break;
                }
            }
        } else {
            echo "Failed to retrieve data.";
        }
        die();
    }

    public function igPost(Request $request)
    {
        // dd($request->has("content"));
        // preg_match_all('/\[(.*?)\]/', 'GET /articles?include=author&fields[articles]=title,body&fields[people]=name HTTP/1.1', $bracket);
        // dd($bracket);

        // if(!$request->has("content")) {
        //     return response()->json([
        //         "data" => [],
        //         "meta" => [],
        //         "errors" => []
        //     ], 400);
        // }

        $publicKey = "public_zYNahpz5UmA+lO+icYgYIsz+2MM=";
        $privateKey = "private_R+sx/ogCDKyO+NkHQn3b/mhsf1s=";
        $imageKitUrl = "https://ik.imagekit.io/apinull";

        $imageKit = new ImageKit(
            $publicKey,
            $privateKey,
            $imageKitUrl
        );

        // instagram.com\/(?:[A-Za-z0-9_.]+\/)?(p|reels|reel|stories)\/([A-Za-z0-9-_]+) // patern get id

        $url = "https://www.instagram.com/thriftcap/p/DCQfqRpB1-1/?__a=1&__d=dis";  // The target URL

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

        $cookies =app_path("/data/cookies.txt");

        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);  // Path to the cookie file
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);   // Same path to save any new cookies

        // Set headers to appear as a real browser request
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36 Edg/130.0.0.0",
            "X-IG-App-ID: 936619743392459",
            "Sec-Fetch-Site: same-origin",
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        // handling not found

        // if(true) {
        //     return [
        //         "data" => 1
        //     ];
        // }

        if(preg_match('/Page Not Found/', $response)) {
            return response()->json([
                "data" => null,
                "meta" => null,
                "error" => [
                    "message" => "post not found",
                    "stacks" => []
                ]
            ], 400);
        }

        $decodeResponse = json_decode($response, JSON_OBJECT_AS_ARRAY);

        $media = [];

        preg_match('/^https\:\/\/.*\/p\/(.*?)\/\?__a=1&__d=dis$/', $url, $extractPostId);

        foreach ($decodeResponse["items"][0]["carousel_media"] as $k => $carouselMedia) {
            preg_match('/^https\:\/\/.*\/(.*?)\?stp=.*$/', $carouselMedia["image_versions2"]["candidates"][0]["url"], $extractFileName);

            $uploadFile = $imageKit->uploadFile([
                'file'          => $carouselMedia["image_versions2"]["candidates"][0]["url"], # required, "binary","base64" or "file url"
                'fileName'      => end($extractFileName),  # required
                "tags"          => end($extractPostId),
                'isPublished'   => true,
                "overwriteFile" => true,
                "overwriteTags" => true,
                "folder"        => "/".end($extractPostId),
            ]);
            $media[] = [
                "index"     => $k + 1,
                "fileId"    => $uploadFile?->result?->fileId,
                "fileUrl"   => $uploadFile?->result?->url,
                "name"      => $uploadFile?->result?->name,
            ];
        }

        return response()->json([
            "data" => $media,
            "meta" => [],
            "errors" => []
        ]);

        // dd($media);
    }

    public function igFeed()
    {
        $cachePool = new FilesystemAdapter('Instagram', 0, app_path("/cache"));

        $api = new Api($cachePool);

        $api->login('paksalooy', '23Cacing09#@^'); // mandatory

        $profile = $api->getProfile('thriftcap');

        dd($profile);
        die();
    }
}
