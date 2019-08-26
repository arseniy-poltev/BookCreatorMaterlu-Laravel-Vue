<?php
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', 3000);
// require_once('./PHPImageWorkshop/ImageWorkshop.php'); // Be sure of the path to the class
require_once __DIR__ . '/../vendor/PHPImageWorkshop/ImageWorkshop.php';

use PHPImageWorkshop\ImageWorkshop;

function reduceData($data, $base)
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
function getimgfile($data, $name, $ext = 'png')
{
	$decode = base64_decode($data);
	$str = file_put_contents("uploads/svg_print/" . $name . "." . $ext, $decode);
	if ($str == true) {
		return $name . "." . $ext;
	} else {
		return "FAIL";
	}
}
function procesar($input, $archivo, $base = 0.15, $status = 0)
{
	$str = file_get_contents("resources/input/" . $input . ".svg");
	$explode = explode(' id="', $str);
	foreach ($explode as $key => $segmento) {
		$fragmento = explode('"', $segmento, 2);
		$fragmento[0] = strtolower($fragmento[0]);
		$explode[$key] = join('"', $fragmento);
	}
	$svg = join(' id="', $explode);
	$explode = explode(' fill="', $svg);
	foreach ($explode as $key => $segmento) {
		$fragmento = explode('"', $segmento, 2);
		$fragmento[0] = strtolower($fragmento[0]);
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
				$imgname = getimgfile($datatype[1], $name, $extension[1]);
				$fragmento[1] = $imgname;
				$explode[$key] = join('"', $fragmento);
				$i++;
			}
		}
	}
	$svg = join("xlink:href", $explode);
	$str = file_put_contents("uploads/svg_print/" . $archivo . ".svg", $svg);

	// WEB VERSION -> IMAGE EMBEBBED AND REDUCED
	$explode = explode('xlink:href', $fullsvg);
	foreach ($explode as $key => $segmento) {
		if ($key > 0) {
			$fragmento = explode('"', $segmento, 3);
			$datatype = explode(",", $fragmento[1]);
			if (count($datatype) > 1) {
				$base64 = reduceData($datatype[1], $base);
				$datatype[1] = $base64;
				$fragmento[1] = join(",", $datatype);
				$explode[$key] = join('"', $fragmento);
			}
		}
	}
	$svg = join("xlink:href", $explode);
	$str = file_put_contents("uploads/svg_web/" . $archivo . ".svg", $svg);
	if ($status == "1") {
		$jsonBase = '{"modelo":{},"cuento":[]}';
		$str = file_put_contents("uploads/json_master/" . $archivo . ".json", $jsonBase);
	}
	return $archivo;
}

@$input = $_REQUEST['input'];
@$archivo = $_REQUEST['archivo'];
@$batch = $_REQUEST['batch'];

if ($input) {
	$proc = procesar($input, $archivo, $_REQUEST['base'], 0);
	?>
<div style="background: #CCC;margin: 10px">
    <h2><?= $input; ?> <?= $proc; ?></h2>
    <a href="resources/input/<?= $input; ?>.svg" target="_blank">ORIGINAL</a><br>
    <a href="uploads/svg_print/<?= $proc; ?>.svg" target="_blank">PRINT</a><br>
    <a href="uploads/svg_web/<?= $proc; ?>.svg" target="_blank">WEB</a><br>
    <a href="load.html?modelo=<?= $proc; ?>" target="_blank">MODELADOR</a><br>
</div>
<?php
}
if ($batch) {
	$min = @$_REQUEST['min'];
	$max = @$_REQUEST['max'] + 1;
	$pattern = @$_REQUEST['pattern'];
	for ($i = $min; $i < $max; $i++) {
		$round = $i / 2;
		$part = "_0a";
		if (is_int($round)) {
			$part = "_1b";
		}
		$pagina = $batch . "_" . ceil($round) . $part;
		$proc = procesar($pattern . $i, $pagina, $_REQUEST['base'], 0);
		?>
<div style="background: #CCC;padding:10px;margin:10px;">
    <h2><?= $i; ?> => <?= $proc; ?></h2>
    <a href="resources/input/<?= $i; ?>.svg" target="_blank">ORIGINAL</a><br>
    <a href="uploads/svg_print/<?= $proc; ?>.svg" target="_blank">PRINT</a><br>
    <a href="uploads/svg_web/<?= $proc; ?>.svg" target="_blank">WEB</a><br>
</div>
<?php
	}
}

if ($input == "" && $batch == "") {
	?>
<div style="background:#BBB;padding: 10px;margin:10px; float: left;">
    <h1 style="margin: 5px;">SINGLE</h1>
    <form method="POST">
        <input type="text" name="input" placeholder="input" required>
        <input type="text" name="archivo" placeholder="output">
        <select name="status">
            <option value="1">New</option>
            <option value="0">Update</option>
        </select>
        <input type="number" name="base" min="0.01" max="1" step="0.01" value="0.15" placeholder="% Reducción" required>
        <input type="submit">
    </form>
</div>
<div style="background:#BBB;padding: 10px;margin:10px; float: left;">
    <h1 style="margin: 5px;">BATCH</h1>
    <form method="POST">
        <input type="text" name="batch" placeholder="ID Story" required>
        <input type="text" name="pattern" placeholder="Pattern">
        <input type="text" name="min" placeholder="First File" value="1" required>
        <input type="text" name="max" placeholder="Last File" required>
        <input type="number" name="base" min="0.01" max="1" step="0.01" value="0.15" placeholder="% Reducción" required>
        <input type="submit">
    </form>
</div>
<?php
	exit();
}
?>
<a href="procesar.php" target="_self">NEW</a><br>