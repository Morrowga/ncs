<?php

namespace App\Models\Articles;

use Illuminate\Database\Eloquent\Model;
use App\Models\Settings\Category;
// use Illuminate\Database\Eloquent\SoftDeletes;

class RawArticle extends Model
{
    //
    // use SoftDeletes;
    protected $table = "raw_articles";

    protected $fillable = ['title', 'content', 'image', 'host', 'publisedDate', 'fontType', 'sent_status', 'update_status', 'uuid', 'thumbnailUrl'];

    protected $convertable = ['title', 'content'];

    protected $casts = ['id' => 'string'];

    protected $guarded = ['id'];

    public function category()
    {
        return $this->belongsTo('App\Models\Settings\Category', 'category_id');
    }

    public function website()
    {
        return $this->belongsTo('App\Models\Scrapes\Website', 'website_id');
    }

    public function tags()
    {
        return $this->belongsToMany('App\Models\Settings\Tag', 'article_tag', 'article_id', 'tag_id')->withPivot('tag_id');
    }

    //////

    // protected $convertable = ['title', 'content'];

    // public function category()
    // {
    //     return $this->belongsTo('App\Models\Settings\Category');
    // }
    // public function website()
    // {
    //     return $this->belongsTo('App\Models\Scrapes\Website');
    // }
    // public function tags()
    // {
    //     return $this->belongsToMany('App\Models\Settings\Keyword', 'articles_tags', 'article_id', 'tag_id');
    //     // return $this->belongsToMany('App\Models\Settings\Keyword', 'articles_tags', 'article_id', 'tag_id')->withPivot('tag_id');
    // }
}
