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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use function App\Helpers\logText;

class YoyarlayEntCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yyl-ent:cron';

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
        $link = Link::find(5);
        $scraper = new Scraper(new Client());

        $scraper->handle($link);

        $articles = RawArticle::where('website_id', '38')->get();
        foreach ($articles as $article) {
            $check_exist = Content::where('article_id', $article->id)->get();
            if ($check_exist->count() < 1) {
                $content_feature = new Content;
                $file_format = file_get_contents($article->image);
                $filename = substr($article->image, strrpos($article->image, '/') + 1);
                $filename = str_replace('.jpg', '.webp', $filename);
                $locate = Storage::disk('public')->put($filename, $file_format);
                $content_feature->article_id = $article->id;
                $content_feature->content_image = $filename;
                $content_feature->save();

                $article->content = str_replace(array("\n", "\r", "\t"), '', $article->content);
                $article->content = trim(str_replace('"', "'", $article->content));
                foreach (explode('</', $article->content) as $yyl_content) {
                    if (stripos($yyl_content, 'src')) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($yyl_content);
                        libxml_clear_errors();
                        $images = $dom->getElementsByTagName('img');
                        foreach ($images as $image) {
                            $img = $image->getAttribute('src');
                            $contents = file_get_contents($img);
                            $name = substr($img, strrpos($img, '/') + 1);
                            $name = str_replace('.jpg', '.webp', $name);
                            $locate = Storage::disk('public')->put($name, $contents);
                            $content = new Content();
                            $content->article_id = $article->id;
                            $content->content_image = $name;
                            $content->save();
                        }
                    } else {
                        $yyl_content = str_replace("class='post-views-count'>", '', $yyl_content);
                        $yyl_content = str_replace('p>', '', $yyl_content);
                        $yyl_content = str_replace('figure>', '', $yyl_content);
                        $yyl_content = str_replace('<', '', $yyl_content);
                        $convert = html_entity_decode($yyl_content);
                        foreach (explode('</strong>', $convert) as $con) {
                            $con = str_replace('<strong>', '', $con);
                            foreach (explode('>', $con) as $con_h2) {
                                $con_h2 = str_replace('h2', '', $con_h2);
                                $con_h2 = preg_replace('/\sid=[\'|"][^\'"]+[\'|"]/', '', $con_h2);
                                $content = new Content();
                                $content->article_id = $article->id;
                                $content->content_text = $con_h2;
                                $content->save();

                                $del = Content::where('content_text', "")->delete();

                                $array = ['div', 'Post Views:', 'span', 'post-views-count', 'br', 'dashicons-chart-bar', 'entry-meta', 'post-views-label'];
                                $result = DB::table('contents')
                                    ->where(function ($query) use ($array) {
                                        foreach ($array as $key) {
                                            $query->orWhere('content_text', 'LIKE', "%$key%");
                                        }
                                    })
                                    ->delete();
                            }
                        }
                    }
                }
            }
        }
        Log::info("YoyarLay-Ent CronJob is Working");
        $log = Helper::logText("Yoyar Entertainment Scraped the data");
    }
}
