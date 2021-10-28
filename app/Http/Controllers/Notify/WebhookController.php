<?php

namespace App\Http\Controllers\Notify;

use App\ArticleRecord;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Handler\WebhookHandler;
use App\Http\Controllers\Controller;
use Spatie\WebhookServer\WebhookCall;
use App\Helpers\Helper;
use App\Models\Articles\RawArticle;

class WebhookController extends Controller
{
    public function sendArticle($id)
    {
        $article = RawArticle::with('category', 'tags', 'website')->where('id', $id)->first();
        if ($article) {
            if ($article->sent_status == 1) {
                $new_uuid = null;
                $updated_uuid = $article->uuid;
            } else {
                $new_uuid = $article->uuid;
                $updated_uuid = null;
            }
        }

        // $url = 'https://devcms.mpt.com.mm/api/news/new_articles';
        $url = "https://webhook.site/f15595a5-cf21-4ede-bd71-672cd7a278ff";

        $data = [
            'new' => $new_uuid,
            'update' => $updated_uuid
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

        $noti_data = [
            "to" => "/topics/general",
            "data" => [
                "body" => "New - Development Server",
                "title" => $article['title'],
                "image" => $article['image'],
                "sound" => "https://www.mboxdrive.com/goalsound.mp3",
                "notiId" => $article['id'],
                "date" => date('Y-m-d H:i:s', strtotime($article['publishedDate'])),
                "provider" => $article['website']['host']
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
        $noti_url = 'https://fcm.googleapis.com/fcm/send';
        $noti_headers = [
            'Authorization: key=AAAAp8NVqeM:APA91bGPWMiGoNRavsQTyJSeY-79eovG1CxbW8SOx4Qm9dXgtSXzfnsJC090HjJzIujGKLNLWeGTnc0jZM_mfDle0vtYYhYDT7L-nzWUQzwa6G711s5KnWZHRuIy6ISkeQBcJv4w2FG2',
            'Accept: application/json',
            'Content-Type: application/json',
        ];
        // $curl = curl_init();
        // curl_setopt($curl, CURLOPT_URL, $noti_url);
        // curl_setopt($curl, CURLOPT_POST, 1);
        // curl_setopt($curl, CURLOPT_POSTFIELDS, $noti_json_array);
        // curl_setopt($curl, CURLOPT_HTTPHEADER, $noti_headers);
        // curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($curl, CURLOPT_HEADER, 1);
        // curl_setopt($curl, CURLOPT_TIMEOUT, 30);

        // $response = curl_exec($curl);
        // $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // curl_close($curl);

        if ($new_uuid) {
            $change_state_article = RawArticle::where('uuid', $new_uuid)->first();
            $change_state_article->sent_status = 1;
            $change_state_article->save();

            $has_record = ArticleRecord::where('article_id', $change_state_article->id)->first();
            if (!$has_record) {
                $article_record = new ArticleRecord;
                $article_record->article_id = $change_state_article->id;
                $article_record->view_count = 0;
                $article_record->read_count = 0;
                $article_record->like_count = 0;
                $article_record->share_count = 0;
                $article_record->save();
            }
        }

        $log = Helper::logText("Send Articles");

        return redirect()->route('sent_articles.index');
    }
}
