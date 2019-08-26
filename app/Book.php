<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;

/**
 * Class Book
 *
 * @package App
 * @property string $name
 */
class Book extends Model
{
    use SoftDeletes;


    protected $fillable = ['name', 'img_url', 'user_id'];


    public static function storeValidation($request)
    {
        return [
            'name'      =>  'max:191|required',
            // 'user_id'   =>  'required'
            // 'logo_url'  =>  'required'
        ];
    }

    public static function updateValidation($request)
    {
        return [
            'name'      =>  'max:191|required',
            // 'user_id'   =>  'required'
            // 'logo_url'  =>  'required'
        ];
    }

    public function user()
    {
        return $this->belongsTo('App\User')
            ->select(['id', 'name']);
    }

    public function checkUser()
    {
        if (Utils::isAdmin()) {
            return true;
        }
        if ($this->user_id != Auth::id()) {
            return false;
        }
        return true;
    }

    public function characters()
    {
        return $this->hasMany('App\Character');
    }

    public function pages()
    {
        return $this->hasMany('App\Page')
            ->orderBy('number', 'asc')
            ->orderBy('id', 'asc');
    }

    public function delete()
    {
        $characters = $this->characters()->get();
        foreach ($characters as $character) {
            $character->delete();
        }
        $pages = $this->pages()->get();
        foreach ($pages as $page) {
            $page->delete();
        }

        return parent::delete();
    }
}