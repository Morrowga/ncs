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
use function App\Helpers\logText;

class YatharCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yathar:cron';

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
        $ch = curl_init();
        $url = 'https://magazine.yathar.com/my/feed/';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $data = curl_exec($ch);

        curl_close($ch);
        $e = json_encode($data);
        $d = json_decode($e, true);
        $d = str_replace('&', '&amp;', $d);
        $rss = new DOMDocument();
        $rss->loadXML($d);

        $feed = array();

        foreach ($rss->getElementsByTagName('item') as $node) {
            // dd($node);
            // echo $node->getElementsByTagName('title')->item(0)->nodeValue;
            $item = array(
                'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
                'guid' => $node->getElementsByTagName('guid')->item(0)->nodeValue,
                'pubDate' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
                'content' => $node->getElementsByTagName('encoded')->item(0)->nodeValue,
                'image' => $node->getElementsByTagName('url')->item(0)->nodeValue,
                'website_id' => '1',
                'category_id' => '1'
            );

            array_push($feed, $item);
        }
        foreach ($feed as $f) {
            $checkExist = RawArticle::where('source_link', $f['guid'])->first();
            if (!isset($checkExist->id)) {
                $raw = new RawArticle();
                $raw->title = tounicode($f['title']);
                $raw->source_link = $f['guid'];
                $raw->publishedDate = date('Y-m-d H:i:s', strtotime($f['pubDate']));
                $raw->content = tounicode($f['content']);
                $raw->image = $f['image'];
                $raw->website_id = $f['website_id'];
                $raw->category_id = $f['category_id'];
                // dd($raw);
                $raw->save();

                // $noti_url = 'https://fcm.googleapis.com/fcm/send';
                // $noti_data = [
                //     "to" => "/topics/general",
                //     "data" => [
                //         "body" => "Development Server",
                //         "title" => $raw->title,
                //         "image" => $raw->image,
                //         "sound" => "https://www.mboxdrive.com/goalsound.mp3",
                //         "notiId" => $raw->id,
                //         "date" => date('Y-m-d H:i:s', strtotime($raw->publishedDate)),
                //         "provider" => "Yathar"
                //     ],
                //     "priority" => "high",
                //     "android" => [
                //         "priority" => "high"
                //     ],
                //     "apns" => [
                //         "headers" => [
                //             "apns-priority" => "5"
                //         ]
                //     ],
                //     "webpush" => [
                //         "headers" => [
                //             "Urgency" => "high"
                //         ]
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

                $current_id = $raw->id;


                foreach (explode('</', str_replace(array('<p>'), '</', $raw->content)) as $f_content) {
                    if (stripos($f_content, 'href') !== false) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($f_content);
                        libxml_clear_errors();
                        $links = $dom->getElementsByTagName('a');
                        foreach ($links as $link) {
                            $a_link = $link->getAttribute('href');
                            $a_text = utf8_decode($link->textContent);
                            $content = new Content();
                            $content->article_id = $current_id;
                            $content->content_link = $a_text . "^" . $a_link;
                            $content->save();
                        }
                    } elseif (stripos($f_content, 'src')) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($f_content);
                        libxml_clear_errors();
                        $images = $dom->getElementsByTagName('img');
                        foreach ($images as $image) {
                            $img = $image->getAttribute('src');
                            $content = new Content();
                            $content->article_id = $current_id;
                            $content->content_image = utf8_decode(urldecode($img));
                            $content->save();
                        }
                    } else {
                        $search_array = array(
                            '/>', '<', 'a>', 'p>', '&8211;', 'figure', 'strong'
                        );
                        $f_con = str_replace($search_array, '', $f_content);
                        $f_content = trim(html_entity_decode($f_con), " \t\n\r\0\x0B\xC2\xA0");

                        // $f_content = str_replace('>', '', $f_content);
                        $f_content = str_replace(array("\n", "\r", "\t"), '', $f_content);
                        $convert = html_entity_decode($f_content);
                        foreach (explode('strong>', $convert) as $con) {
                            foreach (explode('ul>', $con) as $con_ul) {
                                foreach (explode('li>', $con_ul) as $con_li) {
                                    foreach (explode('br>', $con_li) as $br) {
                                        $con_li = str_replace('<p><', '', $br);
                                        $content = new Content();
                                        $content->article_id = $current_id;
                                        $content->content_text = $br;
                                        $content->save();
                                        $del = Content::where('content_text', "")->delete();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        Log::info("Yathar CronJob is Working");
        $log = Helper::logText("Yathar Scraped the data");
    }
}
