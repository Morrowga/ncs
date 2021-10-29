<?php

namespace App\Http\Controllers\API;

use App\Models\Scrapes\Content;
use App\Models\Settings\Category;
use Carbon\Carbon;
use DOMDocument;
use App\Models\Articles\RawArticle;
use App\Website;
use App\ArticleRecord;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\DB;
use App\Helpers\Helper;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexing()
    {
        $index =  request()->indexingArticles;
        if (!empty($index)) {
            foreach ($index as $index_data) {
                $raw_article = new RawArticle;
                $raw_article->source_link = $index_data['url'];
                $raw_article->image = $index_data['imageUrl'];
                $raw_article->title = $index_data['title'];
                $raw_article->host = "lotaya.mpt.com.mm";
                $raw_article->content = $index_data['content'];
                $raw_article->publishedDate = date('Y-m-d H:i:s', strtotime($index_data['publishedDate']));
                $raw_article->save();

                $get_data_array = $get_array = [];
                $img_src = $link_href = '';

                $arr_datum = explode('</p>', $raw_article->content); // ======================= remove </p>
                foreach ($arr_datum as $datum) {
                    if (!empty($datum)) { // =============================================== condition for removing &nbsp; *its not working*
                        $remove_p = str_replace('<p>', "", $datum); // ===================== remove <p>

                        if (stripos($remove_p, '<strong>') !== false) { // =========================== find strong and remove
                            $remove_p = str_replace('<strong>', '', $remove_p);
                            $remove_p = str_replace('</strong>', '', $remove_p);

                            array_push($get_data_array, $remove_p);
                        } elseif (stripos($remove_p, '<a') !== false) { // =========================== find a, take href and remove a
                            $dom = new DOMDocument;
                            $dom->loadHTML($remove_p);
                            $link_counts = $dom->getElementsByTagName('a');
                            foreach ($link_counts as $lc) {
                                $link_href = $lc->getAttribute('href');
                            }
                            $remove_p = stristr($remove_p, '>');
                            $remove_p = str_replace('>', '', $remove_p);
                            $remove_p = str_replace('</a', '', $remove_p);

                            array_push($get_data_array, $remove_p . '^' . $link_href);
                        } elseif (strpos($remove_p, 'src') !== false) { // ========================= find img, take src and remove img
                            $dom = new DOMDocument;
                            $dom->loadHTML($remove_p);
                            $img_counts = $dom->getElementsByTagName('img');
                            foreach ($img_counts as $ic) {
                                $img_src = $ic->getAttribute('src');
                                array_push($get_data_array, $img_src);
                            }
                        } else { // ======================================================== just add text
                            $plain_text = html_entity_decode($remove_p);
                            $plain_text = preg_replace("/\r|\n/", "", $plain_text);
                            $plain_text = preg_replace('/\s+/', '', $plain_text);
                            array_push($get_data_array, $plain_text);
                        }
                    }
                }

                // $count = 0;
                $adding = new Content;
                $adding->article_id = $raw_article->id;
                $adding->content_image = $index_data['imageUrl'];
                $adding->save();

                foreach ($get_data_array as $datum) {
                    $adding = new Content;
                    $adding->article_id = $raw_article->id;
                    if (stripos($datum, '^http') !== false) {
                        $adding->content_link = $datum;

                        $adding->save();
                    } elseif (stripos($datum, 'http') !== false) {
                        $adding->content_image = $datum;
                        $adding->save();
                    } else {
                        $adding->content_text = $datum;
                        $adding->save();
                        $space = "&nbsp;";
                        $space_entity = html_entity_decode($space);
                        $del = Content::where('content_text', '=', $space_entity)->delete();
                    }
                }
                $article_cat = RawArticle::find($raw_article->id);
                $article_cat->category_id =  Helper::indexing_category($article_cat->id);
                $article_cat->website_id = '3';
                $article_cat->save();

                $article_tag = RawArticle::find($article_cat->id);
                $article_tag->tags()->sync((array)Helper::indexing_tags($article_tag->id));
                $article_tag->save();
            }
        }
        $log = Helper::logText("Lotaya Indexing Article");


        return [
            "success" => true,
            "message" => "Successfully Uploads."
        ];
    }


    public function index($id = null)
    {
        $article = RawArticle::where('uuid', $id)->first();
        // return $article->content;
        $result = $content_array = $content_data_text = $content_data_image = $content_data_link = $tag_lists = [];
        $contents = Content::where('article_id', '=', $article->id)->get();

        if ($contents) {
            foreach ($contents as $content) {
                if ($content['content_text'] != null) {
                    $content_data_text = [
                        "key" => "content",
                        "value" => $content['content_text']
                    ];
                    array_push($content_array, $content_data_text);
                }
                if ($content['content_image'] != null) {
                    $hots = RawArticle::where('id', $content->article_id)->where('host', '=', 'yoyarlay.com')->first();
                    if ($hots) {
                        $content_data_image = [
                            "key" => "image",
                            "value" => "http://139.59.110.228/storage/" . $content['content_image']
                        ];
                    } else {
                        $content_data_image = [
                            "key" => "image",
                            "value" => $content['content_image']
                        ];
                    }
                    array_push($content_array, $content_data_image);
                }
                if ($content['content_link'] != null) {
                    $content_data_link = [
                        "key" => "link",
                        "value" => $content['content_link']
                    ];
                    array_push($content_array, $content_data_link);
                }
            }
        }

        if ($id) {
            $article = RawArticle::where('uuid', $id)->with('website', 'category')->first();
            if ($article) {
                foreach ($article->tags as $tag) {
                    array_push($tag_lists, $tag->nameMm);
                }
                $styled_data = [
                    'url' => $article->source_link,
                    'host' => $article->website->host,
                    'imageUrl' => $article->image,
                    'thumbnailUrl' => $article->image,
                    'title' => $article->title,
                    'category' => [
                        'id' => $article->category->id_name,
                        'name' => $article->category->name,
                        'nameMm' => $article->category->nameMm,
                    ],
                    'providerCategory' => $article->website->providerCategory,
                    'fontType' => 'unicode',
                    'score' => 0,
                    'uuid' => $article->uuid,
                    'publishedDate' => date('Y-m-d H:i:s', strtotime($article->publishedDate)),
                    'content' => $content_array,
                    'categoryName' => $article->category->name,
                    'categoryId' => $article->category->id_name,
                    'keywords' => $tag_lists
                ];
                $result['success'] = true;
                $result['data'] = $styled_data;
            } else {
                $result['success'] = false;
                $result['data'] = 'There is no such a data.';
            }
        } else {
            $result['success'] = false;
            $result['data'] = 'There is no such id.';
        }
        $log = Helper::logText("New Single Article");

        return $result;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return RawArticle::where('update_status', 1)->get()->count();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return RawArticle::where('uuid', $id)->first();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function get_all_articles()
    {
        $array_ids = request()->input('article_ids');
        $arr = $result = $styled_data = $content_array = $content_data_text = $content_data_image = $content_data_link = $tag_lists = [];

        if (!empty($array_ids)) {
            if (count($array_ids) > 1) {
                foreach ($array_ids as $get_id) {

                    $article = RawArticle::where('uuid', $get_id)->first();

                    if ($article) {
                        $contents = Content::where('article_id', '=', $article->id)->get();
                        foreach ($contents as $content) {
                            if ($content['content_text'] != null) {
                                $content_data_text = [
                                    "key" => "content",
                                    "value" => $content['content_text']
                                ];
                                array_push($content_array, $content_data_text);
                            }
                            if ($content['content_image'] != null) {
                                $hots = RawArticle::where('id', $content->article_id)->where('host', '=', 'yoyarlay.com')->first();
                                if ($hots) {
                                    $content_data_image = [
                                        "key" => "image",
                                        "value" => "http://139.59.110.228/storage/" . $content['content_image']
                                    ];
                                } else {
                                    $content_data_image = [
                                        "key" => "image",
                                        "value" => $content['content_image']
                                    ];
                                }
                                array_push($content_array, $content_data_image);
                            }
                            if ($content['content_link'] != null) {
                                $content_data_link = [
                                    "key" => "link",
                                    "value" => $content['content_link']
                                ];
                                array_push($content_array, $content_data_link);
                            }
                        }
                        foreach ($article->tags as $tag) {
                            array_push($tag_lists, $tag->nameMm);
                        }
                        $styled_data = [
                            'url' => $article->source_link,
                            'host' => $article->website->host,
                            'imageUrl' => $article->image,
                            'thumbnailUrl' => $article->image,
                            'title' => $article->title,
                            'category' => [
                                'id' => $article->category->id_name,
                                'name' => $article->category->name,
                                'nameMm' => $article->category->nameMm,
                            ],
                            'providerCategory' => $article->website->providerCategory,
                            'fontType' => 'unicode',
                            'score' => 0,
                            'uuid' => $article->uuid,
                            'publishedDate' => date('Y-m-d H:i:s', strtotime($article->publishedDate)),
                            'content' => $content_array,
                            'categoryName' => $article->category->name,
                            'categoryId' => $article->category->id_name,
                            'keywords' => $tag_lists
                        ];
                        array_push($arr, $styled_data);
                        $content_array = [];
                        $tag_lists = [];
                    }
                }
            } else {
                // $array_ids[0]
                $article = RawArticle::where('uuid', $array_ids[0])->first();
                if ($article) {
                    $contents = Content::where('article_id', '=', $article->id)->get();
                    foreach ($contents as $content) {
                        if ($content['content_text'] != null) {
                            $content_data_text = [
                                "key" => "content",
                                "value" => $content['content_text']
                            ];
                            array_push($content_array, $content_data_text);
                        }
                        if ($content['content_image'] != null) {
                            $hots = RawArticle::where('id', $content->article_id)->where('host', '=', 'yoyarlay.com')->first();
                            if ($hots) {
                                $content_data_image = [
                                    "key" => "image",
                                    "value" => "http://139.59.110.228/storage/" . $content['content_image']
                                ];
                            } else {
                                $content_data_image = [
                                    "key" => "image",
                                    "value" => $content['content_image']
                                ];
                            }
                            array_push($content_array, $content_data_image);
                        }
                        if ($content['content_link'] != null) {
                            $content_data_link = [
                                "key" => "link",
                                "value" => $content['content_link']
                            ];
                            array_push($content_array, $content_data_link);
                        }
                    }
                    foreach ($article->tags as $tag) {
                        array_push($tag_lists, $tag->nameMm);
                    }
                    $styled_data = [
                        'url' => $article->source_link,
                        'host' => $article->website->host,
                        'imageUrl' => $article->image,
                        'thumbnailUrl' => $article->image,
                        'title' => $article->title,
                        'category' => [
                            'id' => $article->category->id_name,
                            'name' => $article->category->name,
                            'nameMm' => $article->category->nameMm,
                        ],
                        'providerCategory' => $article->website->providerCategory,
                        'fontType' => 'unicode',
                        'score' => 0,
                        'uuid' => $article->uuid,
                        'publishedDate' => date('Y-m-d H:i:s', strtotime($article->publishedDate)),
                        'content' => $content_array,
                        'categoryName' => $article->category->name,
                        'categoryId' => $article->category->id_name,
                        'keywords' => $tag_lists
                    ];
                    array_push($arr, $styled_data);
                }
            }
            $result['success'] = true;
            $result['data'] = $arr;
        } else {
            $result['success'] = false;
            $result['data'] = null;
        }

        $log = Helper::logText("Multi New Articles");

        return $result;
    }

    public function get_related_artilces()
    {
        $id = request()->input('article_id');
        $arr = [];
        $result = [];
        if ($id) {
            $article = RawArticle::where('uuid', $id)->first();

            if ($article) {
                $related_articles = RawArticle::where([['category_id', $article->category_id], ['uuid', '!=', $id], ['sent_status', 1]])->limit(5)->orderBy('publishedDate', 'desc')->get();

                if ($related_articles) {
                    foreach ($related_articles as $related) {
                        $styled_data = [
                            'score' => 00000,
                            'article_id' => $related->uuid
                        ];
                        array_push($arr, $styled_data);
                    }
                    $result['success'] = true;
                    $result['related_articles'] = $arr;
                } else {
                    $result['success'] = false;
                    $result['error'] = 'no related article data';
                    $result['related_articles'] = [];
                }
            } else {
                $result['success'] = false;
                $result['error'] = 'no article data';
                $result['related_articles'] = [];
            }
        } else {
            $result['success'] = false;
            $result['error'] = 'no article id';
            $result['related_articles'] = [];
        }

        $log = Helper::logText("Related Articles");

        return $result;
    }

    public function get_engagement_articles()
    {
        $result = [];
        $arr = [];
        $engage_data = request()->input('engagements');
        if (!empty($engage_data)) {
            foreach ($engage_data as $e_data) {
                $article_id = RawArticle::where('uuid', $e_data['article_id'])->first()->id;
                $record = ArticleRecord::where('article_id', $article_id)->first();
                // if (!$record) {
                //     $record = new ArticleRecord;
                //     $record->article_id = $article_id;
                // }
                $record->view_count = $e_data['view'];
                $record->read_count = $e_data['read'];
                $record->like_count = $e_data['like'];
                $record->share_count = $e_data['share'];
                $record->save();
            }

            $result['success'] = true;
            $result['message'] = 'Eagagement data successfully updated.';

            $url = 'https://devcms.mpt.com.mm/api/news/update_order';
            $data = [
                "type" =>  "score_update"
            ];

            $json_array = json_encode($data);
            $curl = curl_init();
            $headers = [
                'Accept: application/json',
                'Content-Type: application/json',
            ];
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $json_array);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HEADER, 1);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($curl);
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            curl_close($curl);
        } else {
            $result['success'] = false;
            $result['error'] = 'Eagagement data cannot update.';
        }

        $log = Helper::logText("Engagements");

        return $result;
    }

    public function get_trend()
    {
        $time_one = date('Y-m-d H:i:s', strtotime(Carbon::now()->subHours(6)));
        $time_two = date('Y-m-d H:i:s', strtotime(Carbon::create($time_one)->subHours(18)));

        $window_one = $window_two = $arr_one = $arr_two = $result = $window_one_array = $window_two_array = [];
        $view_one_order_one = $view_one_order_two = $view_two_order_one = $view_two_order_two = [];
        $current_date = date('Y-m-d');

        // by category name
        $category_name = request()->input('category_id');
        if ($category_name) {
            $category_id = Category::where('name', $category_name)->first()->id;

            $articles_one = DB::table('raw_articles')
                ->join('article_records', 'raw_articles.id', '=', 'article_records.article_id')
                ->where([['raw_articles.sent_status', '1'], ['raw_articles.category_id', $category_id], ['raw_articles.publishedDate', '>=', $time_one]])
                ->orderByDesc('article_records.view_count')
                ->orderByDesc('raw_articles.publishedDate')
                ->select('raw_articles.id', 'raw_articles.uuid', 'article_records.view_count')
                ->get();

            $articles_two = DB::table('raw_articles')
                ->join('article_records', 'raw_articles.id', '=', 'article_records.article_id')
                ->where([['raw_articles.sent_status', '1'], ['raw_articles.category_id', $category_id], ['raw_articles.publishedDate', '<=', $time_one], ['raw_articles.publishedDate', '>=', $time_two]])
                ->orderByDesc('article_records.view_count')
                ->select('raw_articles.id', 'raw_articles.uuid', 'article_records.view_count')
                ->get();
        } else {
            $articles_one = DB::table('raw_articles')
                ->join('article_records', 'raw_articles.id', '=', 'article_records.article_id')
                ->where([['raw_articles.sent_status', '1'], ['raw_articles.publishedDate', '>=', $time_one]])
                ->orderByDesc('article_records.view_count')
                ->orderByDesc('raw_articles.publishedDate')
                ->select('raw_articles.id', 'raw_articles.uuid', 'article_records.view_count')
                ->get();

            $articles_two = DB::table('raw_articles')
                ->join('article_records', 'raw_articles.id', '=', 'article_records.article_id')
                ->where([['raw_articles.sent_status', '1'], ['raw_articles.publishedDate', '<=', $time_one], ['raw_articles.publishedDate', '>=', $time_two]])
                ->orderByDesc('article_records.view_count')
                ->orderByDesc('raw_articles.publishedDate')
                ->select('raw_articles.id', 'raw_articles.uuid', 'article_records.view_count')
                ->get();
        }
        // with or without category_id, two arrays will be execute
        if ($articles_one) {
            $one_count = 0;
            foreach ($articles_one as $one_data) {
                if ($one_data) {
                    array_push($arr_one, [
                        'article_id' => $one_data->uuid,
                        'order' => $one_count
                    ]);
                    $one_count++;
                }
            }
        }
        $one_count = 0;
        if ($articles_two) {
            $two_count = 0;
            foreach ($articles_two as $two_data) {
                if ($two_data) {
                    array_push($arr_two, [
                        'article_id' => $two_data->uuid,
                        'order' => $two_count
                    ]);
                    $two_count++;
                }
            }
        }
        $two_count = 0;

        $result['success'] = true;
        $result['trending_articles'] = ['window_one' => $arr_one, 'window_two' => $arr_two];

        $log = Helper::logText("Trending");

        return $result;
    }

    public function getMax()
    {
        $articles_one = DB::table('raw_articles')
            ->join('article_records', 'raw_articles.id', '=', 'article_records.article_id')
            ->where([
                ['raw_articles.update_status', '1'],
                ['raw_articles.sent_status', '1'],
                ['raw_articles.publishedDate', '>=', date('Y-m-d H:i:s', strtotime('2021-09-05 00:00:01'))],
                ['raw_articles.publishedDate', '<=', date('Y-m-d H:i:s', strtotime('2021-09-06 23:59:59'))],
            ])
            ->orderByDesc('article_records.view_count')
            ->orderByDesc('article_records.article_id')
            ->select('raw_articles.id', 'raw_articles.uuid', 'article_records.view_count')
            ->limit(20)
            ->get();

        print_r("<pre>");
        print_r($articles_one);
    }

    public function get_six()
    {
        $time_one = date('Y-m-d H:i:s', strtotime(Carbon::now()->subHours(6)));
        $time_two = date('Y-m-d H:i:s', strtotime(Carbon::create($time_one)->subHours(18)));
        $arr_one = $arr_two = [];

        $articles_one = DB::table('raw_articles')
            ->join('article_records', 'raw_articles.id', '=', 'article_records.article_id')
            ->where([['raw_articles.update_status', '1'], ['raw_articles.sent_status', '1'], ['raw_articles.publishedDate', '>=', $time_one]])
            ->orderByDesc('article_records.view_count')
            ->orderByDesc('raw_articles.publishedDate')
            // ->select('raw_articles.id', 'raw_articles.uuid', 'article_records.view_count')
            ->select('raw_articles.title', 'raw_articles.id', 'article_records.view_count')
            ->get();

        print_r('<pre>');
        print_r($articles_one);
    }

    public function get_18()
    {
        $time_one = date('Y-m-d H:i:s', strtotime(Carbon::now()->subHours(6)));
        $time_two = date('Y-m-d H:i:s', strtotime(Carbon::create($time_one)->subHours(18)));
        $arr_one = $arr_two = [];

        $articles_one = DB::table('raw_articles')
            ->join('article_records', 'raw_articles.id', '=', 'article_records.article_id')
            ->where([['raw_articles.update_status', '1'], ['raw_articles.sent_status', '1'], ['raw_articles.publishedDate', '<=', $time_one], ['raw_articles.publishedDate', '>=', $time_two]])
            ->orderByDesc('article_records.view_count')
            ->orderByDesc('raw_articles.publishedDate')
            // ->select('raw_articles.id', 'raw_articles.uuid', 'article_records.view_count')
            ->select('raw_articles.title', 'raw_articles.id', 'article_records.view_count')
            ->get();

        print_r('<pre>');
        print_r($articles_one);
    }

    public function getTransferData()
    {
        $client = new Client();
        $uri = 'https://ncsmm.com/transferData';
        $res = $client->get($uri);
        $data = json_decode($res->getBody()->getContents(), true);
        $status = 'false';

        foreach ($data as $d) {
            $raw = RawArticle::where('title', $d['title'])->first();
            if ($raw) {
                $hasData = Content::where('article_id', $raw->id)->get();

                if ($hasData->count() < 1) {
                    foreach ($d['content'] as $con) {
                        $content = new Content();
                        $content->article_id = $raw->id;
                        $content->content_image = $con['content_image'];
                        $content->content_link = $con['content_link'];
                        $content->content_text = $con['content_text'];
                        $content->save();
                    }
                    return $status = 'true';
                }
            }
        }
        $log = Helper::logText("TransferData");

        return $status;
    }
}
