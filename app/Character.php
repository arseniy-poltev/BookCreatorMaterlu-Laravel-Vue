<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Character extends Model
{
    //
    protected $fillable = ['name', 'img_url', 'book_id', 'thumb_url', 'origin_url', 'file_name'];

    public static function storeValidation($request)
    {
        return [
            'name'      =>  'max:191|required',
            'book_id'   =>  'required'
            // 'logo_url'  =>  'required'
        ];
    }

    public static function updateValidation($request)
    {
        return [
            'name'      =>  'max:191|required',
            'book_id'   =>  'required'
            // 'logo_url'  =>  'required'
        ];
    }

    public function book()
    {
        return $this->belongsTo('App\Book');
    }
}