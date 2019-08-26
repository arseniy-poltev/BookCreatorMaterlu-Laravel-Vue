<?php

namespace App\Http\Controllers\Api\V1;

use App\Character;
use App\Book;
use App\Http\Controllers\Controller;
use App\Http\Resources\Character as CharacterResource;
use App\Http\Requests\Admin\StoreCharactersRequest;
use App\Http\Requests\Admin\UpdateCharactersRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Utils;
use Auth;

class CharactersController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $books = $user->getBooks();
        $result = [];
        foreach ($books as $book) {
            $book['characters'] = $book->characters;
            array_push($result, $book);
        }
        return new CharacterResource($result);
    }

    public function getCharacters($bId)
    {
        $book = Book::find($bId);
        return new CharacterResource($book->characters);
    }

    public function show($id)
    {
        if (Gate::denies('character_view')) {
            return abort(401);
        }

        $character = Character::with([])->findOrFail($id);

        return new CharacterResource($character);
    }

    public function store(StoreCharactersRequest $request)
    {
        if (Gate::denies('character_create')) {
            return abort(401);
        }


        if (isset($request->img)) {
            $request->merge(Utils::saveImageFile($request->file('img'), $request->book_id));
        }

        $character = Character::create($request->all());

        return (new CharacterResource($character))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateCharactersRequest $request, $id)
    {
        if (Gate::denies('character_edit')) {
            return abort(401);
        }

        $character = Character::findOrFail($id);

        if ($request->file('img')) {
            if (!$request->get('img_url') || $request->img != $request->img_url) {
                $request->merge(Utils::saveImageFile($request->file('img'), $character->book_id));
            }
        } else {
            //delete file
            //$request->merge(['img_url' => null]);
        }

        $character->update($request->all());


        return (new CharacterResource($character))
            ->response()
            ->setStatusCode(202);
    }

    public function destroy($id)
    {
        if (Gate::denies('character_delete')) {
            return abort(401);
        }

        $character = Character::findOrFail($id);
        $character->delete();

        return response(null, 204);
    }

    public function makeLayerJSON(Request $request)
    {
        $id = $request->id;
        $json = $request->json;
        $character = Character::findOrFail($id);
        if ($character == null) {
            return;
        }
        Utils::makeLayerJSON($character->book_id, $id, $json);
        Utils::extract_layer($character->book_id, $id, $character->img_url);
    }

    public function getLayerJSON($id)
    {
        $character = Character::findOrFail($id);
        if ($character == null) {
            return;
        }
        $json = Utils::getLayerJSON($character->book_id, $id);
        //return response($json, 200);
        return (new CharacterResource([
            'json' => $json,
            'path' => Utils::getLayerJSONPath($character->book_id, $id)
        ]))
            ->response()
            ->setStatusCode(202);
    }

    public function getSVG($id)
    {
        $character = Character::findOrFail($id);
        if ($character == null) {
            return;
        }

        return response(file_get_contents(public_path($character->img_url)), 200);
    }

    public function getCharactersInfo($bId)
    {
        $book = Book::findOrFail($bId);
        if (!$book->checkuser()) {
            return Response::json(array(
                'code'      =>  403,
                'message'   =>  Utils::ERR_AUTH
            ), 403);
        }
        $characters = $book->characters;
        $result = [];
        foreach ($characters as $character) {
            array_push($result, [
                'id'    =>  $character->id,
                // 'svg'   =>  file_get_contents(public_path($character->img_url)),
                'thumb_url' =>  $character->thumb_url,
                'json'  =>  Utils::getLayerJSON($bId, $character->id)
            ]);
        }
        return (new CharacterResource($result))
            ->response()
            ->setStatusCode(202);
    }

    public function applySVG(Request $request)
    {
        //$executionStartTime = microtime(true);
        // $id = $request->id;
        // $param = $request->json;
        $id = $_GET['id'];
        $param = base64_decode($_GET['param']);

        $character = Character::findOrFail($id);
        if ($character == null) {
            return;
        }
        $path = public_path(Utils::apply_svg($character->book_id, $id, $character->img_url, $param));

        //$executionEndTime = microtime(true);
        //$seconds = $executionEndTime - $executionStartTime;
        //echo $seconds;
        $content = '';
        if (file_exists($path)) {
            $content = file_get_contents($path);
        }


        return response($content)
            ->header('Content-Type', 'image/png')
            ->header('Pragma', 'public')
            ->header('Cache-Control', 'max-age=60, must-revalidate');
    }
}