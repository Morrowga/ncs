<?php

namespace App\Http\Controllers\Web_Scraping;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Settings\Category;
use App\Models\Scrapes\ItemSchema;
use App\Lib\Scraper;
use App\Models\Scrapes\Link;
use App\Models\Scrapes\Website;
use Illuminate\Http\Request;
use Goutte\Client;
use App\Models\Scrapes\Content;
use Auth;
use Carbon\Carbon;
use App\Models\Articles\RawArticle;
use DOMDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PHPUnit\TextUI\Help;

class LinksController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $search_website = request()->input('search_website');

        if ($search_website) {
            $search_website_query = ['website_id', $search_website];
        } else {
            $search_website_query = ['website_id', '!=', NULL];
        }
        $links = Link::with('category', 'website')
            ->where([
                $search_website_query,
            ])->orderBy('id', 'DESC')->paginate(10);

        $itemSchemas = ItemSchema::all();

        $default = [
            'links' => $links,

            'search_website' => $search_website,
        ];

        return view('web_scraping.links.index', $default)->withItemSchemas($itemSchemas);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::all();
        $websites = Website::all();

        return view('web_scraping.links.create')->withCategories($categories)->withWebsites($websites);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'url' => 'required',
            'main_filter_selector' => 'required',
            'website_id' => 'required',
            'category_id' => 'required'
        ]);

        $link = new Link;

        $link->url = $request->input('url');

        $link->main_filter_selector = $request->input('main_filter_selector');

        $link->website_id = $request->input('website_id');

        $link->category_id = $request->input('category_id');

        $link->save();

        return redirect()->route('links.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $categories = Category::all();
        $websites = Website::all();

        return view('web_scraping.links.edit')->withLink(Link::find($id))->withCategories($categories)->withWebsites($websites);
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
        $this->validate($request, [
            'url' => 'required',
            'main_filter_selector' => 'required',
            'website_id' => 'required',
            'category_id' => 'required'
        ]);

        $link = Link::find($id);

        $link->url = $request->input('url');

        $link->main_filter_selector = $request->input('main_filter_selector');

        $link->website_id = $request->input('website_id');

        $link->category_id = $request->input('category_id');

        $link->save();

        return redirect()->route('links.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $link = Link::findOrFail($id);
        $link->delete();
        return redirect()->route('links.index', $link->id);
    }


    /**
     * @param Request $request
     */
    public function setItemSchema(Request $request)
    {
        if ($request->schema_id && $request->link_id) {
            $link = Link::find($request->link_id);
            $link->item_schema_id = $request->schema_id;
            $link->save();

            return response()->json(['status' => true]);
        }
        return response()->json(['status' => false]);
    }


    /**
     * scrape specific link
     *
     * @param Request $request
     */
    public function scrape(Request $request)
    {
        if (!$request->link_id)
            return;

        $link = Link::find($request->link_id);

        if (empty($link->main_filter_selector) && (empty($link->item_schema_id) || $link->item_schema_id == 0)) {
            return;
        }

        $scraper = new Scraper(new Client());

        $scraper->handle($link);

        if ($scraper->status == 1) {
            return response()->json(['status' => 1, 'msg' => 'Scraping done']);
        } else {
            return response()->json(['status' => 2, 'msg' => $scraper->status]);
        }
    }


    public function getCont()
    {
        $ch = curl_init();
        $url = 'https://lifestylemyanmar.com/category/lotaya/?feed=Lifestyle_Myanmar_RSS_Feed_for_Lotaya';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $data = curl_exec($ch);

        curl_close($ch);
        $e = json_encode($data);
        $d = json_decode($e, true);
        $d = str_replace(array("\n", "\r", "\t"), '', $d);

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
                'providerCategory' => $node->getElementsByTagName('category')->item(0)->nodeValue,
                'category_id' => '1'
            );
            array_push($feed, $item);
        }

        //  return $feed;

        foreach ($feed as $f) {
            $checkExist = RawArticle::where('source_link', $f['guid'])->first();
            if (!isset($checkExist->id)) {
                $raw = new RawArticle();
                $raw->title = tounicode($f['title']);
                $raw->source_link = $f['guid'];
                $raw->publishedDate = date('Y-m-d H:i:s', strtotime($f['pubDate']));
                $raw->content = tounicode($f['content']);
                $raw->image = $f['image'];
                $raw->website_id = $f['website_id'];
                // $raw->category_id = $f['category_id'];
                $raw->host = "lifestylemyanmar.com";
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
                            $content->content_image = utf8_decode(urldecode($img));
                            $content->save();
                        }
                    } else {
                        $convert = html_entity_decode($f_content);
                        $convert = str_replace('*', '', $convert);
                        $convert = str_replace('<', '', $convert);
                        $convert = str_replace('<br>', '', $convert);
                        $convert = str_replace('figure>', '', $convert);
                        $convert = str_replace('p>', '', $convert);
                        foreach (explode('h1>', $convert) as $con) {
                            foreach (explode('strong>', $con) as $con_strong) {
                                foreach (explode('em>', $con_strong) as $con_em) {
                                    foreach (explode('ul>', $con_em) as $con_ul) {
                                        foreach (explode('li>', $con_ul) as $con_li) {
                                            $content = new Content();
                                            $content->article_id = $current_id;
                                            $content->content_text = tounicode($con_li);
                                            $content->save();

                                            $del = Content::where('content_text', "")->delete();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $article_cat = RawArticle::find($raw->id);
                // dd($article_cat);
                $article_tag = RawArticle::find($article_cat->id);
                $article_tag->tags()->sync((array)Helper::suggest_tags($article_tag->id));
                $article_tag->save();

                $article_cat->category_id =  Helper::suggest_category($article_cat->id);
                // $article_cat->website_id = Helper::suggest_website($article_cat->id);
                $article_cat->save();
            }
        }
    }


    public function getConMS()
    {
        $ch = curl_init();
        $url = 'https://mystylemyanmar.com/feed/';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $data = curl_exec($ch);

        $invalid_characters = '/[^\x9\xa\x20-\xD7FF\xE000-\xFFFD]/';
        $data = preg_replace($invalid_characters, '', $data);

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
                'providerCategory' => $node->getElementsByTagName('category')->item(0)->nodeValue,
                'website_id' => '24',
                'category_id' => '1'
            );
            array_push($feed, $item);
        }
        foreach ($feed as $f) {
            $checkExist = RawArticle::where('source_link', $f['guid'])->first();
            if (!isset($checkExist->id)) {
                $raw = new RawArticle();
                $raw->title = tounicode($f['title']);
                $raw->source_link = $f['guid'];
                $raw->publishedDate = date('Y-m-d H:i:s', strtotime($f['pubDate']));
                $raw->content = $f['content'];
                $raw->image = $f['image'];
                $raw->website_id = $f['website_id'];
                // $raw->category_id = $f['category_id'];
                $raw->host = "mystylemyanmar.com";
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
                            $content->content_image = utf8_decode(urldecode($img));
                            $content->save();
                        }
                    } else {
                        $f_content = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/si", '<$1$2>', $f_content);
                        $f_content = str_replace('<br>', '', $f_content);
                        $f_content = str_replace('<br/>', '', $f_content);
                        $f_content = str_replace('br />', '', $f_content);
                        $f_content = str_replace('/>', '', $f_content);
                        $f_content = str_replace('<', '', $f_content);
                        $f_content = str_replace('a>', '', $f_content);
                        $f_content = str_replace('p>', '', $f_content);
                        $f_content = str_replace('iframe>', '', $f_content);
                        $f_content = str_replace('div>', '', $f_content);
                        $f_content = str_replace(array("\n", "\r", "\t"), '', $f_content);
                        $f_content = str_replace('b>', '', $f_content);
                        $convert = html_entity_decode($f_content);
                        foreach (explode('strong>', $convert) as $con) {
                            foreach (explode('ul>', $con) as $con_ul) {
                                foreach (explode('li>', $con_ul) as $con_li) {
                                    foreach (explode('br>', $con_li) as $br) {
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
                $article_cat = RawArticle::find($raw->id);
                // dd($article_cat);
                $article_tag = RawArticle::find($article_cat->id);
                $article_tag->tags()->sync((array)Helper::suggest_tags($article_tag->id));
                $article_tag->save();

                $article_cat->category_id =  Helper::suggest_category($article_cat->id);
                // $article_cat->website_id = Helper::suggest_website($article_cat->id);
                $article_cat->save();
            }
        }
    }

    public function healthCare()
    {
        $date_format = date("Y-m-d");
        $ch = curl_init();
        $url = 'https://healthcare.com.mm/tompt/' . $date_format;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $data = curl_exec($ch);

        curl_close($ch);
        $json_d = json_decode($data, true);

        foreach ($json_d['items'] as $d) {
            $checkExist = RawArticle::where('source_link', $d['posturl'])->first();
            if (!isset($checkExist->id)) {
                $d['content'] = str_replace(array("\n", "\r", "\t"), '', $d['content']);
                $convert = html_entity_decode($d['content']);
                $store_data = new RawArticle();
                $store_data->title = tounicode($d['title']);
                $store_data->content = $convert;
                $store_data->category_id = '8';
                $store_data->website_id = '35';
                $store_data->publishedDate = date('Y-m-d H:i:s', strtotime($d['date']));
                $store_data->image = $d['image'];
                $store_data->source_link = $d['posturl'];
                $store_data->host = "healthcare.com.mm";
                $store_data->save();

                foreach (explode('</', $store_data->content) as $hc_content) {
                    if (stripos($hc_content, 'src')) {
                        $on_content = str_replace('<p>', '', $hc_content);
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($on_content);
                        libxml_clear_errors();
                        $images = $dom->getElementsByTagName('img');
                        foreach ($images as $image) {
                            $img = $image->getAttribute('src');
                        }
                        $content = new Content();
                        $content->article_id = $store_data->id;
                        $content->content_image = utf8_decode(urldecode($img));
                        $content->save();
                    } else {
                        $on_content = str_replace('p>', '', $hc_content);
                        $hc_content = str_replace('<<', '', $hc_content);
                        $hc_content = str_replace('<', '', $hc_content);
                        $content = new Content();
                        $content->article_id = $store_data->id;
                        $content->content_text = html_entity_decode($hc_content);
                        $content->save();

                        $del = Content::where('content_text', "")->delete();
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
    }



    public function ondoctor()
    {
        $advertisement = ['Similac-Mum', 'Similac-Mum', 'solmux-ads', 'Solmux-ads', 'Decolgen', 'decolgen', 'Milk-Thistle-Ads', 'milk-thistle-ads', 'Kremil', 'kremil', 'Mixagrip', 'mixagrip', 'Biogesic', 'biogesic', 'Tiffy', 'tiffy', 'Sara', 'sara', 'Enervon', 'enervon', 'Vicee', 'vicee', 'Ceelin', 'ceelin', 'Mixaflu', 'mixaflu', 'Betax', 'betax', 'Musol', 'musol', 'Konidine', 'konidine', 'Nutrovitar', 'nutrovitar', 'Nutrivita', 'nutrivita', 'Ferovit', 'ferovit', 'Ferofort', 'ferofort', 'Obimin', 'obimin', 'Mediflu', 'mediflu', 'Revicon', 'revicon', 'Vitahome', 'vitahome', 'Livolin', 'livolin'];

        $link = Link::find(3);
        $scraper = new Scraper(new Client());

        $scraper->handle($link);

        $articles = RawArticle::where('website_id', '32')->get();
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
                        $content->content_image = utf8_decode(urldecode($img));
                        foreach ($advertisement as $ads) {
                            if (strstr($content->content_image, $ads)) {
                                $content->content_image = "";
                            }
                        }
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
                $article_cat = RawArticle::find($article->id);
                // dd($article_cat);
                $article_tag = RawArticle::find($article_cat->id);
                $article_tag->tags()->sync((array)Helper::suggest_tags($article_tag->id));
                $article_tag->save();
            }
        }
    }

    public function ict()
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
    }


    public function sayar()
    {
        $ch = curl_init();
        $url = 'http://api.sayar.com.mm/4/1/all';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $data = curl_exec($ch);

        curl_close($ch);
        $json_d = json_decode($data, true);

        foreach ($json_d as $sayar_data) {
            $sayar_link = "https://" . $sayar_data['link'];
            $checkExist = RawArticle::where('source_link', $sayar_link)->first();
            if (!isset($checkExist->id)) {
                $detail_count = str_word_count($sayar_data['detail']);
                $introtext_count = str_word_count($sayar_data['introtext']);

                $store_data = new RawArticle();
                $store_data->title = tounicode($sayar_data['title']);
                if ($detail_count > $introtext_count) {
                    $ict_data['detail'] = str_replace(array("\n", "\r", "\t"), '', $sayar_data['detail']);
                    $convert = html_entity_decode($sayar_data['detail']);
                    $store_data->content = tounicode($convert);
                } else {
                    $ict_data['introtext'] = str_replace(array("\n", "\r", "\t"), '', $sayar_data['introtext']);
                    $convert = html_entity_decode($sayar_data['introtext']);
                    $store_data->content = tounicode($convert);
                }
                $store_data->website_id = '40';
                $store_data->category_id = '13';
                $store_data->publishedDate =  date('Y-m-d H:i:s', strtotime($sayar_data['created']));
                $store_data->image = "https://" . $sayar_data['images']['lg'];
                $store_data->source_link = $sayar_link;
                $store_data->host = "sayar.com.mm";
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
                        "content_text" => tounicode($intro)
                    ]);
                }

                $current_id = $store_data->id;

                $store_data->content = preg_replace('#(<[p]*)(style=("|\')(.*?)("|\'))([a-z ]*>)#', '\\1\\6', $store_data->content);
                // $store_data->content = preg_replace('#(<[span ]*)(style=("|\')(.*?)("|\'))([a-z ]*>)#', '\\1\\6', $store_data->content);
                foreach (explode('</', $store_data->content) as $sayar_con) {
                    if (stripos($sayar_con, 'href') !== false) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($sayar_con);
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
                    } elseif (stripos($sayar_con, 'src') !== false) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($sayar_con);
                        libxml_clear_errors();
                        $images = $dom->getElementsByTagName('img');
                        foreach ($images as $image) {
                            $image = "https://www.sayar.com.mm/" . $image->getAttribute('src');
                            $content = new Content();
                            $content->article_id = $current_id;
                            $content->content_image = utf8_decode(urldecode($image));
                            $content->save();
                        }
                    } else {
                        foreach (explode('span>', $sayar_con) as $con) {
                            $con = str_replace(array("\n", "\r", "\t"), '', $con);
                            $con = strip_tags(str_replace("&nbsp;", " ", $con));
                            $con = str_replace('p>', '', $con);
                            $con = str_replace('strong>', '', $con);
                            $con = str_replace('<br />', '', $con);
                            $content = new Content;
                            $content->article_id = $current_id;
                            $content->content_text = tounicode($con);
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

                // $article_cat->category_id =  Helper::suggest_category($article_cat->id);
                // $article_cat->website_id = Helper::suggest_website($article_cat->id);
                // $article_cat->save();
            }
        }
    }

    public function yoyar_ent()
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
                    if (stripos($yyl_content, 'href') !== false) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($yyl_content);
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
                    } elseif (stripos($yyl_content, 'src')) {
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
                        $yyl_content = str_replace('a>', '', $yyl_content);
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

                                $array = ['div', 'Post Views:', 'span', 'post-views-count', 'br', 'dashicons-chart-bar', 'entry-meta', 'post-views-label',];
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
                $article_cat = RawArticle::find($article->id);
                // dd($article_cat);
                $article_tag = RawArticle::find($article_cat->id);
                $article_tag->tags()->sync((array)Helper::suggest_tags($article_tag->id));
                $article_tag->save();
            }
        }
    }

    public function yoyar_health()
    {
        $link = Link::find(4);
        $scraper = new Scraper(new Client());

        $scraper->handle($link);

        $articles = RawArticle::where('website_id', '37')->get();
        foreach ($articles as $article) {
            $check_exist = Content::where('article_id', $article->id)->get();
            if ($check_exist->count() < 1) {
                $content_feature = new Content;
                $file_format = file_get_contents($article->image);
                $filename = substr($article->image, strrpos($article->image, '/') + 1);
                // $filename = str_replace('.jpg', '.png', $filename);
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
                            $name = str_replace('.jpg', '.png', $name);
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
    }

    public function edge()
    {
        $ch = curl_init();
        $url = 'http://api.edge.com.mm/4/1/all';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $data = curl_exec($ch);

        curl_close($ch);
        $json_d = json_decode($data, true);

        // return $json_d;
        foreach ($json_d as $edge_data) {
            $checkExist = RawArticle::where('source_link', $edge_data['link'])->first();
            if (!isset($checkExist->id)) {
                $detail_count = str_word_count($edge_data['detail']);
                $introtext_count = str_word_count($edge_data['introtext']);

                $store_data = new RawArticle();
                $store_data->title = tounicode($edge_data['title']);
                if ($detail_count > $introtext_count) {
                    $edge_data['detail'] = str_replace(array("\n", "\r", "\t"), '', $edge_data['detail']);
                    $convert = html_entity_decode($edge_data['detail']);
                    $store_data->content = $convert;
                } else {
                    $edge_data['introtext'] = str_replace(array("\n", "\r", "\t"), '', $edge_data['introtext']);
                    $convert = html_entity_decode($edge_data['introtext']);
                    $store_data->content = $convert;
                }
                $store_data->website_id = '39';
                $store_data->category_id = '13';
                $store_data->publishedDate =  date('Y-m-d H:i:s', strtotime($edge_data['created_date']));
                $store_data->image = $edge_data['images'];
                $store_data->source_link = $edge_data['link'];
                $store_data->host = "edge.com.mm";
                $store_data->save();

                $content = new Content;
                $content->article_id = $store_data->id;
                $content->content_image = $store_data->image;
                $content->save();

                if ($detail_count > $introtext_count) {
                    $intro = $edge_data['introtext'];
                    $intro = strip_tags($intro);
                    $content_intro = Content::create([
                        "article_id" => $store_data->id,
                        "content_text" => $intro
                    ]);
                }

                $current_id = $store_data->id;

                $store_data->content = preg_replace('#(<[p]*)(style=("|\')(.*?)("|\'))([a-z ]*>)#', '\\1\\6', $store_data->content);
                // $store_data->content = preg_replace('#(<[span ]*)(style=("|\')(.*?)("|\'))([a-z ]*>)#', '\\1\\6', $store_data->content);
                foreach (explode('</', $store_data->content) as $edge_con) {
                    if (stripos($edge_con, 'href') !== false) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($edge_con);
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
                    } elseif (stripos($edge_con, 'src') !== false) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($edge_con);
                        libxml_clear_errors();
                        $images = $dom->getElementsByTagName('img');
                        foreach ($images as $image) {
                            $image = "https://www.edge.com.mm/" . $image->getAttribute('src');
                            $content = new Content();
                            $content->article_id = $current_id;
                            $content->content_image = utf8_decode(urldecode($image));
                            $content->save();
                        }
                    } else {
                        foreach (explode('>', $edge_con) as $con) {
                            $con = strip_tags(str_replace("&nbsp;", " ", $con));
                            $con = str_replace('a', '', $con);
                            $con = str_replace('p', '', $con);
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

                // $article_cat->category_id =  Helper::suggest_category($article_cat->id);
                // $article_cat->website_id = Helper::suggest_website($article_cat->id);
                // $article_cat->save();
            }
        }
    }

    public function yathar()
    {
        $ch = curl_init();
        $url = 'https://magazine.yathar.com/my/feed/';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $data = curl_exec($ch);

        curl_close($ch);
        $e = json_encode($data);
        $d = json_decode($e, true);
        $d = str_replace('&', '&amp;', $d);
        $rss = new DOMDocument();
        $rss->loadXML($d);

        $feed = array();

        foreach ($rss->getElementsByTagName('item') as $node) {
            // dd($node);
            // echo $node->getElementsByTagName('title')->item(0)->nodeValue;
            $item = array(
                'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
                'guid' => $node->getElementsByTagName('guid')->item(0)->nodeValue,
                'pubDate' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
                'content' => $node->getElementsByTagName('encoded')->item(0)->nodeValue,
                'image' => $node->getElementsByTagName('url')->item(0)->nodeValue,
                'website_id' => '43',
                'category_id' => '9'
            );

            array_push($feed, $item);
        }
        foreach ($feed as $f) {
            $checkExist = RawArticle::where('source_link', $f['guid'])->first();
            if (!isset($checkExist->id)) {
                $raw = new RawArticle();
                $raw->title = tounicode($f['title']);
                $raw->source_link = $f['guid'];
                $raw->publishedDate = date('Y-m-d H:i:s', strtotime($f['pubDate']));
                $raw->content = tounicode($f['content']);
                $raw->image = $f['image'];
                // $raw->website_id = $f['website_id'];
                // $raw->category_id = $f['category_id'];
                $raw->host = "yathar.com";
                // dd($raw);
                $raw->save();

                $current_id = $raw->id;

                $content_feature = new Content;
                $content_feature->article_id = $current_id;
                $content_feature->content_image = $raw->image;
                $content_feature->save();

                foreach (explode('</', str_replace(array('<p>'), '</', $raw->content)) as $f_content) {
                    if (stripos($f_content, 'href') !== false) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($f_content);
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
                    } elseif (stripos($f_content, 'src')) {
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
                            $content->save();
                        }
                    } else {
                        $search_array = array(
                            '/>', '<', 'a>', 'p>', '&8211;', 'figure', 'strong', '>'
                        );
                        $f_con = str_replace($search_array, '', $f_content);
                        $f_content = trim(html_entity_decode($f_con), " \t\n\r\0\x0B\xC2\xA0");

                        // $f_content = str_replace('>', '', $f_content);
                        $f_content = str_replace(array("\n", "\r", "\t"), '', $f_content);
                        $convert = html_entity_decode($f_content);
                        foreach (explode('strong>', $convert) as $con) {
                            foreach (explode('ul>', $con) as $con_ul) {
                                foreach (explode('li>', $con_ul) as $con_li) {
                                    foreach (explode('br>', $con_li) as $br) {
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
                $article_cat = RawArticle::find($raw->id);
                // dd($article_cat);
                $article_tag = RawArticle::find($article_cat->id);
                $article_tag->tags()->sync((array)Helper::suggest_tags($article_tag->id));
                $article_tag->save();

                // $article_cat->category_id =  Helper::suggest_category($article_cat->id);
                // $article_cat->website_id = Helper::suggest_website($article_cat->id);
                // $article_cat->save();
            }
        }
    }


    public function wedding_guide()
    {
        $ch = curl_init();
        $url = 'http://api.weddingguide.com.mm/relationships/7/0/all';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $data = curl_exec($ch);

        curl_close($ch);
        $json_d = json_decode($data, true);

        // return $json_d;
        foreach ($json_d as $wedding_data) {
            $checkExist = RawArticle::where('source_link', 'https://' . $wedding_data['link'])->first();
            if (!isset($checkExist->id)) {
                $detail_count = str_word_count($wedding_data['detail']);
                $introtext_count = str_word_count($wedding_data['introtext']);
                $store_data = new RawArticle();
                $store_data->title = tounicode($wedding_data['title']);
                if ($detail_count > $introtext_count) {
                    $wedding_data['detail'] = str_replace(array("\n", "\r", "\t"), '', $wedding_data['detail']);
                    $convert = html_entity_decode($wedding_data['detail']);
                    $store_data->content = $convert;
                } else {
                    $wedding_data['introtext'] = str_replace(array("\n", "\r", "\t"), '', $wedding_data['introtext']);
                    $convert = html_entity_decode($wedding_data['introtext']);
                    $store_data->content = $convert;
                }
                $store_data->website_id = '42';
                // $store_data->category_id = '1';
                $store_data->publishedDate =  date('Y-m-d H:i:s', strtotime($wedding_data['created']));
                $store_data->image = 'https://' . $wedding_data['images']['lg'];
                $store_data->host = "weddingguide.com.mm";

                $store_data->source_link = 'https://' . $wedding_data['link'];
                // dd($store_data);
                $store_data->save();

                $content = new Content;
                $content->article_id = $store_data->id;
                $content->content_image = $store_data->image;
                $content->save();

                if ($detail_count > $introtext_count) {
                    $intro = $wedding_data['introtext'];
                    $intro = strip_tags($intro);
                    $content_intro = Content::create([
                        "article_id" => $store_data->id,
                        "content_text" => $intro
                    ]);
                }

                $current_id = $store_data->id;

                // $store_data->content = preg_replace('#(<[p]*)(style=("|\')(.*?)("|\'))([a-z ]*>)#', '\\1\\6', $store_data->content);
                // $store_data->content = preg_replace('#(<[span ]*)(style=("|\')(.*?)("|\'))([a-z ]*>)#', '\\1\\6', $store_data->content);
                foreach (explode('</', $store_data->content) as $wedding_con) {
                    if (stripos($wedding_con, 'href') !== false) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($wedding_con);
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
                    } elseif (stripos($wedding_con, 'src') !== false) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($wedding_con);
                        libxml_clear_errors();
                        $images = $dom->getElementsByTagName('img');
                        foreach ($images as $image) {
                            $img = "https://www.weddingguide.com/" . $image->getAttribute('src');
                            $content = new Content();
                            $content->article_id = $current_id;
                            $content->content_image = utf8_decode(urldecode($img));
                            $content->save();
                        }
                    } else {
                        foreach (explode('>', $wedding_con) as $con) {
                            $con = strip_tags(str_replace("&nbsp;", " ", $con));
                            $con = strip_tags(str_replace("&nbsp", " ", $con));
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
    }


    public function moda_celebrity()
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
                $article->content = preg_replace('#<span class="detail-category">(.*?)</span>#', '', $article->content);
                $article->content = preg_replace('#<div class="col.s10.offset-s1.m12">(.*?)</div>#', '', $article->content);
                $article->content = preg_replace('#<h3 class="show-detail-title">(.*?)</h3>#', '', $article->content);
                $article->content = preg_replace('#<p class="description">(.*?)</p>#', '', $article->content);
                $article->content = preg_replace('#<div class="auther-img">(.*?)</div>#', '', $article->content);
                $article->content = preg_replace('#<p class="detail-author">(.*?)</p>#', '', $article->content);
                $article->content = preg_replace('#<p class="detail-date">(.*?)</p>#', '', $article->content);
                $article->content = preg_replace('#<div class="share-btn">(.*?)</div>#', '', $article->content);
                $article->content = preg_replace('#<div class="tag-list">(.*?)</div>#', '', $article->content);
                $article->content = preg_replace('#<div class="col.s12.m4.l3.right">(.*?)</div>#', '', $article->content);
                $article->content = preg_replace('#<div class="index-category.col.s10.offset-s1">(.*?)</div>#', '', $article->content);
                $article->content = preg_replace('#<div class="more-post-title.col.s10.offset-s1">(.*?)</div>#', '', $article->content);
                $article->content = preg_replace('#<hr class="for-mobile">(.*?)</hr>#', '', $article->content);
                $article->content = preg_replace('#<div class="share-group">(.*?)</div>#', '', $article->content);
                $article->content = preg_replace('#<div class="sidebar-post.col.s12.m12">(.*?)</div>#', '', $article->content);
                $article->content = preg_replace('#<div class="more-post-img">(.*?)</div>#', '', $article->content);
                $article->content = preg_replace('#<i class="material-icons">(.*?)</i>#', '', $article->content);
                $article->content = preg_replace('#<div class="col.s12.link-hover">(.*?)</div>#', '', $article->content);


                $article->content = trim(str_replace('"', "'", $article->content));
                // return $article->content;
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
                            if (strstr($img, "https://")) {
                                $content->content_image = $img;
                                $content->save();

                                $article->image = $content->content_image;
                                $article->update();
                            } else {
                                $content->content_image = "https://moda.com.mm/" . $img;
                                $content->save();
                            }
                        }
                    } else {
                        $moda_cele_content = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/si", '<$1$2>', $moda_cele_content);
                        $moda_cele_content = str_replace('i>', '', $moda_cele_content);
                        $moda_cele_content = str_replace('p>', '', $moda_cele_content);
                        $moda_cele_content = str_replace('strong', '', $moda_cele_content);
                        $moda_cele_content = str_replace('br>', '', $moda_cele_content);
                        $moda_cele_content = str_replace('div>', '', $moda_cele_content);
                        $moda_cele_content = str_replace('hr>', '', $moda_cele_content);
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
                $article_cat = RawArticle::find($article->id);
                // dd($article_cat);
                $article_tag = RawArticle::find($article_cat->id);
                $article_tag->tags()->sync((array)Helper::suggest_tags($article_tag->id));
                $article_tag->save();
            }
        }
    }

    public function moda_culture()
    {
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
                $article_cat = RawArticle::find($article->id);
                // dd($article_cat);
                $article_tag = RawArticle::find($article_cat->id);
                $article_tag->tags()->sync((array)Helper::suggest_tags($article_tag->id));
                $article_tag->save();
            }
        }
    }

    public function moda_beauty()
    {
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
                $article_cat = RawArticle::find($article->id);
                // dd($article_cat);
                $article_tag = RawArticle::find($article_cat->id);
                $article_tag->tags()->sync((array)Helper::suggest_tags($article_tag->id));
                $article_tag->save();
            }
        }
    }

    public function moda_fashion()
    {
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
                $article_cat = RawArticle::find($article->id);
                // dd($article_cat);
                $article_tag = RawArticle::find($article_cat->id);
                $article_tag->tags()->sync((array)Helper::suggest_tags($article_tag->id));
                $article_tag->save();
            }
        }
    }

    public function builders_guide()
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

                $store_data->website_id = '48';
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
    }
    public function farmer_media()
    {
        $ch = curl_init();
        $url = 'https://www.thefarmermedia.com/api/v1/news?token=ExzVdU3mvF2hzfGZKsfz';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $data = curl_exec($ch);

        curl_close($ch);
        $json_d = json_decode($data, true);

        // return $json_d;
        foreach ($json_d as $j_d) {
            foreach ($j_d as $d) {
                foreach ($d as $farmer_data) {
                    // dd($farmer_data);
                    $checkExist = RawArticle::where('source_link', $farmer_data['url'])->first();

                    if (!isset($checkExist->id)) {

                        $store_data = new RawArticle();
                        $store_data->title = tounicode($farmer_data['title']);
                        $farmer_data['description'] = str_replace(array("\n", "\r", "\t"), '', $farmer_data['description']);
                        $convert = html_entity_decode($farmer_data['description']);
                        $store_data->content = $convert;
                        $store_data->website_id = '49';
                        $store_data->category_id = '12';
                        $store_data->host = "thefarmermedia.com";
                        $store_data->publishedDate =  date('Y-m-d H:i:s', $farmer_data['post_date']);
                        if (!empty($farmer_data['main_image_1'])) {
                            $farmer_feature_image = $farmer_data['main_image_1'];
                            if (stripos($farmer_feature_image, 'src') !== false) {
                                $dom = new DOMDocument();
                                libxml_use_internal_errors(true);
                                $dom->loadHTML($farmer_feature_image);
                                libxml_clear_errors();
                                $images = $dom->getElementsByTagName('img');
                                foreach ($images as $image) {
                                    $image = $image->getAttribute('src');
                                    $store_data->image = $image;
                                }
                            }
                        }

                        $store_data->source_link = $farmer_data['url'];
                        // dd($store_data);
                        $store_data->save();

                        $current_id = $store_data->id;

                        // $store_data->content = preg_replace('#(<[p]*)(style=("|\')(.*?)("|\'))([a-z ]*>)#', '\\1\\6', $store_data->content);
                        // $store_data->content = preg_replace('#(<[span ]*)(style=("|\')(.*?)("|\'))([a-z ]*>)#', '\\1\\6', $store_data->content);
                        foreach (explode('</', $store_data->content) as $farmer_content) {
                            if (stripos($farmer_content, 'href') !== false) {
                                $dom = new DOMDocument();
                                libxml_use_internal_errors(true);
                                $dom->loadHTML($farmer_content);
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
                            } elseif (stripos($farmer_content, 'src') !== false) {
                                $dom = new DOMDocument();
                                libxml_use_internal_errors(true);
                                $dom->loadHTML($farmer_content);
                                libxml_clear_errors();
                                $images = $dom->getElementsByTagName('img');
                                foreach ($images as $image) {
                                    $image = "https://www.thefarmermedia.com/sites/thefarmermedia.com/" . $image->getAttribute('src');
                                    $content = new Content();
                                    $content->article_id = $current_id;
                                    $content->content_image = $image;
                                    $content->save();
                                }
                            } else {
                                foreach (explode('>', $farmer_content) as $con) {
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
                    }
                }
            }
        }
    }
    public function automobile()
    {
        $ch = curl_init();
        $url = 'http://api.automobiledirectory.com.mm/4/0/all';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $data = curl_exec($ch);

        curl_close($ch);
        $json_d = json_decode($data, true);

        // return $json_d;
        foreach ($json_d as $automobile_data) {
            $true_url = "https://" . $automobile_data['link'];
            $checkExist = RawArticle::where('source_link', $true_url)->first();
            if (!isset($checkExist->id)) {
                $detail_count = str_word_count($automobile_data['detail']);
                $introtext_count = str_word_count($automobile_data['introtext']);

                $store_data = new RawArticle();
                $store_data->title = tounicode($automobile_data['title']);
                if ($detail_count > $introtext_count) {
                    $automobile_data['detail'] = str_replace(array("\n", "\r", "\t"), '', $automobile_data['detail']);
                    $convert = html_entity_decode($automobile_data['detail']);
                    $store_data->content = tounicode($convert);
                } else {
                    $automobile_data['introtext'] = str_replace(array("\n", "\r", "\t"), '', $automobile_data['introtext']);
                    $convert = html_entity_decode($automobile_data['introtext']);
                    $store_data->content = tounicode($convert);
                }
                $store_data->website_id = '50';
                $store_data->category_id = '10';
                $store_data->publishedDate =  date('Y-m-d H:i:s', strtotime($automobile_data['created']));
                $store_data->image = "https://" . $automobile_data['images']['lg'];
                $store_data->source_link = $true_url;
                $store_data->host = "automobiledirectory.com";
                $store_data->save();

                $content = new Content;
                $content->article_id = $store_data->id;
                $content->content_image = $store_data->image;
                $content->save();

                if ($detail_count > $introtext_count) {
                    $intro = $automobile_data['introtext'];
                    $intro = strip_tags($intro);
                    $content_intro = Content::create([
                        "article_id" => $store_data->id,
                        "content_text" => $intro
                    ]);
                }

                $current_id = $store_data->id;

                $store_data->content = preg_replace('#(<[p]*)(style=("|\')(.*?)("|\'))([a-z ]*>)#', '\\1\\6', $store_data->content);
                // $store_data->content = preg_replace('#(<[span ]*)(style=("|\')(.*?)("|\'))([a-z ]*>)#', '\\1\\6', $store_data->content);
                foreach (explode('</', $store_data->content) as $automobile_con) {
                    if (stripos($automobile_con, 'href') !== false) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($automobile_con);
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
                    } elseif (stripos($automobile_con, 'src') !== false) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($automobile_con);
                        libxml_clear_errors();
                        $images = $dom->getElementsByTagName('img');
                        foreach ($images as $image) {
                            if (strpos($automobile_con, '/images')) {
                                $img = $image->getAttribute('src');
                                $content = new Content();
                                $content->article_id = $current_id;
                                $content->content_image =  "https://www.automobiledirectory.com.mm/" . utf8_decode(urldecode($img));
                                $content->save();
                            } else {
                                $img = $image->getAttribute('src');
                                $content = new Content();
                                $content->article_id = $current_id;
                                $content->content_image =  "https://www.automobiledirectory.com.mm/" . utf8_decode(urldecode($img));
                                $content->save();
                            }
                        }
                    } else {
                        foreach (explode('span>', $automobile_con) as $con) {
                            $con = strip_tags(str_replace("&nbsp;", " ", $con), '<br>');
                            $con = str_replace('colgrou', '', $con);
                            $con = str_replace('a>', '', $con);
                            $con = str_replace('tr>', '', $con);
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
            }
        }
    }
    public function ballonestar()
    {
        $ch = curl_init();
        $url = 'http://api.ballonestar.com/api/v2/news/lotaya';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // http: //localhost:8000/myanma_platform
        $data = curl_exec($ch);

        curl_close($ch);
        $json_d = json_decode($data, true);
        // return $json_d;
        foreach ($json_d as $ballone_data) {
            $checkExist = RawArticle::where('source_link', $ballone_data['source'])->first();
            if (!isset($checkExist->id)) {

                $store_data = new RawArticle();
                $store_data->title = tounicode($ballone_data['title']);

                $ballone_data['content'] = str_replace(array("\n", "\r", "\t"), '', $ballone_data['content']);
                $convert = html_entity_decode($ballone_data['content']);
                $store_data->content = $convert;
                $store_data->website_id = '52';
                $store_data->category_id = '11';
                $store_data->publishedDate =  date('Y-m-d H:i:s', strtotime($ballone_data['published_date']));
                $arr_image = $ballone_data['images'];
                foreach ($arr_image as $img) {
                    $store_data->image = $img;
                }
                $store_data->source_link = $ballone_data['source'];
                $store_data->host = "ballonestar.com";
                $store_data->save();

                $content = new Content;
                $content->article_id = $store_data->id;
                $content->content_image = $store_data->image;
                $content->save();
                $current_id = $store_data->id;

                $store_data->content = preg_replace('#(<[p]*)(style=("|\')(.*?)("|\'))([a-z ]*>)#', '\\1\\6', $store_data->content);

                foreach (explode('</', $store_data->content) as $edge_con) {
                    if (stripos($edge_con, 'href') !== false) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($edge_con);
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
                    } elseif (stripos($edge_con, 'src') !== false) {
                        $dom = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($edge_con);
                        libxml_clear_errors();
                        $images = $dom->getElementsByTagName('img');
                        foreach ($images as $image) {
                            $image = $image->getAttribute('src');
                            $content = new Content();
                            $content->article_id = $current_id;
                            $content->content_image = utf8_decode(urldecode($image));
                            $content->save();
                        }
                    } else {
                        foreach (explode('>', $edge_con) as $con) {
                            $con = strip_tags(str_replace("&nbsp;", " ", $con));
                            $con = str_replace('a', '', $con);
                            $con = str_replace('p', '', $con);
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
            }
        }
    }
    public function myanma_platform()
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
                $store_data->category_id = '2';
                $store_data->publishedDate =  date('Y-m-d H:i:s', strtotime(@$myanma_platform['publish_time']));
                $store_data->image = null;
                $store_data->source_link = 'https://www.myanmaplatform.com' . $myanma_platform['url'];
                $store_data->host = "myanma_platform.com";
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
            }
        }
    }
    public function myanmarload()
    {
        $link = Link::find(10);
        $scraper = new Scraper(new Client());

        $scraper->handle($link);

        $articles = RawArticle::where('website_id', '51')->get();
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
                            $content->content_image = $img;
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
                $article_cat = RawArticle::find($article->id);
                // dd($article_cat);
                $article_tag = RawArticle::find($article_cat->id);
                $article_tag->tags()->sync((array)Helper::suggest_tags($article_tag->id));
                $article_tag->save();
            }
        }
    }
}
