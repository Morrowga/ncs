<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
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

class FarmerCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'farmer:cron';

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
        $url = 'https://www.thefarmermedia.com/api/v1/news?token=ExzVdU3mvF2hzfGZKsfz';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $data = curl_exec($ch);

        curl_close($ch);
        $json_d = json_decode($data, true);

        // return $json_d;
        foreach ($json_d as $j_d) {
            foreach ($j_d as $d) {
                foreach ($d as $farmer_data) {
                    // dd($farmer_data);
                    $checkExist = RawArticle::where('source_link', $farmer_data['url'])->first();

                    if (!isset($checkExist->id)) {

                        $store_data = new RawArticle();
                        $store_data->title = tounicode($farmer_data['title']);
                        $farmer_data['description'] = str_replace(array("\n", "\r", "\t"), '', $farmer_data['description']);
                        $convert = html_entity_decode(tounicode($farmer_data['description']));
                        $store_data->content = $convert;
                        $store_data->website_id = '49';
                        $store_data->category_id = '12';
                        $store_data->host = "thefarmermedia.com";
                        $store_data->publishedDate =  date('Y-m-d H:i:s', $farmer_data['post_date']);
                        if (!empty($farmer_data['main_image_1'])) {
                            $farmer_feature_image = $farmer_data['main_image_1'];
                            if (stripos($farmer_feature_image, 'src') !== false) {
                                $dom = new DOMDocument();
                                libxml_use_internal_errors(true);
                                $dom->loadHTML($farmer_feature_image);
                                libxml_clear_errors();
                                $images = $dom->getElementsByTagName('img');
                                foreach ($images as $image) {
                                    $image = $image->getAttribute('src');
                                    $store_data->image = $image;
                                }
                            }
                        }

                        $store_data->source_link = $farmer_data['url'];
                        // dd($store_data);
                        $store_data->save();

                        $current_id = $store_data->id;

                        // $store_data->content = preg_replace('#(<[p]*)(style=("|\')(.*?)("|\'))([a-z ]*>)#', '\\1\\6', $store_data->content);
                        // $store_data->content = preg_replace('#(<[span ]*)(style=("|\')(.*?)("|\'))([a-z ]*>)#', '\\1\\6', $store_data->content);
                        foreach (explode('</', $store_data->content) as $farmer_content) {
                            if (stripos($farmer_content, 'href') !== false) {
                                $dom = new DOMDocument();
                                libxml_use_internal_errors(true);
                                $dom->loadHTML($farmer_content);
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
                            } elseif (stripos($farmer_content, 'src') !== false) {
                                $dom = new DOMDocument();
                                libxml_use_internal_errors(true);
                                $dom->loadHTML($farmer_content);
                                libxml_clear_errors();
                                $images = $dom->getElementsByTagName('img');
                                foreach ($images as $image) {
                                    $image = "https://www.thefarmermedia.com/sites/thefarmermedia.com/" . $image->getAttribute('src');
                                    $content = new Content();
                                    $content->article_id = $current_id;
                                    $content->content_image = $image;
                                    $content->save();
                                }
                            } else {
                                foreach (explode('>', $farmer_content) as $con) {
                                    $con = strip_tags(str_replace("&nbsp;", " ", $con));
                                    $con = str_replace('a', '', $con);
                                    $con = str_replace('p', '', $con);
                                    $con = str_replace('sn', '', $con);
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
            }
        }
        Log::info("Farmer CronJob is Working");
        $log = Helper::logText("Farmer Scraped the data");
    }
}
