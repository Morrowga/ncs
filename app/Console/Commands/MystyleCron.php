<?php

namespace App\Console\Commands;

use DOMDocument;
use Carbon\Carbon;
use Goutte\Client;
use App\Models\Articles\RawArticle;
use App\Models\Scrapes\Content;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MystyleCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mystyle:cron';

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
        $url = 'https://mystylemyanmar.com/feed/';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $data = curl_exec($ch);

        curl_close($ch);
        $e = json_encode($data);
        $d = json_decode($e, true);

        $rss = new DOMDocument();
        $rss->loadXML($d);
        $feed = array();
        foreach ($rss->getElementsByTagName('item') as $node) {

            $item = array(
                'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
                'guid' => $node->getElementsByTagName('guid')->item(0)->nodeValue,
                'pubDate' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
                'content' => $node->getElementsByTagName('encoded')->item(0)->nodeValue,
                'image' => $node->getElementsByTagName('content')->item(0)->getAttribute('url'),
                'website_id' => '1',
                'category_id' => '1'
            );
            array_push($feed, $item);
        }
        foreach ($feed as $f) {
            $checkExist = RawArticle::where('source_link', $f['guid'])->first();
            if (!isset($checkExist->id)) {
                $raw = new RawArticle();
                $raw->title = $f['title'];
                $raw->source_link = $f['guid'];
                $raw->publishedDate = date('Y-m-d H:i:s', strtotime($f['pubDate']));
                $raw->content = $f['content'];
                $raw->image = $f['image'];
                $raw->website_id = $f['website_id'];
                $raw->category_id = $f['category_id'];
                $raw->save();


                $current_id = $raw->id;


                foreach (explode('</', str_replace(array('<p>'), '</', $raw->content)) as $f_content) {
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
                            $content->content_image = $img;
                            $content->save();
                        }
                    } else {
                        $f_content = str_replace('/>', '', $f_content);
                        $f_content = str_replace('<', '', $f_content);
                        $f_content = str_replace('a>', '', $f_content);
                        $f_content = str_replace('p>', '', $f_content);
                        $f_content = str_replace('iframe>', '', $f_content);
                        $f_content = str_replace(array("\n", "\r", "\t"), '', $f_content);
                        $convert = html_entity_decode($f_content);
                        foreach (explode('strong>', $convert) as $con) {
                            foreach (explode('ul>', $con) as $con_ul) {
                                foreach (explode('li>', $con_ul) as $con_li) {
                                    foreach (explode('br', $con_li) as $br) {
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
        Log::info("Mystyle CronJob is Working");
    }
}
