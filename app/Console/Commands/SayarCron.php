<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Scrapes\Link;
use Goutte\Client;
use Carbon\Carbon;
use App\Lib\Scraper;
use App\Models\Scrapes\Content;
use App\Models\Articles\RawArticle;
use DOMDocument;
use Illuminate\Support\Facades\Log;

class SayarCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sayar:cron';

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
        $url = 'http://api.sayar.com.mm/4/1/all';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $data = curl_exec($ch);

        curl_close($ch);
        $json_d = json_decode($data, true);

        // return $json_d;

        foreach ($json_d as $sayar_data) {
            $sayar_link = "https://" . $sayar_data['link'];
            $checkExist = RawArticle::where('source_link', $sayar_link)->first();
            if (!isset($checkExist->id)) {
                $detail_count = str_word_count($sayar_data['detail']);
                $introtext_count = str_word_count($sayar_data['introtext']);

                $store_data = new RawArticle();
                $store_data->title = $sayar_data['title'];
                if ($detail_count > $introtext_count) {
                    $ict_data['detail'] = str_replace(array("\n", "\r", "\t"), '', $sayar_data['detail']);
                    $convert = html_entity_decode($sayar_data['detail']);
                    $store_data->content = tounicode($convert);
                } else {
                    $ict_data['introtext'] = str_replace(array("\n", "\r", "\t"), '', $sayar_data['introtext']);
                    $convert = html_entity_decode($sayar_data['introtext']);
                    $store_data->content = tounicode($convert);
                }
                $store_data->website_id = '1';
                $store_data->category_id = '1';
                $store_data->publishedDate =  date('Y-m-d H:i:s', strtotime($sayar_data['created']));
                $store_data->image = "https://" . $sayar_data['images']['lg'];
                $store_data->source_link = $sayar_link;
                $store_data->save();

                $content = new Content;
                $content->article_id = $store_data->id;
                $content->content_image = $store_data->image;
                $content->save();

                if ($detail_count > $introtext_count) {
                    $intro = $ict_data['introtext'];
                    $intro = strip_tags($intro);
                    $content_intro = Content::create([
                        "article_id" => $store_data->id,
                        "content_text" => tounicode($intro)
                    ]);
                }

                $current_id = $store_data->id;

                $store_data->content = preg_replace('#(<[p]*)(style=("|\')(.*?)("|\'))([a-z ]*>)#', '\\1\\6', $store_data->content);
                // $store_data->content = preg_replace('#(<[span ]*)(style=("|\')(.*?)("|\'))([a-z ]*>)#', '\\1\\6', $store_data->content);
                foreach (explode('</', $store_data->content) as $sayar_con) {
                    if (stripos($sayar_con, 'href') !== false) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($sayar_con);
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
                    } elseif (stripos($sayar_con, 'src') !== false) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($sayar_con);
                        libxml_clear_errors();
                        $images = $dom->getElementsByTagName('img');
                        foreach ($images as $image) {
                            $image = "https://www.sayar.com.mm/" . $image->getAttribute('src');
                            $content = new Content();
                            $content->article_id = $current_id;
                            $content->content_image = $image;
                            $content->save();
                        }
                    } else {
                        foreach (explode('span>', $sayar_con) as $con) {
                            $con = str_replace(array("\n", "\r", "\t"), '', $con);
                            $con = strip_tags(str_replace("&nbsp;", " ", $con));
                            $con = str_replace('p>', '', $con);
                            $con = str_replace('strong>', '', $con);
                            $con = str_replace('<br />', '', $con);
                            $content = new Content;
                            $content->article_id = $current_id;
                            $content->content_text = tounicode($con);
                            $content->save();

                            $del = Content::where('content_text', "")->delete();
                        }
                    }
                }
            }
        }
        Log::info("Sayar CronJob is Working");
    }
}
