<?php

namespace App\Console\Commands;

use App\Helpers\Helper;
use DOMDocument;
use App\Models\Scrapes\Content;
use App\Models\Scrapes\Link;
use Carbon\Carbon;
use Goutte\Client;
use App\Lib\Scraper;
use App\Models\Articles\RawArticle;
use Illuminate\Support\Facades\Log;
use function App\Helpers\logText;
use Illuminate\Console\Command;

class BalloneStarCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ballonestar:cron';

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
        $url = 'http://api.ballonestar.com/api/v2/news/lotaya';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // http: //localhost:8000/myanma_platform
        $data = curl_exec($ch);

        curl_close($ch);
        $json_d = json_decode($data, true);
        // return $json_d;
        foreach ($json_d as $ballone_data) {
            $checkExist = RawArticle::where('source_link', $ballone_data['source'])->first();
            if (!isset($checkExist->id)) {

                $store_data = new RawArticle();
                $store_data->title = tounicode($ballone_data['title']);

                $ballone_data['content'] = str_replace(array("\n", "\r", "\t"), '', $ballone_data['content']);
                $convert = html_entity_decode($ballone_data['content']);
                $store_data->content = $convert;
                $store_data->website_id = '52';
                $store_data->category_id = '11';
                $store_data->publishedDate =  date('Y-m-d H:i:s', strtotime($ballone_data['published_date']));
                $arr_image = $ballone_data['images'];
                foreach ($arr_image as $img) {
                    $store_data->image = $img;
                }
                $store_data->source_link = $ballone_data['source'];
                $store_data->host = "ballonestar.com";
                $store_data->save();

                $content = new Content;
                $content->article_id = $store_data->id;
                $content->content_image = $store_data->image;
                $content->save();
                $current_id = $store_data->id;

                $store_data->content = preg_replace('#(<[p]*)(style=("|\')(.*?)("|\'))([a-z ]*>)#', '\\1\\6', $store_data->content);

                foreach (explode('</', $store_data->content) as $edge_con) {
                    if (stripos($edge_con, 'href') !== false) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($edge_con);
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
                    } elseif (stripos($edge_con, 'src') !== false) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($edge_con);
                        libxml_clear_errors();
                        $images = $dom->getElementsByTagName('img');
                        foreach ($images as $image) {
                            $image = $image->getAttribute('src');
                            $content = new Content();
                            $content->article_id = $current_id;
                            $content->content_image = utf8_decode(urldecode($image));
                            $content->save();
                        }
                    } else {
                        foreach (explode('>', $edge_con) as $con) {
                            $con = strip_tags(str_replace("&nbsp;", " ", $con));
                            $con = str_replace('a', '', $con);
                            $con = str_replace('p', '', $con);
                            $con = str_replace('strong', '', $con);
                            $con = str_replace('span', '', $con);
                            $con = str_replace('<br />', '', $con);
                            $content = new Content;
                            $content->article_id = $current_id;
                            $content->content_text = $con;
                            $content->save();
                            $del = Content::where('content_text', "")->delete();
                        }
                    }
                }
                $article_cat = RawArticle::find($store_data->id);
                // dd($article_cat);
                $article_tag = RawArticle::find($article_cat->id);
                $article_tag->tags()->sync((array)Helper::suggest_tags($article_tag->id));
                $article_tag->save();
            }
        }
        Log::info("BalloneStar CronJob is Working");
        $log = Helper::logText("BalloneStar Scraped the data");
    }
}
