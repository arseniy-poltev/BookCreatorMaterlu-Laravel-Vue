<?php

namespace App;

use File;
use PHPImageWorkshop\ImageWorkshop;
use DOMDocument;
use Imagick;
use ImagickPixel;
use Auth;
use DomQuery;

class Utils
{
    const BOOK_LOGO_PATH        =      '/uploads/images/books/';
    const BOOK_PREFIX           =      '/uploads/book/';
    const BOOK_URL_SVG_WEB      =      '/svg_web';
    const BOOK_URL_PNG_WEB      =      '/png_web';
    const BOOK_URL_THUMBNAIL    =      '/thumbnail';
    const BOOK_URL_TEMP         =      '/temp';
    const BOOK_URL_PRINT        =      '/svg_print';
    const BOOK_URL_APPLY        =      '/apply';
    const BOOK_URL_ORIGIN       =      '/origin';
    const BOOK_URL_JSON         =      '/layer_json';
    const THUMB_PNG_WIDTH       =      150;
    const THUMB_PNG_HEIGHT      =      150;
    const BOOK_STATUS_CORRECT   =       'correct';
    const BOOK_STATUS_INCORRECT =       'incorrect';
    const BOOK_STATUS_NOTCHECK  =       'not_check';
    const ERR_AUTH              =       'Auth Error!';
    const ERR_CHECK_PAGE        =       'Check Pages!';
    const ERR_NO_CHARACTER_JSON =       ' character has no JSON!';
    const ERR_NO_CHARACTER      =       'No character!';
    const ERR_NO_PAGE           =       'No page!';

    const PYTHON_SCIPRT_PATH    =       '/uploads/book/svg2pdf_server.py';
    const PYTHON_LIB_PATH       =       '/uploads/book/';
    const PARAM_JSON_FILE_NAME  =       'param';

    public static function saveBase64Image($image)
    {
        $ext = explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];
        $ext = explode('+', $ext)[0];
        $name = time() . '.' . $ext;
        $url = public_path(self::BOOK_LOGO_PATH) . $name;


        $header = substr($image, 0, strpos($image, ',') + 1);
        $image = str_replace($header, '', $image);
        $image = str_replace(' ', '+', $image);

        file_put_contents($url, base64_decode($image));

