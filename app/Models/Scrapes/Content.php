<?php

namespace App\Models\Scrapes;

use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    //
    protected $table = "contents";

    protected $fillable = ['content_image', 'content_text', 'article_id'];
}
