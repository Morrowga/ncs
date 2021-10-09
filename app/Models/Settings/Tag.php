<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    //

    protected $fillable = ['name', 'nameMm'];

    protected $table = "tags";

    public function articles()
    {
        return $this->belongsToMany('App\Settings\Tag', 'article_tag', 'article_id', 'tag_id');
    }
}
