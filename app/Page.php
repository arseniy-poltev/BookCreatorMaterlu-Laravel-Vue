<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    //
    protected $fillable = ['number', 'img_url', 'status', 'book_id', 'thumb_url', 'origin_url', 'file_name'];

    public static function storeValidation($request)
    {
        return [
            // 'name'      =>  'max:191|required',
            'book_id'   =>  'required',
        ];
    }

    public static function updateValidation($request)
    {
        return [
            // 'name'      =>  'max:191|required',
            'book_id'   =>  'required',
        ];
    }

    public function book()
    {
        return $this->belongsTo('App\Book');
    }
}