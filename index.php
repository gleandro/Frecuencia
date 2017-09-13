<!DOCTYPE html>
<html>
<head>
<style>

#map {
  height: 80%;
}

html, body {
  height: 100%;
  margin: 0;
  padding: 0;
}
.row{
  width: 100%;
}

</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
<script type="text/javascript">

$(document).ready(function() {
  var first_time = 0;
  var map;
  var heatmap;
  var positions = [];
  var center = [];
  var array_frecuencia = [];
  var markers = [];
  var zoom_default = 19;
  var radius_refault = 50;
  var bl_form = 0;

  function initMap() {
    if (first_time != 0) {

    }
    else{

      map = new google.maps.Map(document.getElementById('map'), {
        zoom: zoom_default,
        center: new google.maps.LatLng(center[0]['Latitude'],center[0]['Longitude']),
        mapTypeId: 'terrain'
      });
    }
    llamar();

    map.addListener('zoom_changed', function() {
      var zoom = map.getZoom();
      var radio = 0;
      if (zoom == zoom_default) {
        heatmap.set('radius', radius_refault);
      }
      if (zoom > zoom_default) {
        radio = radius_refault+(zoom-zoom_default)*100;
        heatmap.set('radius', radio);
      }
    });

  }

  $("#change_form").change(function(event) {
    var HTML = "";
    $("#tmp_form").html("");
    if ($(this)[0].checked == true) {
      HTML += '<label for="archivo">Selecciona 1 archivo con extension .mdr</label>';
      HTML += '<input type="file" class="form-control-file" id="archivo" name="archivo" required="required" />'
      HTML += '<input type="text" name="" value="1" hidden="" id="bl_form">';
      bl_form = 1;
    }
    else {
      HTML += '<label for="archivo">Selecciona 1 o varios archivos con extension .mdr</label>';
      HTML += '<input type="file" class="form-control-file" id="archivo" name="archivo" required="required" multiple="" />';
      HTML += '<input type="text" name="" value="0" hidden="" id="bl_form">';
      bl_form = 0;
    }
    HTML += '<input type="submit" class="btn btn-outline-primary" value="Subir" />';

    $("#tmp_form").append(HTML);
  });

  function llamar() {
    var heatmapData = [];
    var markData = [];
    var tmp_frecuencias = array_frecuencia[0];
    var archivo = 1;
    var weight = 0;
    $.each(positions,function(index, pos) {
      weight = get_porcent(tmp_frecuencias[archivo][0]);
      heatmapData.push({location: new google.maps.LatLng(pos['Latitude'],pos['Longitude']), weight, frec : tmp_frecuencias[archivo][0]});
      if (bl_form == 0) {
        archivo++;
      }
    });
    if (first_time != 0) {
      heatmap.setData(heatmapData);
      clearMarkers();
      markers = [];
    }
    else {
      heatmap = new google.maps.visualization.HeatmapLayer({
        data: heatmapData,
        map: map,
        radius: radius_refault
      });
      first_time =1 ;
    }
    for (var i = 0; i < heatmapData.length; i++) {
      var marker = new google.maps.Marker({
        position: heatmapData[i]['location'],
        animation: google.maps.Animation.BOUNCE,
        map:map,
        title: String(heatmapData[i]['frec'])
      });
      markers.push(marker);
    }
  }

  function setMapOnAll(map) {
    for (var i = 0; i < markers.length; i++) {
      markers[i].setMap(map);
    }
  }

  function clearMarkers() {
    setMapOnAll(null);
  }

  function convertGRAD_TO_DEC(dato){
    var grados = parseInt(dato.substring(1,3));
    var minutos = parseInt(dato.substring(3,5));
    minutos = minutos/60;
    var segundos = parseInt(dato.substring(6));
    segundos = segundos/3600;
    var dec =grados+minutos+segundos;
    return "-"+dec;
  }

  function get_porcent(dato){
    if (dato.substring(0,1) == "-") {
      dato = parseFloat(dato.substring(1));
    }
    else if (dato.substring(1,2) == "-") {
      dato = parseFloat(dato.substring(2));
    }
    var new_dato = 1-(dato/150);
    return new_dato.toFixed(2);
  }

  function buscar_frecuencia(){

    clearMarkers();
    markers = [];

    var frec = $("#select").children("option:selected").val();
    var sweep = $("#sweep").children("option:selected").val();
    heatmap.setMap(null);
    var tmp_frecuencias = array_frecuencia[frec];
    var heatmapData = [];
    var archivo = 1;
    var weight = 0;
    $.each(positions,function(index, pos) {
      if (bl_form == 1) {
        sweep = pos['sweep_tmp'];
      }
      weight = get_porcent(tmp_frecuencias[archivo][sweep]);
      heatmapData.push({location: new google.maps.LatLng(pos['Latitude'],pos['Longitude']), weight, frec: tmp_frecuencias[archivo][sweep]});
      if (bl_form == 0) {
        archivo++;
      }
    });
    heatmap = new google.maps.visualization.HeatmapLayer({
      data: heatmapData,
      map: map,
      radius: radius_refault
    });

    for (var i = 0; i < heatmapData.length; i++) {
      var marker = new google.maps.Marker({
        position: heatmapData[i]['location'],
        animation: google.maps.Animation.BOUNCE,
        map:map,
        title: String(heatmapData[i]['frec'])
      });
      markers.push(marker);
    }

  }

  $("#select").change(function(event) {
    buscar_frecuencia();
  });
  $("#sweep").change(function(event) {
    buscar_frecuencia();
  });

  $("#form_1").submit(function(event) {
    event.preventDefault();

    if (bl_form == 1) {
      $("#sweep").hide();
    }
    else {
      $("#sweep").show();
    }

    var archivo = $("#archivo")[0].files;
    var data = new FormData();
    for (var i = 0; i < archivo.length; i++) {
      data.append('archivo'+i,archivo[i]);
    }

    $.ajax({
      url: 'http://192.168.0.24/file.php',
      type: 'post',
      contentType:false,
      processData:false,
      cache:false,
      dataType: 'json',
      data: data,
      success: function(result){
        if (result.status ==1) {
          positions = [];
          $("#cuerpo").html("");
          $("#tabla").html("");
          $("#select").html("");
          $("#sweep").html("");
          var TABLE = "<table class='table-hover table-inverse'><tr>";
          var HTML = "";
          var sweep = 0;
          var name_count = result.nombre.length;
          var index = 0;
          var total_sweep=0;
          var archivo_tmp = 1;
          array_frecuencia = [];
          for (var j = 0; j < name_count; j++) {
            $.get("archivos/"+result.nombre[j], function (mdr) {
              var sweep_tmp = 0;
              index++;
              $(mdr).find("Sweep").each(function () {
                var count_sweep = $(this).parent().children('sweep').length;
                var fec_start = $(this).find('StartDate').text();
                var fec_end = $(this).find('StopDate').text();
                var geodata = $(this).find('GeoData');
                var GPS_Time = geodata.find('GPS_Time').text();
                var Latitude = convertGRAD_TO_DEC(geodata.find('Latitude').text());
                var Longitude = convertGRAD_TO_DEC(geodata.find('Longitude').text());
                if (bl_form == 0) {
                  if (sweep_tmp == 0) {
                    var bl_pos = false;
                    $.each(positions,function(index, pos) {
                      if (Latitude == pos['Latitude'] && Longitude == pos['Longitude']) {
                        bl_pos = true;
                      }
                    });
                    if (bl_pos == false) {
                      positions.push({Latitude,Longitude});
                    }
                  }
                }
                else {
                  var bl_pos = false;
                  $.each(positions,function(index, pos) {
                    if (Latitude == pos['Latitude'] && Longitude == pos['Longitude']) {
                      bl_pos = true;
                    }
                  });
                  if (bl_pos == false) {
                    positions.push({Latitude,Longitude,sweep_tmp});
                  }
                }
                var Elevation = geodata.find('Elevation').text();
                var Speed = geodata.find('Speed').text();
                var Tilt_XYZ = geodata.find('Tilt_XYZ').text();
                var Compass_XYZ = geodata.find('Compass_XYZ').text();
                var Gyro_XYZ = geodata.find('Gyro_XYZ').text();
                HTML += "Sweep "+sweep+"<br>";
                HTML += "StartDate -> "+fec_start+"<br>";
                HTML += "StopDate -> "+fec_end+"<br>";
                HTML += "GeoData :"+"<br>";
                HTML += " --> GPS_Time : "+GPS_Time+"<br>";
                HTML += " --> Latitude : "+Latitude+"<br>";
                HTML += " --> Longitude : "+Longitude+"<br>";
                // HTML += " --> Elevation : "+Elevation+"<br>";
                // HTML += " --> Speed : "+Speed+"<br>";
                // HTML += " --> Tilt_XYZ : "+Tilt_XYZ+"<br>";
                // HTML += " --> Compass_XYZ : "+Compass_XYZ+"<br>";
                // HTML += " --> Gyro_XYZ : "+Gyro_XYZ+"<br>";
                HTML += "<br>";
                if (sweep == 0) {
                  center.push({Latitude,Longitude});
                  var frecuencia = $(this).find('Frequencies');
                  var cantidad = frecuencia.attr('count');
                  frecuencias= frecuencia.text().split(";");
                  TABLE += "<td>SWEEP/FRECUENCIA</td>";
                  for (var i = 0; i < cantidad; i++) {
                    var tmp_frec = {frecuencias : frecuencias[i]};
                    array_frecuencia.push(tmp_frec);
                    TABLE += "<td>"+  frecuencias[i];+"</td>";
                    $("#select").append('<option value="'+i+'">'+frecuencias[i]+'</option>');
                  }
                  TABLE += "</tr>";
                }
                if (sweep_tmp == 0) {
                  TABLE += "<tr><td>SWEEP</td></tr>";
                }
                TABLE += "<tr>";
                TABLE += "<td>sweep "+sweep+"</td>";
                var valor = $(this).find('Values');
                var cantidad = valor.attr('count');
                var valores= valor.text().split(";");
                for (var i = 0; i < cantidad; i++) {
                  TABLE += "<td>"+  valores[i];+"</td>";
                  if (sweep_tmp==0) {
                    array_frecuencia[i][archivo_tmp] = [];
                    array_frecuencia[i][archivo_tmp].push(valores[i]);
                  }else {
                    array_frecuencia[i][archivo_tmp].push(valores[i]);
                  }
                }
                TABLE += "</tr>";
                sweep++;
                sweep_tmp++;
                if (index == name_count && count_sweep == sweep_tmp) {
                  TABLE += "</table>";
                  $("#cuerpo").append(HTML);
                  $("#tabla").append(TABLE);
                  initMap();
                }
              });//end for
              archivo_tmp++;
              if (total_sweep==0) {
                total_sweep=sweep_tmp;
              }
              if(total_sweep>sweep_tmp) {
                total_sweep=sweep_tmp;
              }
              if(index==name_count) {
                for (var i = 0; i < total_sweep; i++) {
                  $("#sweep").append('<option value="'+i+'">sweep'+i+'</option>');
                }
              }
            });
          }
          console.log(array_frecuencia);
          console.log(positions);
        }
      }
    });

  });
});
</script>
</head>
<body>
  <div class="form-check">
    <label class="form-check-label">
      <input class="form-check-input" type="checkbox" value="" id="change_form" >
      Individual
    </label>
  </div>
  <form method="post" enctype="multipart/form-data" id="form_1">
    <div class="form-group" id="tmp_form">
      <label for="archivo">Selecciona 1 o varios archivos con extension .mdr</label>
      <input type="text" name="" value="0" hidden="" id="bl_form">
      <input type="file" class="form-control-file" id="archivo" name="archivo" required="required" multiple="" />
      <input type="submit" class="btn btn-outline-primary" value="Subir" />
    </div>
  </form>

  <div class="row">
    <div class="col-md-6">
      <select class="form-control form-control-sm" name="" id="select"></select>
    </div>
    <div class="col-md-6">
      <select class="form-control form-control-sm" name="" id="sweep"></select>
    </div>
  </div>


  <div id="map"></div>
  <div class="" id="cuerpo"></div>
  <div class="row" style="margin-right:0;margin-left:0">
    <div class="" id="tabla">

    </div>
  </div>


  <script async defer
  src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC4Z4AblCiMBbfdt0FbEfgSRyRXZXVI0OU&libraries=visualization">
  </script>
</body>
</html>
