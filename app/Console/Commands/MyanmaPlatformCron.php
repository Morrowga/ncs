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

class MyanmaPlatformCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'platform:cron';

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
        $remove_img = ['ea4a20200929162419364365.jpg', 'no_comment.png', '91b2120200929145648932074.jpg'];
        $ch = curl_init();
        $url = 'https://www.myanmaplatform.com/api/article/lists/typeid/6/pages/15.html';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $data = curl_exec($ch);

        curl_close($ch);
        $json_d = json_decode($data, true);
        // return $json_d['data'];
        foreach ($json_d['data'] as $myanma_platform) {
            $source = 'https://www.myanmaplatform.com' . $myanma_platform['url'];
            $checkExist = RawArticle::where('source_link', $source)->first();
            if (!isset($checkExist)) {

                $store_data = new RawArticle();
                $store_data->title = tounicode($myanma_platform['title']);
                $store_data->content = @$myanma_platform['description'];
                $store_data->website_id = '51';
                $store_data->category_id = '8';
                $store_data->publishedDate =  date('Y-m-d H:i:s', strtotime(@$myanma_platform['publish_time']));
                $store_data->image = null;
                $store_data->source_link = 'https://www.myanmaplatform.com' . $myanma_platform['url'];
                $store_data->host = "myanma_platform.com";
                $store_data->uuid = Helper::uuid();
                // return $store_data;
                $ch = curl_init();
                $url = $store_data->source_link;
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                $data = curl_exec($ch);
                curl_close($ch);

                $store_data->content = $data;
                $store_data->save();

                $content = new Content;
                $content->article_id = $store_data->id;
                $content->content_image = $store_data->image;
                $content->save();

                $current_id = $store_data->id;

                $store_data->content = str_replace(array("\n", "\r", "\t"), '', $store_data->content);
                $store_data->content = str_replace("<!DOCTYPE html", '', $store_data->content);
                $store_data->content = str_replace("html", '', $store_data->content);
                $store_data->content = preg_replace('#<head(.*?)>(.*?)</head>#is', '', $store_data->content);
                $store_data->content = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $store_data->content);
                $store_data->content = preg_replace('#<ul class="toolbar">(.*?)>(.*?)</ul>#is', '', $store_data->content);
                $store_data->content = preg_replace('#<div class="bui-left.logo-box">(.*?)</div>#', '', $store_data->content);
                $store_data->content = preg_replace('#<div class="bui-left.chinese-tag">(.*?)</div>#', '', $store_data->content);
                $store_data->content = preg_replace('#<ul class="user-nav-list.clearfix">(.*?)</ul>#', '', $store_data->content);
                $store_data->content = preg_replace('#<li class="nav-login">(.*?)</li>#', '', $store_data->content);
                $store_data->content = preg_replace('#<h1 class="article-title">(.*?)</h1>#', '', $store_data->content);
                $store_data->content = preg_replace('#<div class="article-sub">(.*?)</div>#', '', $store_data->content);
                $store_data->content = preg_replace('#<div class="topbar">(.*?)</div>#', '', $store_data->content);
                $store_data->content = preg_replace('#<div class="bui-right">(.*?)</div>#', '', $store_data->content);
                $store_data->content = preg_replace('#<div class="middlebar">(.*?)</div>#', '', $store_data->content);
                $store_data->content = preg_replace('#<div class="middlebar-inner.clearfix">(.*?)</div>#', '', $store_data->content);
                $store_data->content = preg_replace('#<li class="tool-item">(.*?)</li>#', '', $store_data->content);
                $store_data->content = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $store_data->content);
                $store_data->content = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $store_data->content);
                $store_data->content = preg_replace('#<div class="bui-left.index-left">(.*?)>(.*?)</div>#is', '', $store_data->content);
                $store_data->content = preg_replace('#<div class="bui-right.index-right">(.*?)</div>#', '', $store_data->content);
                $store_data->content = preg_replace('#<div id="comment">(.*?)</div>#', '', $store_data->content);
                $store_data->content = preg_replace('#<div class="bd-bottom-1">(.*?)</div>#', '', $store_data->content);
                $store_data->content = preg_replace('#<div class="c-header">(.*?)</div>#', '', $store_data->content);
                $store_data->content = preg_replace('#<div class="inputBox">(.*?)</div>#', '', $store_data->content);
                $store_data->content = preg_replace('#<div class="bui-box">(.*?)</div>#', '', $store_data->content);
                $store_data->content = preg_replace('#<div class="no-comment">(.*?)</div>#', '', $store_data->content);
                $store_data->content = preg_replace('#<div class="pane-module">(.*?)</div>#', '', $store_data->content);
                $store_data->content = preg_replace('#<div class="module-head">(.*?)</div>#', '', $store_data->content);
                $store_data->content = preg_replace('#<div class="bui-left">(.*?)</div>#', '', $store_data->content);
                $store_data->content = preg_replace('#<div class="share-box">(.*?)</div>#', '', $store_data->content);
                $store_data->content = preg_replace('#<div class="search-wrap">(.*?)</div>#', '', $store_data->content);
                $store_data->content = preg_replace('#<a class="share-count">(.*?)</a>#', '', $store_data->content);
                $store_data->content = preg_replace('#<div class="tt-autocomplete">(.*?)</div>#', '', $store_data->content);
                $store_data->content = preg_replace('#<div class="tt-input tt-input-group.tt-input-group--append">(.*?)</div>#', '', $store_data->content);
                $store_data->content = preg_replace('#<div class="tt-input-group__append">(.*?)</div>#', '', $store_data->content);
                $store_data->content = preg_replace('#<div class="c-submit">(.*?)</div>#', '', $store_data->content);
                $store_data->content = preg_replace('#<div class="title">(.*?)</div>#', '', $store_data->content);
                $store_data->content = preg_replace('#(<[p]*)(style=("|\')(.*?)("|\'))([a-z ]*>)#', '\\1\\6', $store_data->content);

                foreach (explode('</', str_replace(array('<p>'), '</', $store_data->content)) as $f_content) {
                    if (stripos($f_content, 'src')) {
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
                            foreach ($remove_img as $r_img) {
                                if (strstr($content->content_image, $r_img)) {
                                    $content->content_image = "";
                                }
                            }
                            $content->save();
                            if (empty($store_data->image)) {
                                $store_data->image = $content->content_image;
                                $store_data->save();
                                $content->content_image = "";
                                $content->save();
                            }
                        }
                    } else {
                        $f_content = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/si", '<$1$2>', $f_content);
                        $f_content = str_replace('<br/>', '', $f_content);
                        $f_content = str_replace('<link', '', $f_content);
                        $f_content = str_replace('br />', '', $f_content);
                        $f_content = str_replace('/>', '', $f_content);
                        $f_content = str_replace('head>', '', $f_content);
                        $f_content = str_replace('textarea>', '', $f_content);
                        $f_content = str_replace('<', '', $f_content);
                        $f_content = str_replace('a>', '', $f_content);
                        $f_content = str_replace('p>', '', $f_content);
                        $f_content = str_replace('br>', '', $f_content);
                        $f_content = str_replace('ul>', '', $f_content);
                        $f_content = str_replace('iframe>', '', $f_content);
                        $f_content = str_replace('div>', '', $f_content);
                        $f_content = str_replace('strong>', '', $f_content);
                        $f_content = str_replace('<strong', '', $f_content);
                        $f_content = str_replace('span>', '', $f_content);
                        $f_content = str_replace('li>', '', $f_content);
                        $f_content = str_replace('i>', '', $f_content);
                        $f_content = str_replace('em>', '', $f_content);
                        $f_content = str_replace('body>', '', $f_content);
                        $f_content = str_replace('style>', '', $f_content);
                        $f_content = str_replace('-->', '', $f_content);
                        $f_content = str_replace('Weibo', '', $f_content);
                        $f_content = str_replace('QQ space', '', $f_content);
                        $f_content = str_replace('QQ friends', '', $f_content);
                        $f_content = str_replace(' comment ', '', $f_content);
                        $f_content = str_replace('>>', '', $f_content);
                        $f_content = str_replace('!--', '', $f_content);
                        $f_content = str_replace('>', '', $f_content);
                        $f_content = str_replace('No reviews yet, come and grab the sofa！', '', $f_content);
                        $f_content = str_replace('!--', '', $f_content);
                        $f_content = str_replace(array("\n", "\r", "\t"), '', $f_content);
                        $f_content = str_replace('b>', '', $f_content);
                        $convert = html_entity_decode($f_content);
                        foreach (explode('strong>', $convert) as $con) {
                            foreach (explode('ul>', $con) as $con_ul) {
                                foreach (explode('li>', $con_ul) as $con_li) {
                                    foreach (explode('br>', $con_li) as $br) {
                                        $con_li = str_replace('<p><', '', $br);
                                        $con_li = str_replace('ul>', '', $br);
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
                $article_cat = RawArticle::find($store_data->id);
                // dd($article_cat);
                $article_tag = RawArticle::find($article_cat->id);
                $article_tag->tags()->sync((array)Helper::suggest_tags($article_tag->id));
                $article_tag->save();

                // $article_cat->category_id =  Helper::suggest_category($article_cat->id);
                // $article_cat->website_id = Helper::suggest_website($article_cat->id);
                // $article_cat->save();
            }
        }
        Log::info("Myanma Platform CronJob is Working");
        $log = Helper::logText("Myanma Platform Scraped the data");
    }
}
