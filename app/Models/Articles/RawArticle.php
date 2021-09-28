<?php

namespace App\Models\Articles;

use Illuminate\Database\Eloquent\Model;
use App\Models\Settings\Category;
use Illuminate\Database\Eloquent\SoftDeletes;

class RawArticle extends Model
{
    //
    use SoftDeletes;

    protected $convertable = ['title', 'content'];

    public function category()
    {
        return $this->belongsTo('App\Models\Settings\Category');
    }
    public function website()
    {
        return $this->belongsTo('App\Models\Scrapes\Website');
    }
    public function tags()
    {
        return $this->belongsToMany('App\Models\Settings\Keyword', 'articles_tags', 'article_id', 'tag_id');
        // return $this->belongsToMany('App\Models\Settings\Keyword', 'articles_tags', 'article_id', 'tag_id')->withPivot('tag_id');
    }
}
