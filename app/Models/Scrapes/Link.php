<?php

namespace App\Models\Scrapes;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $table = "links";

    public function category()
    {
        return $this->belongsTo('App\Models\Settings\Category', 'category_id');
    }
    public function website()
    {
        return $this->belongsTo('App\Models\Scrapes\Website', 'website_id');
    }
    public function itemSchema()
    {
        return $this->belongsTo('App\Models\Scrapes\ItemSchema', 'item_schema_id');
    }
}
