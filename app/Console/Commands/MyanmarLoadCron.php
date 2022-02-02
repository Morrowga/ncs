<?php

namespace App\Console\Commands;

use App\Helpers\Helper;
use App\Lib\Scraper;
use App\Models\Articles\RawArticle;
use App\Models\Scrapes\Content;
use App\Models\Scrapes\Link;
use DOMDocument;
use Goutte\Client;
use Illuminate\Console\Command;

class MyanmarLoadCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'myanmarload:cron';

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
        $link = Link::find(10);
        $scraper = new Scraper(new Client());

        $scraper->handle($link);

        $articles = RawArticle::where('website_id', '53')->get();
        foreach ($articles as $article) {
            $check_exist = Content::where('article_id', $article->id)->get();
            if ($check_exist->count() < 1) {
                // $content_feature = new Content;
                // $content_feature->content_image = $article->image;
                // $content_feature->save();
                $article->image = null;

                $article->content = str_replace(array("\n", "\r", "\t"), '', $article->content);
                $article->content = trim(str_replace('"', "'", $article->content));
                foreach (explode('</', $article->content) as $mm_load_content) {

                    if (stripos($mm_load_content, 'href') !== false) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($mm_load_content);
                        libxml_clear_errors();
                        $links = $dom->getElementsByTagName('a');
                        foreach ($links as $link) {
                            $a_link = $link->getAttribute('href');
                            $a_text = utf8_decode($link->textContent);
                            $content = new Content();
                            $content->article_id = $article->id;
                            $content->content_link = $a_text . "^" . $a_link;
                            $content->save();
                        }
                    } elseif (stripos($mm_load_content, 'src')) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($mm_load_content);
                        libxml_clear_errors();
                        $images = $dom->getElementsByTagName('img');
                        foreach ($images as $image) {
                            $img = $image->getAttribute('src');
                            $content = new Content();
                            $content->article_id = $article->id;
                            $content->content_image = $img;
                            if (strstr($content->content_image, "data:image")) {
                                $content->content_image = "";
                            }
                            $content->save();
                            if (empty($article->image)) {
                                if (strstr($content->content_image, "http")) {
                                    $article->image = $content->content_image;
                                    $article->save();
                                    $content->content_image = "";
                                    $content->save();
                                }
                            }
                        }
                    } else {
                        $mm_load_content = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/si", '<$1$2>', $mm_load_content);
                        $mm_load_content = str_replace('p>', '', $mm_load_content);
                        $mm_load_content = str_replace('noscript>', '', $mm_load_content);
                        $mm_load_content = str_replace('i>', '', $mm_load_content);
                        $mm_load_content = str_replace('figcaption>', '', $mm_load_content);
                        $mm_load_content = str_replace('figure>', '', $mm_load_content);
                        $mm_load_content = str_replace('div>', '', $mm_load_content);
                        $mm_load_content = str_replace('b>', '', $mm_load_content);
                        $mm_load_content = str_replace('a>', '', $mm_load_content);
                        $mm_load_content = str_replace('strong', '', $mm_load_content);
                        $mm_load_content = str_replace('br', '', $mm_load_content);
                        $mm_load_content = str_replace('p>', '', $mm_load_content);
                        $mm_load_content = str_replace('span', '', $mm_load_content);
                        $mm_load_content = str_replace('<', '', $mm_load_content);
                        $mm_load_content = str_replace('iframe', '', $mm_load_content);

                        $convert = html_entity_decode($mm_load_content);
                        foreach (explode('>', $convert) as $con) {
                            $content = new Content();
                            $content->article_id = $article->id;
                            $content->content_text = $con;
                            $content->save();

                            $del = Content::where('content_text', "")->delete();
                        }
                    }
                }
                $article_cat = RawArticle::find($article->id);
                // dd($article_cat);
                $article_tag = RawArticle::find($article_cat->id);
                $article_tag->tags()->sync((array)Helper::suggest_tags($article_tag->id));
                $article_tag->save();
            }
        }
    }
}
