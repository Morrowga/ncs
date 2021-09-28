<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    //
    public function articles()
    {
        return $this->belongsToMany('App\Models\Articles\RawArticle', 'articles_tags', 'tag_id', 'article_id');
    }
}
