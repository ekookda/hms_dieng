<?php
/*----------------------------------------------|
|                                               |
|                                               |
|-------------------MS ACCESS-------------------|
|                                               |
|                                               |
|----------------------------------------------*/

// cek file database access
//$file_db = $_SERVER['DOCUMENT_ROOT'] . "/ic2000.mdb";
$file_db = "ic2000.mdb";

//$file_db = "C:/Users/klikota/AppData/Local/VirtualStore/Program Files (x86)/RF system/RF system/ic2000.mdb";
echo $file_db . "<br><br>";
if (!file_exists($file_db)) {
  die("Could not find database");
}

// koneksi ke ms-access
$connection = odbc_connect("Driver={Microsoft Access Driver (*.mdb)};Dbq=$file_db", '', '');

if (!$connection) {
  die("ODBC can't connect");
}

// $kosongkan   = "TRUNCATE TABLE room";
$kosongkan_room   = "DELETE FROM room;";
$empty_table = odbc_exec($connection, $kosongkan_room);
$kosongkan_room_type   = "DELETE FROM room_type;";
$empty_table = odbc_exec($connection, $kosongkan_room_type);

/*----------------------------------------------|
|                                               |
|                                               |
|-------------------   JSON  -------------------|
|                                               |
|                                               |
|----------------------------------------------*/

// ambil data dari server cloud table room
$url  = "http://localhost/hms/hotel_api/api.php/rooms";
//$url  = "http://hms.virtunesia.com/hotel_api/api.php/rooms";
$data = file_get_contents($url);

// ambil data room dari mysql menggunakan json
$rooms = json_decode($data, TRUE);
// var_dump($rooms);

foreach ($rooms as $room) {

  $count = count($room['records']); // cek jumlah array records
  
  for ($i=0; $i<$count; $i++) {
	
    $id					= $room['records'][$i][0];
    $id_parent			= $room['records'][$i][1];
	$name				= $room['records'][$i][2];
	$price				= $room['records'][$i][3];
	$status				= $room['records'][$i][4];
	$kondisi_kamar		= $room['records'][$i][5];
	$facility			= $room['records'][$i][6];
	$overtime_cost		= $room['records'][$i][7];
	
	if(!isset($id)) $room_id="NULL";
	if(!isset($id_parent)) $id_parent="NULL";
	if(!isset($name)) $name="NULL"; else $name="'$name'";
	if(!isset($price)) $price="NULL";
	if(!isset($status)) $status="NULL";
	if(!isset($kondisi_kamar)) $kondisi_kamar="NULL";
	if(!isset($facility)) $facility="NULL"; else $facility="'$facility'";
	if(!isset($overtime_cost)) $overtime_cost="NULL";
  
	if ($id_parent != "NULL") {
		
		$sql_odbc = "INSERT INTO room(
			room_id, 
			building_id, 
			building_name, 
			floor, 
			room_no_id, 
			room_no, 
			card_num, 
			max_num, 
			room_type_code, 
			room_status_code, 
			remark
		) 
		VALUES (
			$id, 
			1, 
			'Hotel Dieng', 
			1, 
			$id, 
			$name, 
			0, 
			0, 
			$id_parent, 
			$status, 
			''
		);";
	}
	else {
	
		$sql_odbc = "INSERT INTO room_type(
			id, 
			type, 
			code, 
			descript, 
			day_price, 
			hour_price, 
			furnish
		) 
		VALUES (
			$id, 
			4, 
			$id, 
			$name, 
			$price, 
			$overtime_cost, 
			$facility
		);";
	}
	 
	echo $sql_odbc . "<br><br>";

	$input_data = odbc_exec($connection, $sql_odbc);

	unset($room_id); 
	unset($building_id); 
	unset($building_name); 
	unset($floor); 
	unset($room_no_id); 
	unset($room_no); 
	unset($card_num); 
	unset($max_num); 
	unset($room_type_code); 
	unset($room_status_code); 
	unset($remark); 

  }

}


?>