        return self::BOOK_LOGO_PATH . $name;
    }
    private static function reduceData($data, $base)
    {
        $decode = base64_decode($data);
        $image = imagecreatefromstring($decode);
        $layer = ImageWorkshop::initFromResourceVar($image);
        $ancho = $layer->getWidth() * $base;
        $layer->resizeInPixel($ancho, null, true);
        $image = $layer->getResult();
        ob_start();
        imagepng($image, null, 9);
        $imagedata = ob_get_contents();
        ob_end_clean();
        return base64_encode($imagedata);
    }

    private static function getimgfile($data, $book_id, $name, $ext = 'png')
    {
        $decode = base64_decode($data);

        $path = self::makeDirPath($book_id, self::BOOK_URL_APPLY);
        $public_path = public_path(self::makeFilePath($path, $name, $ext));

        $str = file_put_contents($public_path, $decode);
        if ($str == true) {
            return $name . "." . $ext;
        } else {
            return "FAIL";
        }
    }

    public static function makeFilePath($path, $name, $ext = 'svg')
    {
        return $path . '/' . $name . '.' . $ext;
    }

    public static function makeDirPath($book_id, $sub = '')
    {
        return self::BOOK_PREFIX . $book_id . $sub;
    }

    private static function reduceSVGSize($source, $out)
    {
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->loadXML($source);
        $svg = $dom->documentElement;

        if (!$svg->hasAttribute('viewBox')) { // viewBox is needed to establish
            // userspace coordinates
            $pattern = '/^(\d*\.\d+|\d+)(px)?$/'; // positive number, px unit optional

            $interpretable =  preg_match($pattern, $svg->getAttribute('width'), $width) &&
                preg_match($pattern, $svg->getAttribute('height'), $height);

            if ($interpretable) {
                $view_box = implode(' ', [0, 0, $width[0], $height[0]]);
                $svg->setAttribute('viewBox', $view_box);
            } else { // this gets sticky
                throw new Exception("viewBox is dependent on environment");
            }
        }

        //$svg->setAttribute('width', '400');
        //$svg->setAttribute('height', '400');
        $dom->save($out);
    }

    private static function process($book_id, $file_name, $archivo, $base = 0.25, $status = 0)
    {
        $path = self::makeDirPath($book_id, self::BOOK_URL_ORIGIN);
        $public_path = public_path(self::makeFilePath($path, $file_name));

        $str = file_get_contents($public_path);

        $explode = explode(' id="', $str);
        foreach ($explode as $key => $segmento) {
            $fragmento = explode('"', $segmento, 2);
            $fragmento[0] = mb_strtolower($fragmento[0]);
            $explode[$key] = join('"', $fragmento);
        }
        $svg = join(' id="', $explode);

        $explode = explode(' font-family="', $svg);
        foreach ($explode as $key => $segmento) {
            if ($key > 0) {
                $fragmento = explode('"', $segmento, 2);
                $fragmento[0] = "'Futura'";
                $fragmento[1]  = ' id="txt' . $key . '"' . $fragmento[1];
                $explode[$key] = join('"', $fragmento);
            }
        }
        $svg = join(' font-family="', $explode);


        $explode = explode(' fill="', $svg);
        foreach ($explode as $key => $segmento) {
            $fragmento = explode('"', $segmento, 2);
            $fragmento[0] = mb_strtolower($fragmento[0]);
            $explode[$key] = join('"', $fragmento);
        }
        $fullsvg = join(' fill="', $explode);

        // PRINT VERSION -> IMAGE OUTSIDE
        $explode = explode('xlink:href', $fullsvg);
        $i = 0;
        foreach ($explode as $key => $segmento) {
            if ($key > 0) {
                $fragmento = explode('"', $segmento, 3);
                $datatype = explode(",", $fragmento[1]);
                if (count($datatype) > 1) {
                    $name = $archivo . '_' . $i;
                    $aux = explode(";", $datatype[0]);
                    $extension = explode("/", $aux[0]);
                    $imgname = self::getimgfile($datatype[1], $book_id,  $name, $extension[1]);
                    $fragmento[1] = $imgname;
                    $explode[$key] = join('"', $fragmento);
                    $i++;
                }
            }
        }
        $svg = join("xlink:href", $explode);

        // $path = self::makeDirPath($book_id, self::BOOK_URL_PRINT);
        // self::SVG2PNG($svg, public_path(self::makeFilePath($path, $archivo, 'png')));

        $path = self::makeDirPath($book_id, self::BOOK_URL_PRINT);

        //$str = file_put_contents("uploads/svg_print/" . $archivo . ".svg", $svg);
        $str = file_put_contents(public_path(self::makeFilePath($path, $archivo)), $svg);

        // WEB VERSION -> IMAGE EMBEBBED AND REDUCED
        $explode = explode('xlink:href', $fullsvg);
        foreach ($explode as $key => $segmento) {
            if ($key > 0) {
                $fragmento = explode('"', $segmento, 3);
                $datatype = explode(",", $fragmento[1]);
                if (count($datatype) > 1) {
                    $base64 = self::reduceData($datatype[1], $base);
                    $datatype[1] = $base64;
                    $datatype[0] = 'data:image/png;base64';
                    $fragmento[1] = join(",", $datatype);
                    $explode[$key] = join('"', $fragmento);
                }
            }
        }
        $svg = join("xlink:href", $explode);

        $path = self::makeDirPath($book_id, self::BOOK_URL_SVG_WEB);

        //$str = file_put_contents("uploads/svg_web/" . $archivo . ".svg", $svg);
        //$str = file_put_contents(public_path(self::makeFilePath($path, $archivo)), $svg);
        self::reduceSVGSize($svg, public_path(self::makeFilePath($path, $archivo)));
        //make thumbnail
        $path = self::makeDirPath($book_id, self::BOOK_URL_SVG_WEB);
        $thumb_path = self::makeDirPath($book_id, self::BOOK_URL_THUMBNAIL);
        self::svg2png(
            public_path(self::makeFilePath($path, $archivo)),
            public_path(self::makeFilePath($thumb_path, $archivo, 'png'))
        );

        if ($status == "1") {
            $jsonBase = '{"modelo":{},"cuento":[]}';
            //$str = file_put_contents("uploads/json_master/" . $archivo . ".json", $jsonBase);
        }


        return $archivo;
    }



    public static function getLayerJSON($book_id, $character_id)
    {
        $public_path = public_path(self::getLayerJSONPath($book_id, $character_id));

        if (file_exists($public_path)) {
            return file_get_contents($public_path);
        } else {
            return  '{"modelo":{}}';
        }
    }

    public static function getLayerJSONPath($book_id, $character_id)
    {
        $path = self::makeDirPath($book_id, self::BOOK_URL_JSON);
        return self::makeFilePath($path, $character_id . "_layer", "json");
    }

    public static function existLayerJSON($book_id, $character_id)
    {
        $public_path = public_path(self::getLayerJSONPath($book_id, $character_id));
        return file_exists($public_path);
    }


    public static function makeLayerJSON($book_id, $character_id, $json_str)
    {
        $path = self::makeDirPath($book_id, self::BOOK_URL_JSON);
        $temp = public_path($path);
        if (!File::exists($temp)) {
            File::makeDirectory($temp);
        }
        $public_path = public_path(self::makeFilePath($path, $character_id . "_layer", "json"));
        file_put_contents($public_path, $json_str);
    }

    public static function makeParamJSON($book_id, $json_str)
    {
        $path = self::makeDirPath($book_id);
        $public_path = public_path(self::makeFilePath($path, self::PARAM_JSON_FILE_NAME, "json"));
        file_put_contents($public_path, $json_str);
    }



    public static function saveImageFile($file, $book_id)
    {
        $path = self::makeDirPath($book_id, self::BOOK_URL_ORIGIN);
        $public_path = public_path($path);

        if (!File::exists($public_path)) {
            File::makeDirectory($public_path);
        }

        $file_name = $file->getClientOriginalName();


        $name = pathinfo($file_name, PATHINFO_FILENAME);
        $extension = pathinfo($file_name, PATHINFO_EXTENSION);

        $file->move($public_path,  $file_name);


        if ($extension == "svg") {
            self::process($book_id, $name, $name);
            //make thumbnail using Python
            return [
                'img_url'   =>  self::makeFilePath(self::makeDirPath($book_id, self::BOOK_URL_SVG_WEB), $name),
                'origin_url' => self::makeFilePath(self::makeDirPath($book_id, self::BOOK_URL_ORIGIN), $name),
                'thumb_url' =>  self::makeFilePath(self::makeDirPath($book_id, self::BOOK_URL_THUMBNAIL), $name, 'png'),
                'file_name' =>  $file_name
            ];
        } else {
            return ['img_url' => self::makeFilePath(self::makeDirPath($book_id, self::BOOK_URL_ORIGIN), $name, $extension)];
        }
    }

    public static function sendFileToMaterlu($path, $file_name, $book_id, $type)
    {
        $materluPath = env('MATERLU_BOOK_SVGS_DIR') . $book_id . '/' . $type . '/' . $file_name;
        File::copy(public_path($path), $materluPath);
    }

    public static function makeDirForBook($book_id)
    {
        //make directory for this book
        //upload/book/11
        //upload/book/11/svg_print
        //upload/book/11/svg_web
        //upload/book/11/png_web
        $path = public_path(self::BOOK_PREFIX . $book_id);
        if (!File::exists($path)) {
            File::makeDirectory($path);
            $temp = $path;
            $path .= self::BOOK_URL_SVG_WEB;
            File::makeDirectory($path);
            $path = $temp . self::BOOK_URL_PRINT;
            File::makeDirectory($path);
            $path = $temp . self::BOOK_URL_ORIGIN;
            File::makeDirectory($path);
            $path = $temp . self::BOOK_URL_JSON;
            File::makeDirectory($path);
            $path = $temp . self::BOOK_URL_PNG_WEB;
            File::makeDirectory($path);
            $path = $temp . self::BOOK_URL_APPLY;
            File::makeDirectory($path);
            $path = $temp . self::BOOK_URL_THUMBNAIL;
            File::makeDirectory($path);
            $path = $temp . self::BOOK_URL_TEMP;
            File::makeDirectory($path);
            //copy 
        }
    }

    public static function isAdmin()
    {
        return Auth::user()->isAdmin();
    }

    public static function svg2pdf($book_id, $type)
    {
        $path = public_path(self::PYTHON_SCIPRT_PATH);
        $result = exec("python " . $path . " " . $book_id . " 2>&1");
        return $result;
    }

    public static function svg2png($input_path, $output_path)
    {
        //make thumbnail
        // $lib_path = public_path(self::PYTHON_LIB_PATH . "svg2png.py");
        // $result = exec("python " . $lib_path . " " . $input_path . " " . $output_path . " 2>&1");
        // return $result;
        $svg = file_get_contents($input_path);
        $im = new Imagick();
        $im->setBackgroundColor(new ImagickPixel('transparent'));
        $im->readImageBlob($svg);
        $im->setImageFormat("png");
        $im->writeImage($output_path);/*(or .jpg)*/
        $im->clear();
        $im->destroy();
    }

    public static function extract_layer($book_id, $character_id, $svg_path)
    {
        $lib_path = public_path(self::PYTHON_LIB_PATH . "extract_svg.py");
        $result = exec("python " . $lib_path . " " . $book_id . " " . $character_id . " " . public_path($svg_path) . " 1 2>&1");
        echo $result;
        exit();
        return $result;
    }
    public static function get_letter($str)
    {
        $len = strlen($str);
        $letters = '';
        for ($i = 0; $i < $len; $i++) {
            if ($str[$i] >= '0' && $str[$i] <= '9') {
                break;
            }
            $letters = $letters . $str[$i];
        }
        return $letters;
    }

    public static function apply_svg($book_id, $character_id, $svg_path, $json)
    {
        //make id_param.json
        //$executionStartTime = microtime(true);

        $json_path = self::makeFilePath(self::makeDirPath($book_id, self::BOOK_URL_JSON), $character_id . "_param", 'json');
        file_put_contents(public_path($json_path), $json);
        $lib_path = public_path(self::PYTHON_LIB_PATH . "apply_layer.py");
        $result = exec("python " . $lib_path . " " . $book_id . " " . $character_id . " " . public_path($svg_path) . " 2>&1");

        //won't use Python because of speed
        //will use PHP
        //open svg file


        // // //$executionEndTime = microtime(true);
        // // //$seconds = $executionEndTime - $executionStartTime;
        //return $im->getImagesBlob();
        return $result;
    }
    public static function apply_page($book_id, $file_name, $svg_path, $json)
    {
        //make id_param.json
        $json_path = self::makeFilePath(self::makeDirPath($book_id, ''), "layer", 'json');
        file_put_contents(public_path($json_path), $json);
        $lib_path = public_path(self::PYTHON_LIB_PATH . "apply_page.py");
        $result = exec("python -W ignore " . $lib_path . " " . $book_id . " " . $file_name . " " . public_path($svg_path) . " 2>&1");

        //return $result;
    }

    public static function check_page($book_id, $output_name, $svg_path)
    {
        $lib_path = public_path(self::PYTHON_LIB_PATH . "check_page.py");
        $result = exec("python -W ignore " . $lib_path . " " . $book_id . " " . public_path($svg_path) . " " . $output_name . " 2>&1");
    }
}