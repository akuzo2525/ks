<?php

require "./login.php";

$host = "127.0.0.1";
$port = 3306;
$user = "nico";
$pass = "";
$db = "ks";
$tb = "acc";

function get_nicohistory($video_id, $user_session)
{
	$url = "http://nicovideo.jp/watch/$video_id";
	$options = array('http'=>array('method'=>"HEAD", 'header'=>"Accept-language: ja\r\nCookie: user_session=$user_session\r\n"));
	$context = stream_context_create($options);
	$data = file_get_contents($url, false, $context);
//	echo "[$data]\n";

	$nicohistory = "";
	foreach($http_response_header as $res)
	{
	//	echo $res."\n";
		preg_match("(Set-Cookie: nicohistory=(.+?);)", $res, $tmp);
		if(count($tmp) >= 2)
		{
			$nicohistory = $tmp[1];
		}
	}
	return $nicohistory;
}

	$video_id = "sm31767145";

	$mysqli = new mysqli($host, $user, $pass, $db);
	if($mysqli->connect_errno)
	{
		dir($mysqli->connect_error."\n");
	}
	$mysqli->query("set names utf8") or die($mysqli->error);

	$query = "SELECT * FROM $db.$tb limit 2";
	$result = $mysqli->query($query) or die($mysqli->error);
	while($row = $result->fetch_assoc())
	{
		$mail = $row['mail'];
		$password = $row['password'];
		$user_session = $row['user_session'];
		echo "$mail\n";
		$nicohistory = get_nicohistory($video_id, $user_session);
		if($nicohistory === "")
		{
			$user_session = login($mail, $password);
			$nicohistory = get_nicohistory($video_id, $user_session);
			if($nicohistory === "")
			{
				exit("login error");
			}
			$query = "UPDATE $db.$tb SET user_session='$user_session' WHERE mail='$mail'";
			echo "$query\n";
			$mysqli->query($query) or die($mysqli->error);
		}
		echo "#$nicohistory#\n";
	}
?>
