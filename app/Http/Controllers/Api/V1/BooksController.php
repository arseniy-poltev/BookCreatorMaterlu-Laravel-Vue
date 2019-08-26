<?php

namespace App\Http\Controllers\Api\V1;

use App\Book;
use App\Http\Controllers\Controller;
use App\Http\Resources\Book as BookResource;
use App\Http\Requests\Admin\StoreBooksRequest;
use App\Http\Requests\Admin\UpdateBooksRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Utils;
use DB;
use Auth;
use PHPUnit\Framework\Constraint\FileExists;
use Response;

class BooksController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $books = $user->getBooks();
        $result = [];
        foreach ($books as $book) {
            $artist = $book->user;
            array_push($result, array_merge($book->toArray(), [
                'artist'    =>  $artist ? $artist->name : ''
            ]));
        }
        return new BookResource($result);
    }

    public function show($id)
    {
        if (Gate::denies('book_view')) {
            return abort(401);
        }

        $book = Book::with(['user'])
            ->withCount(['characters', 'pages'])
            ->findOrFail($id);
        return new BookResource($book->checkuser() ? $book : null);
    }

    public function store(StoreBooksRequest $request)
    {
        if (Gate::denies('book_create')) {
            return abort(401);
        }

        $statement = DB::select("SHOW TABLE STATUS LIKE 'books'");
        $nextId = $statement[0]->Auto_increment;

        Utils::makeDirForBook($nextId);

        if (isset($request->img)) {
            $request->merge(Utils::saveImageFile($request->img, $nextId));
        }


        $book = Book::create($request->all());


        return (new BookResource($book))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateBooksRequest $request, $id)
    {
        if (Gate::denies('book_edit')) {
            return abort(401);
        }

        $book = Book::findOrFail($id);

        if (isset($request->img)) {
            if (!$request->get('img_url') || $request->img != $request->img_url) {
                $request->merge(Utils::saveImageFile($request->img, $id));
            }
        } else {
            //delete file
            $request->merge(['img_url' => null]);
        }

        if (Gate::denies('book_create')) {
            $request->except(['user_id']);
        }

        $book->update($request->all());




        return (new BookResource($book))
            ->response()
            ->setStatusCode(202);
    }

    public function destroy($id)
    {
        if (Gate::denies('book_delete')) {
            return abort(401);
        }

        $book = Book::findOrFail($id);
        $book->delete();

        return response(null, 204);
    }

    public function sendBookToMaterlu($id)
    {

        $book = Book::findOrFail($id);
        if (!$book->checkuser()) {
            return Response::json(array(
                'code'      =>  500,
                'message'   =>  Utils::ERR_AUTH
            ), 500);
        }
        //check pages status
        $pages = $book->pages;
        $cnt = 0;
        foreach ($pages as $page) {
            $cnt++;
            if ($page->status != Utils::BOOK_STATUS_CORRECT) {

                return Response::json(array(
                    'code'      =>  500,
                    'message'   =>  Utils::ERR_CHECK_PAGE
                ), 500);
            }
        }
        if ($cnt == 0) {
            return Response::json(array(
                'code'      =>  500,
                'message'   =>  Utils::ERR_NO_PAGE
            ), 500);
        }

        //character json check
        $characters = $book->characters;
        $cnt = 0;
        foreach ($characters as $character) {
            $cnt++;
            if (!Utils::existLayerJSON($id, $character->id)) {
                return Response::json(array(
                    'code'      =>  500,
                    'message'   =>  $character->name . Utils::ERR_NO_CHARACTER_JSON
                ), 500);
            }
        }
        if ($cnt == 0) {
            return Response::json(array(
                'code'      =>  500,
                'message'   =>  Utils::ERR_NO_CHARACTER
            ), 500);
        }


        //test
        $bookIdInMaterlu = 10;
        //send character svgs
        $index = 1;
        foreach ($characters as $character) {

            Utils::sendFileToMaterlu($character->origin_url, "character_" . $index . ".svg", $bookIdInMaterlu, 'characters');
            Utils::sendFileToMaterlu(Utils::getLayerJSONPath($id, $character->id), "character_" . $index . ".json", $bookIdInMaterlu, 'characters');
            $index++;
        }
        $index = 1;
        foreach ($pages as $page) {
            Utils::sendFileToMaterlu($page->origin_url, $index . ".svg", $bookIdInMaterlu, 'pages');
            $index++;
        }
        return Response::json(array(
            'code'      =>  200,
            'message'   =>  "Deploy Success!"
        ), 200);
        //send character jsons
        //send page svgs
    }

    public function generatePDF(Request $request)
    {
        $book = Book::findOrFail($request->id);
        if (!$book->checkuser()) {
            return Response::json(array(
                'code'      =>  500,
                'message'   =>  Utils::ERR_AUTH
            ), 500);
        }
        //make param.json
        $pages = $book->pages;
        $page_names = [];
        $png_names = [];
        foreach ($pages as $page) {
            array_push($page_names, $page->file_name);
            array_push($png_names, basename($page->file_name, ".svg") . ".png");
        }

        //load param if exist
        // $param_path = Utils::makeFilePath(Utils::makeDirPath($book->id, ''), "layer", 'json');
        // $characters = [];
        // if (file_exists($param_path)) {
        //     $characters = file_get_contents(public_path($param_path));
        // }
        // echo ($characters);
        // exit();


        $params = [
            'lang' => $request->language,
            'story' => $request->id,
            'quality' => $request->type,
            'font_style' => $request->font_style,
            'output_file'   =>  $request->type . '.pdf',
            'png_dir'   =>  'png_web',
            'source_web_dir'    =>  'svg_web',
            'source_print_dir'  =>  'svg_print',
            'pages' =>  $page_names,
            'characters'    =>  json_decode($request->characters)
        ];
        Utils::makeParamJSON($request->id, json_encode($params));

        //make web pdf
        $result = Utils::svg2pdf($request->id, $request->type);
        


        return (new BookResource($png_names))
            ->response()
            ->setStatusCode(202);
    }
}