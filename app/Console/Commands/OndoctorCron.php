<?php

namespace App\Console\Commands;

use App\Helpers\Helper;
use App\Http\Controllers\Notify\WebhookController;
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
        $advertisement = [
            'Similac-Mum', 'Similac-Mum', 'solmux-ads', 'Solmux-ads',
            'Decolgen', 'decolgen', 'Milk-Thistle-Ads', 'milk-thistle-ads', 'Kremil',
            'kremil', 'Mixagrip', 'mixagrip', 'Biogesic', 'biogesic', 'Tiffy',
            'tiffy', 'Sara', 'sara', 'Enervon', 'enervon', 'Vicee', 'vicee', 'Ceelin',
            'ceelin', 'Mixaflu', 'mixaflu', 'Betax', 'betax', 'Musol', 'musol',
            'Konidine', 'konidine', 'Nutrovitar', 'nutrovitar', 'Nutrivita',
            'nutrivita', 'Ferovit', 'ferovit', 'Ferofort', 'ferofort', 'Obimin',
            'obimin', 'Mediflu', 'mediflu', 'Revicon', 'revicon', 'Vitahome',
            'vitahome', 'Livolin', 'livolin', 'Flemex', 'flemex', 'Antigas', 'antigas',
            'Ketorex', 'ketorex', 'Hiruscar', 'hiruscar', 'Bio-Oil', 'Hiruscar', 'Voltex',
            'Len-sen', 'lensen', 'Lensen', 'len-sen', 'Tothema', 'tothema', 'Burn', 'CLA'
        ];

        $link = Link::find(3);
        $scraper = new Scraper(new Client());

        $scraper->handle($link);

        $articles = RawArticle::where('website_id', '32')->get();
        foreach ($articles as $article) {
            $check_exist = Content::where('article_id', $article->id)->get();
            if ($check_exist->count() < 1) {
                $article->content = str_replace(array("\n", "\r", "\t", "<strong>", '</strong>'), '', $article->content);

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
                        $content->content_image = utf8_decode(urldecode($img));
                        foreach ($advertisement as $ads) {
                            if (strstr($content->content_image, $ads)) {
                                $content->content_image = "";
                            }
                        }
                        $content->save();
                    } else {
                        $on_content = str_replace('p>', '', $on_content);
                        $on_content = str_replace('strong>', '', $on_content);
                        $on_content = str_replace('/strong>', '', $on_content);
                        // foreach (explode('strong>', $on_content) as $con) {
                        foreach (explode('ul>', $on_content) as $con_ul) {
                            foreach (explode('li>', $con_ul) as $con_li) {
                                $con_li = str_replace('<br>', '', $con_li);
                                $con_li = str_replace('<<', '', $con_li);
                                $con_li = str_replace('<', '', $con_li);
                                $con_li = str_replace('a>', '', $con_li);
                                $con_li = str_replace('h4>', '', $con_li);
                                $con_li = str_replace('span>', '', $con_li);
                                $ocon_li = str_replace('strong>', '', $con_li);
                                $con_li = str_replace('/strong>', '', $con_li);
                                $con_li = str_replace('span style=\'font-weight: 400;\'>', '', $con_li);
                                $content = new Content();
                                $content->article_id = $article->id;
                                $content->content_text = $con_li;
                                $content->save();

                                $del = Content::where('content_text', "")->delete();
                            }
                        }
                        // }
                    }
                }
                $article_cat = RawArticle::find($article->id);
                // dd($article_cat);
                $article_tag = RawArticle::find($article_cat->id);
                $article_tag->tags()->sync((array)Helper::suggest_tags($article_tag->id));
                $article_tag->save();

                //check duplicate title
                if (empty(Helper::duplicate_with_title($article->id))) {
                    if (empty(Helper::duplicate_with_content($article->id))) {
                        if (empty(Helper::sensitive_keywords($article->id))) {
                            if (empty(Helper::checkBlacklist($article->id))) {
                                //auto send
                                $auto = new WebhookController();
                                $auto->SendMethod($article->id);
                                $log = Helper::logText("Ondoctor auto send the data");
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
