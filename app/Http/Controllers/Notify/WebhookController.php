<?php

namespace App\Http\Controllers\Notify;

use App\Models\Articles\RawArticle;
use App\ArticleRecord;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use function App\Helpers\logText;

class WebhookController extends Controller
{
    public function sendArticle(Request $request)
    {
        $articles = RawArticle::with('category', 'tags', 'website')->where('update_status', '=', '1')->where('sent_status', '=', '0')->limit(10)->get();
        $data_id = [];

        foreach ($articles as $uuid_data) {
            $add = $uuid_data['uuid'];
            array_push($data_id, $add);
        }

        $update = $request->input('update_news');
        $update_im = explode("//", $update);

        // $url = 'https://devcms.mpt.com.mm/api/news/new_articles';

        $url = 'https://article';


        $data = [
            'new' => $data_id,
            'update' => $update_im
        ];

        $json_array = json_encode($data);
        $curl = curl_init();
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
        ];
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json_array);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);


        $sent = json_decode($json_array, true);

        $loot = [];

        if ($sent['new']) {
            foreach ($sent['new'] as $sent_data) {

                $raw_articles = RawArticle::where('uuid', $sent_data)->with('category', 'website')->get();
                foreach ($raw_articles as $raw) {
                    array_push($loot, $raw);
                }
            }

            $loot_free = [];

            foreach ($loot as $loot_once) {
                $noti_url = 'https://fcm.googleapis.com/fcm/send';

                $noti_data = [
                    "to" => "/topics/general",
                    "data" => [
                        "body" => "New - Development Server",
                        "title" => $loot_once['title'],
                        "image" => $loot_once['image'],
                        "sound" => "https://www.mboxdrive.com/goalsound.mp3",
                        "notiId" => $loot_once['id'],
                        "date" => date('Y-m-d H:i:s', strtotime($loot_once['publishedDate'])),
                        "provider" => $loot_once['website']['host']
                    ],
                    "priority" => "high",
                    "android" => [
                        "priority" => "high"
                    ],
                    "apns" => [
                        "headers" => [
                            "apns-priority" => "5"
                        ]
                    ],
                    "webpush" => [
                        "headers" => [
                            "Urgency" => "high"
                        ]
                    ]
                ];

                $noti_json_array = json_encode($noti_data);
                $noti_headers = [
                    'Authorization: key=AAAAp8NVqeM:APA91bGPWMiGoNRavsQTyJSeY-79eovG1CxbW8SOx4Qm9dXgtSXzfnsJC090HjJzIujGKLNLWeGTnc0jZM_mfDle0vtYYhYDT7L-nzWUQzwa6G711s5KnWZHRuIy6ISkeQBcJv4w2FG2',
                    'Accept: application/json',
                    'Content-Type: application/json',
                ];
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $noti_url);
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $noti_json_array);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $noti_headers);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_HEADER, 1);
                curl_setopt($curl, CURLOPT_TIMEOUT, 30);

                $response = curl_exec($curl);
                $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

                curl_close($curl);



                array_push($loot_free, $noti_json_array);
            }
        }


        $loot_update_array = [];
        if ($sent['update']) {
            foreach ($sent['update'] as $sent_update) {

                $update_articles = RawArticle::where('uuid', $sent_update)->with('category', 'website')->get();
                foreach ($update_articles as $raw_update) {
                    array_push($loot_update_array, $raw_update);
                }
            }

            $loot_update = [];

            foreach ($loot_update_array as $loot_up) {
                $noti_url = 'https://fcm.googleapis.com/fcm/send';

                $noti_data = [
                    "to" => "/topics/general",
                    "data" => [
                        "body" => "Update - Development Server",
                        "title" => $loot_up['title'],
                        "image" => $loot_up['image'],
                        "sound" => "https://www.mboxdrive.com/goalsound.mp3",
                        "notiId" => $loot_up['id'],
                        "date" => date('Y-m-d H:i:s', strtotime($loot_up['publishedDate'])),
                        "provider" => $loot_up['website']['host']
                    ],
                    "priority" => "high",
                    "android" => [
                        "priority" => "high"
                    ],
                    "apns" => [
                        "headers" => [
                            "apns-priority" => "5"
                        ]
                    ],
                    "webpush" => [
                        "headers" => [
                            "Urgency" => "high"
                        ]
                    ]
                ];

                $noti_json_array = json_encode($noti_data);
                $noti_headers = [
                    'Authorization: key=AAAAp8NVqeM:APA91bGPWMiGoNRavsQTyJSeY-79eovG1CxbW8SOx4Qm9dXgtSXzfnsJC090HjJzIujGKLNLWeGTnc0jZM_mfDle0vtYYhYDT7L-nzWUQzwa6G711s5KnWZHRuIy6ISkeQBcJv4w2FG2',
                    'Accept: application/json',
                    'Content-Type: application/json',
                ];
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $noti_url);
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $noti_json_array);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $noti_headers);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_HEADER, 1);
                curl_setopt($curl, CURLOPT_TIMEOUT, 30);

                $response = curl_exec($curl);
                $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

                curl_close($curl);



                array_push($loot_update, $noti_json_array);
            }
        }

        // return $sent['new'];
        // dd($sent);
        // return count($sent['new'][0]);
        if (!empty($sent['new'])) {
            foreach ($sent['new'] as $se) {
                $raw_a = RawArticle::where('uuid', $se)->first();
                $raw_a->sent_status = 1;
                $raw_a->save();

                $has_record = ArticleRecord::where('article_id', $raw_a->id)->first();
                if (!$has_record) {
                    $article_record = new ArticleRecord;
                    $article_record->article_id = $raw_a->id;
                    $article_record->view_count = 0;
                    $article_record->read_count = 0;
                    $article_record->like_count = 0;
                    $article_record->share_count = 0;
                    $article_record->save();
                }
            }
        }

        $log = Helper::logText("Send Articles");

        return redirect()->route('modifyDataDisplay');
    }
}
