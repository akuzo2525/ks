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

function watch($video_id)
{
	global $host, $user, $pass, $db, $tb;

	$mysqli = new mysqli($host, $user, $pass, $db);
	if($mysqli->connect_errno)
	{
		dir($mysqli->connect_error."\n");
	}
	$mysqli->query("set names utf8") or die($mysqli->error);

	$query = "SELECT * FROM $db.$tb";
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
	//	echo "#$nicohistory#\n";
	}
	$mysqli->close();
}

$mysqli = new mysqli("14.39.90.172", "nico", "", "ks", 6306);
if($mysqli->connect_errno)
{
	die($mysqli->connect_error);
}
$mysqli->query("set names utf8") or die($mysqli->error);

$file = file_get_contents("http://www.nicovideo.jp/ranking/fav/daily/dance");
if(!$file)
{
	dir("read error $url");
}

preg_match_all('/<li class="item videoRanking.*?<\/div>\s*<\/li>/s', $file, $data);
$n = count($data[0]);
if($n != 100)
{
	dir("#$n");
}
else
{
	$pat  = '/.*?';
	$pat .= 'data-id="(.*?)".*?';
	$pat .= '<p class="rankingPt">(.*?)<\/p>.*?';
	$pat .= '<p class="itemTime(.*?)"> <span>(.*?)<\/span>.*?<\/p>.*?'
	       .'data-original="(.*?)".*?'
	       .'(<a title=".*?".*?<\/a>).*?'		// 6
	       .'view">.*?([0-9,]*)<\/span>.*?'		// 7
	       .'comment">.*?([0-9,]*)<\/span>.*?'	// 8
	       .'mylist">.*?([0-9,]*)<\/a>.*?'	// 9
	       .'/s';

	for($i = 0; $i < $n; $i++)
	{
		preg_match($pat, $data[0][$i], $res);
		if(count($res) > 0)
		{
			$id = $res[1];
			$pt = (int)str_replace(',', '', $res[2]);
			$view = str_replace(',', '', $res[7]);
			$comment = str_replace(',', '', $res[8]);
			$mylist = str_replace(',', '', $res[9]);

			$query = "SELECT flag,skip,black FROM v WHERE video_id='$id'";
			$result = $mysqli->query($query);

			if($row = $result->fetch_assoc())
			{
				$flag = $row['flag'];
				$skip = $row['skip'];
				$black = $row['black'];
			}
			else
			{
				$flag = 0;
				$black = false;
			}

			if($black == false)
			{
				$stat = (($flag&15) > 0) ? 'o' : '.';
			}
			else
			{
				$stat = '-';
			}

			{
				echo sprintf("%3d %s %7s %7s %s\n", $i+1, $stat, $pt, $view, $id);
			}
		}
	}

//	$result->free();
	$mysqli->close();
}

?>
