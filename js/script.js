/* Author: 

*/

var map;

$.expr[':'].external = function(obj){
    return !obj.href.match(/^mailto\:/)
            && (obj.hostname != location.hostname);
};


$(function(){
	
	var loader = "<img id='loader' src='img/ajax-loader.gif' alt='loading...'/>";
	
	$('a:external').each(function(){
		$(this).click(function(){
			//_gaq.push(['_trackEvent', 'External Links', 'Clicked', $(this).text()]);
			//return false;
		});
	});
	
	$("#radio").buttonset();
	
	//$(".tooltip").tooltip();
	
	$("#levels").bind('change keyup click', function(){
	    var res = Math.pow(2,$(this).val()-1) * 256;
		$("#zoomInfo").text('Image Dimensions need to be: ' + res + 'x' + res);
		$("#dimension").val(res);
	});
	
	
	$('#submit').button({

        icons: {
            primary: "ui-icon-clipboard"
        }
    });
	
	$("#levels").trigger('change');
	
	$("#resetBtn").live("click", function(){
		//reset form
			$("#firstStep").slideDown('slow');
			$("#ajaxContent").html('');
			$(".qq-upload-list").html('');
			$("#mapname").val('');
			
			
			var moonTypeOptions = {
			   getTileUrl: function(coord, zoom) {
			       var bound = Math.pow(2, zoom);
			       return 'http://unitseven.com.au/maptiles/Lara/tile_' + zoom + '_' + coord.x + '-' + coord.y + '.png?v=2';
			   },
			   tileSize: new google.maps.Size(256, 256),
			   isPng: true,
			   maxZoom: 4,
			   minZoom: 0,
			   name: "Lara"
			 };

			 var moonMapType = new google.maps.ImageMapType(moonTypeOptions);


			function re_initialize() {
				map = null;
				$("#map_canvas").remove();
				$("#mapContainer").html('<div id="map_canvas"></div>');
			    var myLatlng = new google.maps.LatLng(0, 0);
			    var myOptions = {
			      zoom: 1,
			      center: myLatlng,
				mapTypeControlOptions: {
				        mapTypeIds: ["sam"]
				      }
			    }
			    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

				map.mapTypes.set('sam', moonMapType);
			    map.setMapTypeId('sam');
			  }
			
			re_initialize();
			$("#preparedEarlier").text("Here's one I prepared earlier...");
		
	});
	
	$("#form").validate({rules: {
	    levels: {
	      required: true,
	      min: 1,
		max:5
	    }
	  }});
	
		var uploader = new qq.FileUploader({
          element: document.getElementById('file-uploader'),
          action: 'php.php',
          debug: false,
          allowedExtensions: ['png'],		
		onSubmit: function(){
			if($("#mapname").val() == ""){
			 smoke.signal("Please enter a name for this map");
			$("#mapname").focus();
				return false;
			}else{
			uploader.setParams({
			   name: $("#mapname").val(),
			   levels: $("#levels").val(),
			   dimension: $("#dimension").val(),
			});
			return true;
		}
		},
		onComplete: function(id, fileName, responseJSON){

						//show
						log(id);
						log(fileName);
						log(responseJSON);

						if(responseJSON.success == true){
							
						$("#firstStep").slideUp('slow');
						$("#ajaxContent").html('<div class="thumbnail" id="'+responseJSON.id+'"><div class="image shadow"><img height="150px" src="http://unitseven.com.au/maptiles/uploads/'+responseJSON.filename+'"/></div></div>');
						
						$("#"+responseJSON.id).after(loader);
						$("#loader").after("<br/><span id='loadingText'>Generating tiles</span>");
						
					
						
					var dots = setInterval("$('#loadingText').append('.');",1000)
						
						$.post("ajaxTiles.php", { "levels": $("#levels").val(), "mapname": $("#mapname").val(), "filename": "./uploads/"+responseJSON.filename },
						 function(data){
							$("#loader").remove();
							$("#loadingText").remove();
							$("#ajaxContent").append(data.html);
							$("#ajaxContent").before(data.js);
								
							clearInterval(dots);
														
						/*	var brush = new SyntaxHighlighter.brushes.JScript();
						            brush.init({ toolbar: true });
						            var html = brush.getHtml(data.code);
						            $('#source').html(html);
						*/
						
						$("#resetBtn").button(			{
									            icons: {
									                primary: "ui-icon-refresh"
									            }});
							
							initNewMap();
							
							$("#preparedEarlier").text("Here's your custom Map...");
						 },"json");
						}else{
							//smoke.signal(responseJSON.error);
							console.log(responseJSON.error);
						}
					
					},
      });
	initialize();
	//initNewMap();
});
/*
var newMapTypeOptions = { 
	getTileUrl: function(coord, zoom) { 
		var bound = Math.pow(2, zoom); 
		return 'http://unitseven.com.au/maptiles/sam/tile_' + zoom + '_' + coord.x + '-' + coord.y + '.png?v=2'; 
		}, 
		tileSize: new google.maps.Size(256, 256), 
		isPng: true, 
		maxZoom: 3, 
		minZoom: 0, 
		name: "sam" 
}; 

var newMapType = new google.maps.ImageMapType(newMapTypeOptions); 
function initNewMap() { 
	var myLatlng = new google.maps.LatLng(0, 0); 
	var myOptions = { zoom: 1, center: myLatlng, mapTypeControlOptions: { mapTypeIds: ["sam"] } } 
	map = new google.maps.Map(document.getElementById("map_canvas"), myOptions); 
	map.mapTypes.set('sam', newMapType); 
	map.setMapTypeId('sam'); 
	}
*/

var moonTypeOptions = {
   getTileUrl: function(coord, zoom) {
       var bound = Math.pow(2, zoom);
       return 'http://unitseven.com.au/maptiles/Lara/tile_' + zoom + '_' + coord.x + '-' + coord.y + '.png?v=2';
   },
   tileSize: new google.maps.Size(256, 256),
   isPng: true,
   maxZoom: 4,
   minZoom: 0,
   name: "Lara"
 };

 var moonMapType = new google.maps.ImageMapType(moonTypeOptions);


function initialize() {
    var myLatlng = new google.maps.LatLng(0, 0);
    var myOptions = {
      zoom: 1,
      center: myLatlng,
	mapTypeControlOptions: {
	        mapTypeIds: ["sam"]
	      }
    }
    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

	map.mapTypes.set('sam', moonMapType);
    map.setMapTypeId('sam');
  }
















