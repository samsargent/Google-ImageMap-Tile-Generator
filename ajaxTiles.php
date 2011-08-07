<?php

ini_set("memory_limit","128M");

error_reporting(E_ALL);

define('TILE_SIZE', 256);
define('TILE_FILENAME', "tile_%s_%s-%s.png");

$mapName = $_POST['mapname'];
define('OUTPUT_DIR', "uploads/".$mapName);
$levels = $_POST['levels'] - 1;

$tileCount = 0;


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
	global $tileCount;
	 //echo "<br/>level " . $level;
     $dst_image = imagecreatetruecolor(TILE_SIZE, TILE_SIZE);
    
	imagecopyresampled($dst_image, $image, 0, 0, $x * TILE_SIZE, $y * TILE_SIZE, TILE_SIZE, TILE_SIZE, TILE_SIZE, TILE_SIZE);

			$filename = sprintf(TILE_FILENAME, $level, $x, $y);

            // the Portable Network Graphics (PNG) encoder is used implicitly
			imagepng($dst_image, OUTPUT_DIR."/".$filename); 
			imagedestroy($dst_image);
			$tileCount++;
            //echo "<br/>Processed " . $filename;
            //echo  "<br/><img src='".OUTPUT_DIR."/".$filename."'/>";
    }

    function ResizeImage($toResize, $size)
    {
        $dst_image = imagecreatetruecolor($size, $size);

		imagecopyresampled($dst_image, $toResize, 0, 0, 0, 0, $size, $size, imagesx($toResize), imagesy($toResize));

        return $dst_image;
    }


