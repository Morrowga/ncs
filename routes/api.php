<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('send-new-articles/{id}', 'Notify\WebhookController@sendArticle')->name('send-test-webhook');

// Route::get('send-update-trending', 'Notify\WebhookController@sendArticle')->name('send-test-webhook');


Route::get('articles/{id}', 'API\ArticleController@index');
Route::post('articles', 'API\ArticleController@get_all_articles');

Route::get('categories', 'API\CategoryController@index');

Route::get('related', 'API\ArticleController@get_related_artilces');

Route::post('engagement', 'API\ArticleController@get_engagement_articles');
Route::get('trend', 'API\ArticleController@get_trend');
Route::get('trend?category_id=health', 'API\ArticleController@get_trend');

Route::get('categories/list', 'API\CategoryController@index');

Route::post('indexing', 'API\ArticleController@indexing');


// Route::get('test-index', 'API\ArticleController@testingIndex');

Route::get('transferData', 'API\ArticleController@transferData');
