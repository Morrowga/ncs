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


class IctCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ict:cron';

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
        $url = 'http://api.ictdirectory.com.mm/4/1/all';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $data = curl_exec($ch);

        curl_close($ch);
        $json_d = json_decode($data, true);

        // return $json_d;
        foreach ($json_d as $ict_data) {
            $true_url = "https://" . $ict_data['link'];
            $checkExist = RawArticle::where('source_link', $true_url)->first();
            if (!isset($checkExist->id)) {
                $detail_count = str_word_count($ict_data['detail']);
                $introtext_count = str_word_count($ict_data['introtext']);

                $store_data = new RawArticle();
                $store_data->title = tounicode($ict_data['title']);
                if ($detail_count > $introtext_count) {
                    $ict_data['detail'] = str_replace(array("\n", "\r", "\t"), '', $ict_data['detail']);
                    $convert = html_entity_decode($ict_data['detail']);
                    $store_data->content = $convert;
                } else {
                    $ict_data['introtext'] = str_replace(array("\n", "\r", "\t"), '', $ict_data['introtext']);
                    $convert = html_entity_decode($ict_data['introtext']);
                    $store_data->content = $convert;
                }
                $store_data->website_id = '34';
                $store_data->category_id = '10';
                $store_data->publishedDate =  date('Y-m-d H:i:s', strtotime($ict_data['created']));
                $store_data->image = "https://" . $ict_data['images']['lg'];
                $store_data->source_link = $true_url;
                $store_data->host = "ictdirectory.com.mm";
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
                        "content_text" => $intro
                    ]);
                }

                $current_id = $store_data->id;

                $store_data->content = preg_replace('#(<[p]*)(style=("|\')(.*?)("|\'))([a-z ]*>)#', '\\1\\6', $store_data->content);
                // $store_data->content = preg_replace('#(<[span ]*)(style=("|\')(.*?)("|\'))([a-z ]*>)#', '\\1\\6', $store_data->content);
                foreach (explode('</', $store_data->content) as $ict_con) {
                    if (stripos($ict_con, 'href') !== false) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($ict_con);
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
                    } elseif (stripos($ict_con, 'src') !== false) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($ict_con);
                        libxml_clear_errors();
                        $images = $dom->getElementsByTagName('img');
                        foreach ($images as $image) {
                            if (strpos($ict_con, '/images')) {
                                $image = "https://www.myanmaritdirectory.com/" . $image->getAttribute('src');
                                $content = new Content();
                                $content->article_id = $current_id;
                                $content->content_image = utf8_decode(urldecode($image));
                                $content->save();
                            } else {
                                $image = $image->getAttribute('src');
                                $content = new Content();
                                $content->article_id = $current_id;
                                $content->content_image = $image;
                                $content->save();
                            }
                        }
                    } else {
                        foreach (explode('span>', $ict_con) as $con) {
                            $con = strip_tags(str_replace("&nbsp;", " ", $con), '<br>');
                            $con = str_replace('colgrou', '', $con);
                            $con = str_replace('a>', '', $con);
                            $con = str_replace('tr>', '', $con);
                            $con = str_replace('ul>', '', $con);
                            $con = str_replace('li>', '', $con);
                            $con = str_replace('td>', '', $con);
                            $con = str_replace('div>', '', $con);
                            $con = str_replace('tbody>', '', $con);
                            $con = str_replace('table>', '', $con);
                            $con = str_replace('p>', '', $con);
                            $con = str_replace('em>', '', $con);
                            $con = str_replace('strong>', '', $con);
                            $con = str_replace('iframe>', '', $con);
                            foreach (explode('<br />', $con) as $con_br) {
                                $content = new Content;
                                $content->article_id = $current_id;
                                $content->content_text = $con_br;
                                $content->save();
                                $del = Content::where('content_text', "")->delete();
                            }
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
        Log::info("ICT CronJob is Working");
        $log = Helper::logText("ICT Scraped the data");
    }
}
