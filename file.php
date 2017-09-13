<?php

header('Access-Control-Allow-Origin: *');

$result['status'] = 0;

$upload_folder ='archivos';

$nombres = [];

foreach ($_FILES as $key) {

  $nombre_archivo = $key['name'];

  $tipo_archivo = $key['type'];

  $tamano_archivo = $key['size'];

  $tmp_archivo = $key['tmp_name'];

  $archivador = $upload_folder . '/' . $nombre_archivo;

  $result['nombre'] = $nombre_archivo;

  if (move_uploaded_file($tmp_archivo, $archivador)) {

    $result['status'] = 1;

  }
  array_push($nombres, $nombre_archivo);

}

$result['nombre'] = $nombres;

echo json_encode($result);
?>
