<?php

namespace App\Http\Controllers\Api\V1;

use App\Page;
use App\Book;
use App\Character;
use App\Http\Controllers\Controller;
use App\Http\Resources\Page as PageResource;
use App\Http\Requests\Admin\StorePagesRequest;
use App\Http\Requests\Admin\UpdatePagesRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Utils;
use DB;

class PagesController extends Controller
{
    public function index()
    {
        return new PageResource([]);
    }

    public function getPages($bId)
    {
        $book = Book::find($bId);
        if (!$book->checkuser()) {
            return Response::json(array(
                'code'      =>  403,
                'message'   =>  Utils::ERR_AUTH
            ), 403);
        }
        return new PageResource($book->pages);
    }

    public function getPagesSVG($bId)
    {
        //return $this->getPages($bId);
        $book = Book::findOrFail($bId);
        if (!$book->checkuser()) {
            return Response::json(array(
                'code'      =>  403,
                'message'   =>  Utils::ERR_AUTH
            ), 403);
        }
        $pages = $book->pages;
        $result = [];

        foreach ($pages as $page) {
            array_push(
                $result,
                [
                    'id'        =>  $page->id,
                    'file_name' => $page->file_name,
                    'status'    =>  $page->status,
                    'img_path'  =>  $page->thumb_url,
                    // 'svg'   =>  file_get_contents(public_path($page->img_url))
                ]
            );
        }
        return new PageResource($result);
    }

    public function changeStatus(Request $request)
    {
        $id = $request->id;
        $status = $request->status;
        $page = Page::findOrFail($id);
        if ($page == null) {
            return response(null, 404);
        }
        $page->status = $status;
        $page->save();
        // return response(null, 200);
    }

    public function show($id)
    {
        if (Gate::denies('page_view')) {
            return abort(401);
        }

        $page = Page::with([])->findOrFail($id);
        $characters = $page->book->characters;
        $jsons = [];
        foreach ($characters as $character) {
            array_push($jsons, Utils::getLayerJSON($character->book_id, $character->id));
        }
        $page['characters'] = $jsons;

        return new PageResource($page);
    }

    public function savePage(Request $request)
    {
        if (Gate::denies('page_create')) {
            return abort(401);
        }
        if ($request->file('file')) {
            $request->merge(Utils::saveImageFile($request->file('file'), $request->book_id));
        }
        $page = Page::create($request->all());

        return (new PageResource($page))
            ->response()
            ->setStatusCode(201);
    }

    public function changeOrder(Request $request)
    {
        $bookId = $request->book_id;
        $orderData = json_decode($request->pages_order);
        $index = 0;
        foreach ($orderData as $data) {
            $page = Page::findOrFail($data);
            if ($page == null || $page->book_id != $bookId) {
                continue;
            }
            $page->number = $index++;
            $page->save();
        }
        // return response(null, 200);
    }

    public function store(StorePagesRequest $request)
    {
        if (Gate::denies('page_create')) {
            return abort(401);
        }


        // if (isset($request->img)) {
        //     $request->merge(['img_url' => Utils::saveImageFile($request->file('img'), $request->book_id)]);
        // }

        // $page = Page::create($request->all());

        // return (new PageResource($page))
        //     ->response()
        //     ->setStatusCode(201);
    }

    public function update(UpdatePagesRequest $request, $id)
    {
        if (Gate::denies('page_edit')) {
            return abort(401);
        }

        $page = Page::findOrFail($id);

        if ($request->file('img')) {
            if (!$request->get('img_url') || $request->img != $request->img_url) {
                $request->merge(Utils::saveImageFile($request->file('img'), $request->book_id));
            }
            //  $request->status = Utils::BOOK_STATUS_NOTCHECK;
        } else {
            //delete file
            //$request->merge(['img_url' => null]);
        }

        $page->update($request->all());


        return (new PageResource($page))
            ->response()
            ->setStatusCode(202);
    }

    public function destroy($id)
    {
        if (Gate::denies('page_delete')) {
            return abort(401);
        }

        $page = Page::findOrFail($id);
        $page->delete();

        return response(null, 204);
    }

    public function applySVG(Request $request)
    {
        $id = $request->id;
        $index = $request->index;
        $param = $request->json;
        $page = Page::findOrFail($id);
        if ($page == null) {
            return;
        }
        Utils::apply_page($page->book_id, $page->file_name, $page->img_url, $param);
        $path = public_path('/uploads/book/' . $page->book_id . "/temp/" . $page->file_name . '_temp.png');
        $base64 = '';
        if (file_exists($path)) {
            $data = file_get_contents($path);
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        return response([
            'content' =>  $base64,
            'index' =>  $index
        ], 200);
    }

    public function checkPage($id)
    {
        $page = Page::findOrFail($id);
        if ($page == null) {
            return;
        }
        Utils::check_page($page->book_id, $page->file_name, $page->img_url);
        $path = public_path('/uploads/book/' . $page->book_id . "/temp/" . $page->file_name . '_temp.json');
        $result = '';
        if (file_exists($path)) {
            $result = file_get_contents($path);
        }
        return response([
            'result'    =>  $result,
            'img_url'   =>  $page->thumb_url,
        ], 200);
    }
}