<?php

use Illuminate\Support\Facades\Route;
use App\Articles\RawArticleController;
use Illuminate\Support\Facades\Auth;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'HomeController@index');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
// Category
Route::resource('category', 'Settings\CategoryController');
Route::resource('tag', 'Settings\TagController');

// Web Crawl and Scrape
Route::resource('website', 'Web_Scraping\WebsiteController');
// Route::resource('webscrape', 'Scrapes\WebController');
Route::resource('links', 'Web_Scraping\LinksController');

Route::resource('itemschema', 'Web_Scraping\ItemSchemaController');
Route::post('links/set-item-schema', 'Web_Scraping\LinksController@setItemSchema');
Route::post('links/scrape', 'Web_Scraping\LinksController@scrape');
//contents
// Route::post('contents', 'Articles\RawArticleController@createContent')->name('contents.store');
Route::resource('contents', 'Articles\ContentController');
// Articles
Route::resource('raw_articles', 'Articles\RawArticleController');
Route::resource('sent_articles', 'Articles\SentArticleController');
Route::resource('reject_articles', 'Articles\RejectArticleController');

// report
Route::get('/monthly', 'Articles\SentArticleController@monthly')->name('monthly');
//log
Route::get('/larave_logs', 'Articles\RawArticleController@laravelLog')->name('laravellogs');
//excel
Route::get('/monthly/export', 'Articles\SentArticleController@export')->name('monthly.export');
// Route::get('/monthly/excel', 'Articles\SentArticleController@excel')->name('monthly.excel');

Route::put('raw_articles/sent_lotaya/{id}', 'Articles\RawArticleController@sent_lotaya')->name('raw_articles.sent_lotaya');
Route::put('raw_articles/duplicate/{id}', 'Articles\RawArticleController@duplicate')->name('raw_articles.duplicate');
Route::put('raw_articels/blacklist/{id}', 'Articles\RawArticleController@blacklist')->name('raw_articles.blacklist');
Route::get('activities', 'Articles\RawArticleController@activityLog')->name('activity');


// get data and crawl data
Route::get('/con_get', 'Web_Scraping\LinksController@getCont');
Route::get('/con_get_ms', 'Web_Scraping\LinksController@getConMS');
Route::get('/ondoctor', 'Web_Scraping\LinksController@ondoctor');
Route::get('/healthcare', 'Web_Scraping\LinksController@healthCare');
Route::get('/ict', 'Web_Scraping\LinksController@ict');
Route::get('/sayar', 'Web_Scraping\LinksController@sayar');
Route::get('/yyl_health', 'Web_Scraping\LinksController@yoyar_health');
Route::get('/yyl_ent', 'Web_Scraping\LinksController@yoyar_ent');
Route::get('/edge', 'Web_Scraping\LinksController@edge');
Route::get('/wedding', 'Web_Scraping\LinksController@wedding_guide');
Route::get('/moda_cele', 'Web_Scraping\LinksController@moda_celebrity');
Route::get('/moda_culture', 'Web_Scraping\LinksController@moda_culture');
Route::get('/moda_beauty', 'Web_Scraping\LinksController@moda_beauty');
Route::get('/moda_fashion', 'Web_Scraping\LinksController@moda_fashion');
Route::get('/yathar', 'Web_Scraping\LinksController@yathar');
Route::get('/builders_guide', 'Web_Scraping\LinksController@builders_guide');
Route::get('/farmer_media', 'Web_Scraping\LinksController@farmer_media');
Route::get('/automobile', 'Web_Scraping\LinksController@automobile');
Route::get('/ballonestar', 'Web_Scraping\LinksController@ballonestar');
Route::get('/myanma_platform', 'Web_Scraping\LinksController@myanma_platform');
