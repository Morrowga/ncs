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


class BuilderguideCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'builderguide:cron';

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
        $url = 'http://api.buildersguide.com.mm/4/0/all';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $data = curl_exec($ch);

        curl_close($ch);
        $json_d = json_decode($data, true);

        // return $json_d;
        foreach ($json_d as $builder_guide_data) {
            // dd($builder_guide_data);
            $checkExist = RawArticle::where('source_link', $builder_guide_data['link'])->first();
            if (!isset($checkExist->id)) {
                $detail_count = str_word_count($builder_guide_data['detail']);
                // return $detail_count;

                $introtext_count = str_word_count($builder_guide_data['introtext']);
                // return $introtext_count;
                $store_data = new RawArticle();
                $store_data->title = tounicode($builder_guide_data['title']);
                if ($detail_count > $introtext_count) {
                    $builder_guide_data['detail'] = str_replace(array("\n", "\r", "\t"), '', $builder_guide_data['detail']);
                    $convert = html_entity_decode($builder_guide_data['detail']);
                    $store_data->content = $convert;
                } else {
                    $builder_guide_data['introtext'] = str_replace(array("\n", "\r", "\t"), '', $builder_guide_data['introtext']);
                    $convert = html_entity_decode($builder_guide_data['introtext']);
                    $store_data->content = $convert;
                }

                $store_data->website_id = '11';
                // $store_data->category_id = '1';
                $store_data->host = "buildersguide.com.mm";
                $store_data->publishedDate =  date('Y-m-d H:i:s', strtotime($builder_guide_data['created']));
                if (!empty($builder_guide_data['images'])) {
                    $store_data->image = 'https://' . $builder_guide_data['images']['lg'];
                }

                $store_data->source_link = $builder_guide_data['link'];
                // dd($store_data);
                $store_data->save();

                $content = new Content;
                $content->article_id = $store_data->id;
                if (!empty($store_data->image_thumbnail)) {
                    $content->content_image = $store_data->image_thumbnail;
                } else {
                    $content->content_image = $store_data->image;
                }
                $content->save();

                if ($detail_count > $introtext_count) {
                    $intro = $builder_guide_data['introtext'];
                    $intro = strip_tags($intro);
                    $content_intro = Content::create([
                        "article_id" => $store_data->id,
                        "content_text" => $intro
                    ]);
                }

                $current_id = $store_data->id;

                // $store_data->content = preg_replace('#(<[p]*)(style=("|\')(.*?)("|\'))([a-z ]*>)#', '\\1\\6', $store_data->content);
                // $store_data->content = preg_replace('#(<[span ]*)(style=("|\')(.*?)("|\'))([a-z ]*>)#', '\\1\\6', $store_data->content);
                foreach (explode('</', $store_data->content) as $builder_guide_content) {
                    if (stripos($builder_guide_content, 'href') !== false) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($builder_guide_content);
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
                    } elseif (stripos($builder_guide_content, 'src') !== false) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($builder_guide_content);
                        libxml_clear_errors();
                        $images = $dom->getElementsByTagName('img');
                        foreach ($images as $image) {
                            $image = "https://www.buildersguide.com.mm/" . $image->getAttribute('src');
                            $content = new Content();
                            $content->article_id = $current_id;
                            $content->content_image = $image;
                            $content->save();
                        }
                    } else {
                        foreach (explode('>', $builder_guide_content) as $con) {
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

                $article_cat->category_id =  Helper::suggest_category($article_cat->id);
                // $article_cat->website_id = Helper::suggest_website($article_cat->id);
                $article_cat->save();
            }
        }
        Log::info("BuilderGuide CronJob is Working");
        $log = Helper::logText("BuilderGuide Scraped the data");
    }
}
