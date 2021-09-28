<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IndexingArticle extends Model
{
    //
    protected $fillable = ['url', 'article_id', 'imageUrl', 'providerCategory', 'content', 'title', 'publishedDate', 'host'];

    protected $table = 'indexing_articles';
}
