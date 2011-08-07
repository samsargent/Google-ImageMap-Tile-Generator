<?php

error_reporting(E_ALL);

define('TILE_SIZE', 256);
define('TILE_FILENAME', "tile_%s_%s-%s.png");

$mapName = $_POST['mapname'];
define('OUTPUT_DIR', $mapName);

function SplitTilesRecursive($image, $level){
	//echo "<br/>SplitTilesRecursive - level: " .$level;
	$mapWidth = GetMapWidth($level);
	
	//echo "<br/>MapWidth:". $mapWidth;
	
	$tilesOnSide = $mapWidth / TILE_SIZE;
	//echo "<br/>tiles on side:". $tilesOnSide."<br/>";
	
	
	$resized = ResizeImage($image, $mapWidth);
	imagedestroy($image);
	
	for ($x = 0; $x < $tilesOnSide; $x++)
        for ($y = 0; $y < $tilesOnSide; $y++)
            CropAndSaveTile($resized, $x, $y, $level);

	 if ($level > 0)
	     SplitTilesRecursive($resized, $level - 1);
}


function GetMapWidth($level){
	return TILE_SIZE * (int)pow(2,$level);
}

 function CropAndSaveTile($image, $x, $y, $level)
    {
	 //echo "<br/>level " . $level;
     $dst_image = imagecreatetruecolor(TILE_SIZE, TILE_SIZE);
    
	imagecopyresampled($dst_image, $image, 0, 0, $x * TILE_SIZE, $y * TILE_SIZE, TILE_SIZE, TILE_SIZE, TILE_SIZE, TILE_SIZE);

			$filename = sprintf(TILE_FILENAME, $level, $x, $y);

            // the Portable Network Graphics (PNG) encoder is used implicitly
			imagepng($dst_image, OUTPUT_DIR."/".$filename); 
			imagedestroy($dst_image);
            //echo "<br/>Processed " . $filename;
            //echo  "<br/><img src='".OUTPUT_DIR."/".$filename."'/>";
    }

    function ResizeImage($toResize, $size)
    {
        $dst_image = imagecreatetruecolor($size, $size);

		imagecopyresampled($dst_image, $toResize, 0, 0, 0, 0, $size, $size, imagesx($toResize), imagesy($toResize));

        return $dst_image;
    }


if(empty($_POST['levels']) || $_POST['levels'] < 1 || $_POST['levels'] > 5 || empty($_FILES))
{
	echo "Invalid Input";
}else{

$output = "tile generator";

$levels = $_POST['levels'];


$output .=  "<br/>level: " . $levels;

//echo  "<img src='".$_POST['filename']."'/>";
if(!file_exists('./'.OUTPUT_DIR))
	mkdir('./'.OUTPUT_DIR);
	else
	{
		if ($handle = opendir('./'.OUTPUT_DIR)) {
		    while (false !== ($file = readdir($handle))) {
		        if ($file != "." && $file != ".." && $file != ".DS_STORE") {
		            unlink('./'.OUTPUT_DIR.'/'.$file);
		        }
		    }
		    closedir($handle);
		}
	}


$original = imagecreatefrompng($_FILES['filename']['tmp_name']);

SplitTilesRecursive($original, $levels);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Maps API Demos</title>
</head>

<body>
	<?php $output; ?>
    <div id="croft-map" style="width:100%;height:600px;margin:10px auto;border:2px solid #000;"></div>

    <script type="text/javascript" src="http://maps.google.com/maps/api/js?libraries=geometry&sensor=false"></script>
    <script type="text/javascript">
    /* <![CDATA[ */
        // Google Maps Demo
        //////////////////////////////////
        var Demo = Demo || {};
        Demo.ImagesBaseUrl = 'http://localhost/maptiles/';

        // CroftMap class
        //////////////////////////////////
        Demo.CroftMap = function (container) {
            // Create map
            this._map = new google.maps.Map(container, {
                zoom: 0,
                center: new google.maps.LatLng(0, -20),
                mapTypeControl: false
            });

            // Set custom tiles
            this._map.mapTypes.set('<?php echo $mapName; ?>', new Demo.ImgMapType('<?php echo $mapName; ?>', '#4E4E4E'));
            this._map.setMapTypeId('<?php echo $mapName; ?>');
        };

      

        // ImgMapType class
        //////////////////////////////////
        Demo.ImgMapType = function (theme, backgroundColor) {
            this.name = this._theme = theme;
            this._backgroundColor = backgroundColor;
        };

        Demo.ImgMapType.prototype.tileSize = new google.maps.Size(256, 256);
        Demo.ImgMapType.prototype.minZoom = 0;
        Demo.ImgMapType.prototype.maxZoom = <?php echo $_POST['levels']; ?>;

        Demo.ImgMapType.prototype.getTile = function (coord, zoom, ownerDocument) {
            var tilesCount = Math.pow(2, zoom);

            if (coord.x >= tilesCount || coord.x < 0 || coord.y >= tilesCount || coord.y < 0) {
                var div = ownerDocument.createElement('div');
                div.style.width = this.tileSize.width + 'px';
                div.style.height = this.tileSize.height + 'px';
                div.style.backgroundColor = this._backgroundColor;
                return div;
            }

            var img = ownerDocument.createElement('IMG');
            img.width = this.tileSize.width;
            img.height = this.tileSize.height;
var randomnumber=Math.floor(Math.random()*11);

            img.src = Demo.Utils.GetImageUrl(this._theme + '/tile_' + zoom + '_' + coord.x + '-' + coord.y + '.png?v='+randomnumber);

            return img;
        };

        // Other
        //////////////////////////////////
        Demo.Utils = Demo.Utils || {};

        Demo.Utils.GetImageUrl = function (image) {
            return Demo.ImagesBaseUrl + image;
        };

        Demo.Utils.SetOpacity = function (obj, opacity /* 0 to 100 */ ) {
            obj.style.opacity = opacity / 100;
            obj.style.filter = 'alpha(opacity=' + opacity + ')';
        };

        // Map creation
        //////////////////////////////////
        google.maps.event.addDomListener(window, 'load', function () {
            var croftMap = new Demo.CroftMap(document.getElementById('croft-map'));
        });
    /* ]]> */
    </script>
</body>
</html>
<?php } ?>