<?php

//$url  = "http://localhost/hotel_api/api.php/log?order=id,desc&page=1,1";
$url  = "http://hms.virtunesia.com/hotel_api/api.php/log?order=id,desc&page=1,1";
$data = file_get_contents($url);
$logs = json_decode($data, TRUE);

foreach ($logs as $log) {

  $count = count($log['records']); // cek jumlah array records
  
  for ($i=0; $i<$count; $i++)
    $id					= $log['records'][$i][0];
}

$where = "1";
if (isset($id)) $where = "id > $id";

//koneksi ke database
$file_db = $_SERVER['DOCUMENT_ROOT'] . "/doorlock/ic2000.mdb";
//$file_db = "C:/Users/klikota/AppData/Local/VirtualStore/Program Files (x86)/RF system/RF system/ic2000.mdb";
if (!file_exists($file_db)) {
  die("Could not find database");
}
$connection = odbc_connect("Driver={Microsoft Access Driver (*.mdb)};Dbq=$file_db", '', '');

// test koneksi
if (!$connection) {
    die("can't connect");
}

// menampilkan data dari database, table tb_anggota

//$sql = "SELECT DISTINCT card_no, room_id, room_no, card_type_id, card_type, unlock_time, get_time, issue_log_id FROM unlock_log WHERE $where";
$sql = "SELECT MAX(id) AS [id_], card_no, room_id, room_no, card_type_id, card_type, unlock_time, get_time, issue_log_id FROM unlock_log WHERE $where GROUP BY card_no, room_id, room_no, card_type_id, card_type, unlock_time, get_time, issue_log_id";
//$sql = "SELECT * FROM unlock_log";

$hasil = odbc_exec($connection, $sql);

if (!$hasil || odbc_num_rows($hasil) == 0) {
    echo "Data kosong";
}
else {
	echo "Data isi<br>";

	//$url  = "http://localhost/hotel_api/api.php/log";
	$url  = "http://hms.virtunesia.com/hotel_api/api.php/log";

	while ($row=odbc_fetch_array($hasil)) {

		//echo $row['id']."<br>";

		$data = array(
			'id' => $row['id_'],
			'card_no' => $row['card_no'],
			'room_id' => $row['room_id'],
			'room_no' => $row['room_no'],
			'card_type_id' => $row['card_type_id'],
			'card_type' => $row['card_type'],
			'unlock_time' => $row['unlock_time'],
			'get_time' => $row['get_time'],
			'issue_log_id' => $row['issue_log_id']
		);

		// use key 'http' even if you send the request to https://...
		$options = array(
			'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => 'POST',
				'content' => http_build_query($data)
			)
		);
		$context  = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		if ($result === FALSE) { /* Handle error */ }

		var_dump($result); echo "<br>";
	}

}

?>