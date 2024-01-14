<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use App\Models\ShortURL;

class ShortURLController extends Controller
{
    private function generateShortUrl()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $shortUrl = '';

        for ($i = 0; $i < 6; $i++) {
            $shortUrl .= $characters[rand(0, $charactersLength - 1)];
        }

        return $shortUrl;
    }

    private function isUrlSafe($url)
    {
        $apiKey = env('SAFE_BROWSING_KEY');
        $apiUrl = 'https://safebrowsing.googleapis.com/v4/threatMatches:find?key=' . $apiKey;

        $requestData = [
            'client' => [
                'clientId' => 'YourAppClientId',
                'clientVersion' => '1.0.0',
            ],
            'threatInfo' => [
                'threatTypes' => ['MALWARE', 'SOCIAL_ENGINEERING', 'UNWANTED_SOFTWARE'],
                'platformTypes' => ['ANY_PLATFORM'],
                'threatEntryTypes' => ['URL'],
                'threatEntries' => [
                    ['url' => $url],
                ],
            ],
        ];

        $response = Http::post($apiUrl, [
            'json' => $requestData,
        ]);

        $result = $response->json();
        return empty($result['matches']);
    }

    public function save(Request $request)
    {
        $requestData = $request->all();
        $url = $requestData['url'];
        $responseData = [];

        // Check request url is valid on Safe Browsing Lookup API
        if (!$this->isUrlSafe($url)) {
            $responseData = [
                'status' => 'failure',
                'message' => 'Your URL is not valid',
            ];

            return response()->json($responseData, 400);
        }

        // Check origin url is in db
        $temp = ShortURL::where('origin_url', $url)->first();
        if ($temp === null) {

            $shortUrl = $this->generateShortUrl();
            $url = ShortURL::firstOrCreate([
                'origin_url' => $url,
                'short_url' => $shortUrl
            ]);

            $responseData = [
                'status' => 'success',
                'message' => 'Your URL got shortened successfully',
                'data' => $url['short_url'],
            ];
        } else {
            $responseData = [
                'status' => 'success',
                'message' => 'Your URL already exist',
                'data' => $temp['short_url'],
            ];
        }

        return response()->json($responseData);
    }

    public function toURL(Request $request)
    {
        $requestData = $request->all();
        $shortUrl = $requestData['short-url'];
        $temp = ShortURL::where('short_url', $shortUrl)->first();
        return redirect($temp['origin_url']);
    }
}
