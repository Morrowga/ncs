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

class ModaCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moda:cron';

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

        //moda-celebrity
        $link = Link::find(9);
        $scraper = new Scraper(new Client());

        $scraper->handle($link);

        $articles = RawArticle::where('website_id', '47')->get();
        foreach ($articles as $article) {
            $check_exist = Content::where('article_id', $article->id)->get();
            if ($check_exist->count() < 1) {
                $content_feature = new Content;
                $content_feature->content_image = $article->image;
                $content_feature->save();

                $article->content = str_replace(array("\n", "\r", "\t"), '', $article->content);
                $article->content = trim(str_replace('"', "'", $article->content));
                foreach (explode('</', $article->content) as $moda_cele_content) {
                    if (stripos($moda_cele_content, 'src')) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($moda_cele_content);
                        libxml_clear_errors();
                        $images = $dom->getElementsByTagName('img');
                        foreach ($images as $image) {
                            $img = $image->getAttribute('src');
                            $content = new Content();
                            $content->article_id = $article->id;
                            $content->content_image = "https://moda.com.mm/" . $img;
                            $content->save();
                        }
                    } else {
                        $moda_cele_content = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/si", '<$1$2>', $moda_cele_content);
                        $moda_cele_content = str_replace('p>', '', $moda_cele_content);
                        $moda_cele_content = str_replace('strong', '', $moda_cele_content);
                        $moda_cele_content = str_replace('br', '', $moda_cele_content);
                        $moda_cele_content = str_replace('p>', '', $moda_cele_content);
                        $moda_cele_content = str_replace('span', '', $moda_cele_content);
                        $moda_cele_content = str_replace('<', '', $moda_cele_content);
                        $moda_cele_content = str_replace('iframe', '', $moda_cele_content);
                        $convert = html_entity_decode($moda_cele_content);
                        foreach (explode('>', $convert) as $con) {
                            $content = new Content();
                            $content->article_id = $article->id;
                            $content->content_text = $con;
                            $content->save();

                            $del = Content::where('content_text', "")->delete();
                        }
                    }
                }
            }
        }

        //moda-culture
        $link = Link::find(7);
        $scraper = new Scraper(new Client());

        $scraper->handle($link);

        $articles = RawArticle::where('website_id', '45')->get();
        foreach ($articles as $article) {
            $check_exist = Content::where('article_id', $article->id)->get();
            if ($check_exist->count() < 1) {
                $content_feature = new Content;
                $content_feature->content_image = $article->image;
                $content_feature->save();

                $article->content = str_replace(array("\n", "\r", "\t"), '', $article->content);
                $article->content = trim(str_replace('"', "'", $article->content));
                foreach (explode('</', $article->content) as $moda_culture_content) {
                    if (stripos($moda_culture_content, 'src')) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($moda_culture_content);
                        libxml_clear_errors();
                        $images = $dom->getElementsByTagName('img');
                        foreach ($images as $image) {
                            $img = $image->getAttribute('src');
                            $content = new Content();
                            $content->article_id = $article->id;
                            $content->content_image = "https://moda.com.mm/" . $img;
                            $content->save();
                        }
                    } else {
                        $moda_culture_content = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/si", '<$1$2>', $moda_culture_content);
                        $moda_culture_content = str_replace('p>', '', $moda_culture_content);
                        $moda_culture_content = str_replace('strong', '', $moda_culture_content);
                        $moda_culture_content = str_replace('br', '', $moda_culture_content);
                        $moda_culture_content = str_replace('p>', '', $moda_culture_content);
                        $moda_culture_content = str_replace('span', '', $moda_culture_content);
                        $moda_culture_content = str_replace('<', '', $moda_culture_content);
                        $moda_culture_content = str_replace('iframe', '', $moda_culture_content);
                        $convert = html_entity_decode($moda_culture_content);
                        foreach (explode('>', $convert) as $con) {
                            $content = new Content();
                            $content->article_id = $article->id;
                            $content->content_text = $con;
                            $content->save();

                            $del = Content::where('content_text', "")->delete();
                        }
                    }
                }
            }
        }

        //moda-beauty
        $link = Link::find(8);
        $scraper = new Scraper(new Client());

        $scraper->handle($link);

        $articles = RawArticle::where('website_id', '46')->get();
        foreach ($articles as $article) {
            $check_exist = Content::where('article_id', $article->id)->get();
            if ($check_exist->count() < 1) {
                $content_feature = new Content;
                $content_feature->content_image = $article->image;
                $content_feature->save();

                $article->content = str_replace(array("\n", "\r", "\t"), '', $article->content);
                $article->content = trim(str_replace('"', "'", $article->content));
                foreach (explode('</', $article->content) as $moda_beauty_content) {
                    if (stripos($moda_beauty_content, 'src')) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($moda_beauty_content);
                        libxml_clear_errors();
                        $images = $dom->getElementsByTagName('img');
                        foreach ($images as $image) {
                            $img = $image->getAttribute('src');
                            $content = new Content();
                            $content->article_id = $article->id;
                            $content->content_image = "https://moda.com.mm/" . $img;
                            $content->save();
                        }
                    } else {
                        $moda_beauty_content = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/si", '<$1$2>', $moda_beauty_content);
                        $moda_beauty_content = str_replace('p>', '', $moda_beauty_content);
                        $moda_beauty_content = str_replace('strong', '', $moda_beauty_content);
                        $moda_beauty_content = str_replace('br', '', $moda_beauty_content);
                        $moda_beauty_content = str_replace('p>', '', $moda_beauty_content);
                        $moda_beauty_content = str_replace('span', '', $moda_beauty_content);
                        $moda_beauty_content = str_replace('<', '', $moda_beauty_content);
                        $moda_beauty_content = str_replace('iframe', '', $moda_beauty_content);
                        $convert = html_entity_decode($moda_beauty_content);
                        foreach (explode('>', $convert) as $con) {
                            $content = new Content();
                            $content->article_id = $article->id;
                            $content->content_text = $con;
                            $content->save();

                            $del = Content::where('content_text', "")->delete();
                        }
                    }
                }
            }
        }

        //moda-fashion
        $link = Link::find(6);
        $scraper = new Scraper(new Client());

        $scraper->handle($link);

        $articles = RawArticle::where('website_id', '44')->get();
        foreach ($articles as $article) {
            $check_exist = Content::where('article_id', $article->id)->get();
            if ($check_exist->count() < 1) {
                $content_feature = new Content;
                $content_feature->content_image = $article->image;
                $content_feature->save();

                $article->content = str_replace(array("\n", "\r", "\t"), '', $article->content);
                $article->content = trim(str_replace('"', "'", $article->content));
                foreach (explode('</', $article->content) as $moda_beauty_content) {
                    if (stripos($moda_beauty_content, 'src')) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($moda_beauty_content);
                        libxml_clear_errors();
                        $images = $dom->getElementsByTagName('img');
                        foreach ($images as $image) {
                            $img = $image->getAttribute('src');
                            $content = new Content();
                            $content->article_id = $article->id;
                            $content->content_image = "https://moda.com.mm/" . $img;
                            $content->save();
                        }
                    } else {
                        $moda_beauty_content = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/si", '<$1$2>', $moda_beauty_content);
                        $moda_beauty_content = str_replace('p>', '', $moda_beauty_content);
                        $moda_beauty_content = str_replace('strong', '', $moda_beauty_content);
                        $moda_beauty_content = str_replace('br', '', $moda_beauty_content);
                        $moda_beauty_content = str_replace('p>', '', $moda_beauty_content);
                        $moda_beauty_content = str_replace('span', '', $moda_beauty_content);
                        $moda_beauty_content = str_replace('<', '', $moda_beauty_content);
                        $moda_beauty_content = str_replace('iframe', '', $moda_beauty_content);
                        $convert = html_entity_decode($moda_beauty_content);
                        foreach (explode('>', $convert) as $con) {
                            $content = new Content();
                            $content->article_id = $article->id;
                            $content->content_text = $con;
                            $content->save();

                            $del = Content::where('content_text', "")->delete();
                        }
                    }
                }
            }
        }
        Log::info("Moda CronJob is Working");
        $log = Helper::logText("Moda Scraped the data");
    }
}
