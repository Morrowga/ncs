<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //

    protected $fillable = ['name', 'nameMm'];

    protected $table = "categories";


    public function articles()
    {
        return $this->belongsToMany('App\Models\Articles\RawArticle', 'article_category', 'category_id', 'article_id');
    }
    public function deleteData($id)
    {
        return static::find($id)->delete();
    }
}
