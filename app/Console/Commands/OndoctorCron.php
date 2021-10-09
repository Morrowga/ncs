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

class OndoctorCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ondoctor:cron';

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

        $link = Link::find(3);
        $scraper = new Scraper(new Client());

        $scraper->handle($link);

        $articles = RawArticle::where('website_id', '1')->get();
        foreach ($articles as $article) {
            $check_exist = Content::where('article_id', $article->id)->get();
            if ($check_exist->count() < 1) {
                $article->content = str_replace(array("\n", "\r", "\t"), '', $article->content);
                $article->content = trim(str_replace('"', "'", $article->content));
                foreach (explode('</', $article->content) as $on_content) {
                    if (stripos($on_content, 'src')) {
                        $on_content = str_replace('<p>', '', $on_content);
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($on_content);
                        libxml_clear_errors();
                        $images = $dom->getElementsByTagName('img');
                        foreach ($images as $image) {
                            $img = $image->getAttribute('src');
                        }
                        $content = new Content();
                        $content->article_id = $article->id;
                        $content->content_image = utf8_decode(urldecode($img));;
                        $content->save();
                    } else {
                        $on_content = str_replace('p>', '', $on_content);
                        foreach (explode('strong>', $on_content) as $con) {
                            foreach (explode('ul>', $con) as $con_ul) {
                                foreach (explode('li>', $con_ul) as $con_li) {
                                    $con_li = str_replace('<br>', '', $con_li);
                                    $con_li = str_replace('<<', '', $con_li);
                                    $con_li = str_replace('<', '', $con_li);
                                    $con_li = str_replace('a>', '', $con_li);
                                    $con_li = str_replace('h4>', '', $con_li);
                                    $content = new Content();
                                    $content->article_id = $article->id;
                                    $content->content_text = $con_li;
                                    $content->save();

                                    $del = Content::where('content_text', "")->delete();
                                }
                            }
                        }
                    }
                }
            }
        }
        Log::info("Ondoctor CronJob is Working");
        $log = Helper::logText("Ondoctor Scraped the data");
    }
}
