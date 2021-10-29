<?php

namespace App\Console\Commands;

use App\Helpers\Helper;
use Illuminate\Console\Command;
use DOMDocument;
use App\Models\Scrapes\Content;
use App\Models\Scrapes\Link;
use Carbon\Carbon;
use Goutte\Client;
use App\Lib\Scraper;
use App\Models\Articles\RawArticle;
use Illuminate\Support\Facades\Log;

class Healthcare extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'healthcare:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date_format = date("Y-m-d");
        $context = stream_context_create(array('http' => array('header' => 'Connection: close\r\n')));
        $url  = "https://healthcare.com.mm/tompt/" . $date_format;
        $fileContents = file_get_contents($url, false, $context);
        $json_d = json_decode($fileContents, true);

        foreach ($json_d['items'] as $d) {
            $checkExist = RawArticle::where('source_link', $d['posturl'])->first();
            if (!isset($checkExist->id)) {
                $store_data = new RawArticle();
                $store_data->title = $d['title'];
                $store_data->content = $d['content'];
                $store_data->category_id = '8';
                $store_data->website_id = '35';
                $store_data->publishedDate = date('Y-m-d H:i:s', strtotime($d['date']));
                $store_data->image = $d['image'];
                $store_data->host = "healthcare.com.mm";
                $store_data->source_link = $d['posturl'];
                $store_data->save();

                $date_format = date("Y-m-d");
                $ch = curl_init();
                $url = 'https://healthcare.com.mm/tompt/' . $date_format;
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, 0);

                $data = curl_exec($ch);

                curl_close($ch);
                $json_d = json_decode($data, true);

                foreach ($json_d['items'] as $d) {
                    $checkExist = RawArticle::where('source_link', $d['posturl'])->first();
                    if (!isset($checkExist->id)) {
                        $d['content'] = str_replace(array("\n", "\r", "\t"), '', $d['content']);
                        $convert = html_entity_decode($d['content']);
                        $store_data = new RawArticle();
                        $store_data->title = tounicode($d['title']);
                        $store_data->content = $convert;
                        $store_data->category_id = '8';
                        $store_data->website_id = '35';
                        $store_data->publishedDate = date('Y-m-d H:i:s', strtotime($d['date']));
                        $store_data->image = $d['image'];
                        $store_data->source_link = $d['posturl'];
                        $store_data->host = "healthcare.com.mm";
                        $store_data->save();

                        foreach (explode('</', $store_data->content) as $hc_content) {
                            if (stripos($hc_content, 'src')) {
                                $on_content = str_replace('<p>', '', $hc_content);
                                $dom = new DOMDocument();
                                libxml_use_internal_errors(true);
                                $dom->loadHTML($on_content);
                                libxml_clear_errors();
                                $images = $dom->getElementsByTagName('img');
                                foreach ($images as $image) {
                                    $img = $image->getAttribute('src');
                                }
                                $content = new Content();
                                $content->article_id = $store_data->id;
                                $content->content_image = utf8_decode(urldecode($img));
                                $content->save();
                            } else {
                                $on_content = str_replace('p>', '', $hc_content);
                                $hc_content = str_replace('<<', '', $hc_content);
                                $hc_content = str_replace('<', '', $hc_content);
                                $content = new Content();
                                $content->article_id = $store_data->id;
                                $content->content_text = html_entity_decode($hc_content);
                                $content->save();

                                $del = Content::where('content_text', "")->delete();
                            }
                        }
                    }
                }
                // $noti_url = 'https://fcm.googleapis.com/fcm/send';
                // $noti_data = [
                //     "to" => "/topics/general",
                //     "data" => [
                //         "body" => date('h:i:s A', strtotime($store_data->publishedDate)) . "Dev Server",
                //         "title" => "Lifestyle Myanmar",
                //         "image" => $store_data->image,
                //         "sound" => "https://www.mboxdrive.com/goalsound.mp3",
                //     ]
                // ];
                // $noti_json_array = json_encode($noti_data);
                // $noti_headers = [
                //     'Authorization: key=AAAAp8NVqeM:APA91bGPWMiGoNRavsQTyJSeY-79eovG1CxbW8SOx4Qm9dXgtSXzfnsJC090HjJzIujGKLNLWeGTnc0jZM_mfDle0vtYYhYDT7L-nzWUQzwa6G711s5KnWZHRuIy6ISkeQBcJv4w2FG2',
                //     'Accept: application/json',
                //     'Content-Type: application/json',
                // ];
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
            }
        }

        Log::info("Healthcare CronJob is Working");
        $log = Helper::logText("Healthcare Scraped the data");
    }
}
