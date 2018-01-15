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

$xmlstr = file_get_contents("http://www.nicovideo.jp/ranking/fav/daily/dance?rss=2.0&lang=ja-jp");
//$xmlstr = file_get_contents("./a.xml");
//file_put_contents("./a.xml", $xmlstr);
if(!$xmlstr)
{
	dir("read error $url");
}
$xml = new SimpleXMLElement($xmlstr);
echo $xml->channel->title."\n";
echo $xml->channel->link."\n";
echo $xml->channel->pubDate."\n";

$n = 0;
foreach($xml->channel->item as $item)
{
	$n++;
//	if($n > 4)break;
	$title = $item->title;
	$guid = $item->guid;
	$description = $item->description;

	$pattern = '|.*:/watch/(.*)|';
	if(preg_match($pattern, $guid, $matches) !== 1)
	{
		continue;
	}
	$id = $matches[1];

	$pattern = '|<strong class="nico-info-(.*?)">(.*?)</strong>|';
	$cnt = preg_match_all($pattern, $description, $matches);
	if($cnt < 9)continue;
	$pt      = (int)str_replace(',', '', $matches[2][0]);
	$date    = $matches[2][2];
	$view    = (int)str_replace(',', '', $matches[2][3]);

	if(preg_match_all('|\d+|', $date, $matches) !== 6)continue;
	$date = sprintf("%04d-%02d-%02d %02d:%02d:%02d", $matches[0][0], $matches[0][1], $matches[0][2], $matches[0][3], $matches[0][4], $matches[0][5]);
	$time = strtotime($date);
	$new = (time()-$time)/60/60 <= 24;

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

	echo sprintf("%3d %s %7s %7s %s $title\n", $n, $stat, $pt, $view, $id);
}

$result->free();
$mysqli->close();

?>