if(empty($_POST['levels']) || $_POST['levels'] < 1 || $_POST['levels'] > 5 || empty($_POST['filename']))
{
	echo "Invalid Input";
}else{

$output = "tile generator";




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


$original = imagecreatefrompng($_POST['filename']);

SplitTilesRecursive($original, $levels);
/*
 var myLatlng = new google.maps.LatLng(0, 0);
    

*/





ob_start();
?>
<script>
var newMapTypeOptions = {
   getTileUrl: function(coord, zoom) {
			   var normalizedCoord = getNormalizedCoord(coord, zoom);
		        if (!normalizedCoord) {
		          return null;
		        }
		        var bound = Math.pow(2, zoom);
		        return "http://unitseven.com.au/maptiles/uploads/<?php echo $mapName;?>/tile_" + zoom + "_" + normalizedCoord.x + "-" + normalizedCoord.y + ".png";
   },
   tileSize: new google.maps.Size(256, 256),
   isPng: true,
   maxZoom: <?php echo $levels;?>,
   minZoom: 0,
   name: "<?php echo $mapName;?>"
 };

 var newMapType = new google.maps.ImageMapType(newMapTypeOptions);

		// Normalizes the coords that tiles repeat across the x axis (horizontally)
		  // like the standard Google map tiles.
		  function getNormalizedCoord(coord, zoom) {
		    var y = coord.y;
		    var x = coord.x;

		    // tile range in one direction range is dependent on zoom level
		    // 0 = 1 tile, 1 = 2 tiles, 2 = 4 tiles, 3 = 8 tiles, etc
		    var tileRange = 1 << zoom;

		    // don't repeat across y-axis (vertically)
		    if (y < 0 || y >= tileRange) {
		      return null;
		    }

		    // repeat across x-axis
		    if (x < 0 || x >= tileRange) {
		      x = (x % tileRange + tileRange) % tileRange;
		    }

		    return {
		      x: x,
		      y: y
		    };
		  }


function initNewMap() {
	map = null;
	$("#map_canvas").remove();
	$("#mapContainer").html('<div id="map_canvas"></div>');
	var myLatlng = new google.maps.LatLng(0, 0);
    var myOptions = {
      zoom: 1,
      center: myLatlng,
	mapTypeControlOptions: {
	        mapTypeIds: ["<?php echo $mapName;?>"]
	      }
    }
    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

	map.mapTypes.set('<?php echo $mapName;?>', newMapType);
    map.setMapTypeId('<?php echo $mapName;?>');
  }
</script>
<?php
$js = ob_get_clean();

ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1.0,user-scalable=no" />
    <meta charset="utf-8" />
    <title>Google Maps - Custom ImageMap Type Demo</title>
</head>

<body onload="initialize()">
    <div id="map_canvas" style="width:600px;height:360px;"></div>
	<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
    <script type="text/javascript">
 		var customMapTypeOptions = {
		   getTileUrl: function(coord, zoom) {
				   var normalizedCoord = getNormalizedCoord(coord, zoom);
			        if (!normalizedCoord) {
			          return null;
			        }
			        var bound = Math.pow(2, zoom);
					/*Edit this URL to where you upload your tiles...*/
			        return "http://unitseven.com.au/maptiles/uploads/<?php echo $mapName;?>/tile_" + zoom + "_" + normalizedCoord.x + "-" + normalizedCoord.y + ".png";
			
		   },
		   tileSize: new google.maps.Size(256, 256),
		   isPng: true,
		   maxZoom: <?php echo $levels;?>,
		   minZoom: 0,
		   name: "<?php echo $mapName;?>"
		 };

		 var customMapType = new google.maps.ImageMapType(customMapTypeOptions);

			// Normalizes the coords that tiles repeat across the x axis (horizontally)
			  // like the standard Google map tiles.
			  function getNormalizedCoord(coord, zoom) {
			    var y = coord.y;
			    var x = coord.x;

			    // tile range in one direction range is dependent on zoom level
			    // 0 = 1 tile, 1 = 2 tiles, 2 = 4 tiles, 3 = 8 tiles, etc
			    var tileRange = 1 << zoom;

			    // don't repeat across y-axis (vertically)
			    if (y < 0 || y >= tileRange) {
			      return null;
			    }

			    // repeat across x-axis
			    if (x < 0 || x >= tileRange) {
			      x = (x % tileRange + tileRange) % tileRange;
			    }

			    return {
			      x: x,
			      y: y
			    };
			  }


		function initialize() {
		    var myLatlng = new google.maps.LatLng(0, 0);
		    var myOptions = {
		      zoom: 1,
		      center: myLatlng,
			mapTypeControlOptions: {
			        mapTypeIds: ["<?php echo $mapName;?>"]
			      }
		    }
		    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

			map.mapTypes.set('<?php echo $mapName;?>', customMapType);
		    map.setMapTypeId('<?php echo $mapName;?>');
		  }
	</script>
</body>
</html>
<?php
$code = ob_get_clean();

$fp = fopen(OUTPUT_DIR.'/index.html', 'w+');
fwrite($fp, $code);
fclose($fp);

$path="uploads/".$mapName;
$zip = new ZipArchive;
$zip->open("uploads/".$mapName.'-files.zip', ZipArchive::CREATE);
if (false !== ($dir = opendir($path)))
     {
         while (false !== ($file = readdir($dir)))
         {
             if ($file != '.' && $file != '..')
             {
                       $zip->addFile($path.DIRECTORY_SEPARATOR.$file);
             }
         }
     }
     else
     {
         die('Can\'t read dir');
     }
$zip->close();

ob_start();
?>
<div id="newMap-id" stlye="width:100%; height:360px;"></div>
<h1>Done!</h1>
<h3>Your Map Tiles &amp; Google Maps HTML &amp; JS Code have been packaged into a zip archive for you to download and install on your own server. Download &amp; follow the instructions in the comments of the code to start using your Custom ImageMap Google Map.</h3>
<p><a href="./uploads/<?php echo $mapName.'-files.zip' ?>" class="downloadLink"><img src="img/zip-icon.png" alt="Zip Icon"> Download Files</a> (<?php echo $tileCount; ?> png tiles &amp; 1 html file). </p>
<p class="info">Files are removed every night at midnight. Be sure to download before then. You have: 
<?php
$h = 24 - date("H")/1; 
$m = 60 - date("i")/1; 
$s = 60 - date("s")/1; 
$h = (($h <10)?"0":"").$h; 
$m = (($m <10)?"0":"").$m; 
$s = (($s <10)?"0":"").$s; 
echo "$h:$m:$s";
?>
</p>
<p>View your map in a <a href="./uploads/<?php echo $mapName;?>/index.html">new window</a></p>
<button id="resetBtn">Reset &amp; Start Again</button>
<br/><br/>
<!--
<h2>Instructions for use</h2>
<p>Blah blah blah, figure it out for yourself...</p>
<div id="source"></div>
-->
<?php
$html = ob_get_clean();

$result = array('success'=>true, 'code'=> $code, 'html'=> $html, 'js'=> $js, 'id'=> "Some sort of session id...");

echo json_encode($result);

?>
<?php } ?>