<?php

$url = 'https://account.nicovideo.jp/my/history/login';
$u = unserialize(file_get_contents('u'));
$s = unserialize(file_get_contents('s'));

echo "<!DOCTYPE html>\n";
echo "<html lang=\"ja\">\n";
echo "<head>\n";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n";
echo "<title>history/login</title>\n";
echo "</head>\n";

echo "<body>\n";
if(isset($_GET['id']))
{
	$id = $_GET['id'];
	$session = $s[$id]['s'];
	$options = array('http'=>array('method'=>"GET", 'header'=>"Accept-language: ja\r\n"."Cookie: user_session=$session\r\n"));
	$context = stream_context_create($options);
	$file = file_get_contents($url, false, $context);
	if($file !== FALSE)
	{
		$pattern = '|"session-log-date">(.+?)</p>.*?"session-log-address">(.+?)</p>.*?"session-log-country">(.+?)</p>.*?"session-log-connection">(.+?)</p>|s';
		$count = preg_match_all($pattern, $file, $matches);
		echo "<table border=\"1\">\n";
		for($i = 0; $i < $count; $i++)
		{
			echo "<tr>\n";
			echo "<td>$i</td>\n";
			echo "<td>".$matches[1][$i]."</td>\n";
			echo "<td>".$matches[2][$i]."</td>\n";
			echo "<td>".$matches[3][$i]."</td>\n";
			echo "<td>".$matches[4][$i]."</td>\n";
			echo "</tr>\n";
		}
		echo "</table>\n";
	}
}
else
{
	$request_uri = $_SERVER['REQUEST_URI'];
	$cnt = count($u);
	echo "<table border=\"1\">\n";
	for($i = 0; $i < $cnt; $i++)
	{
		echo "<tr>\n";
		echo "<td>".$i."</td>\n";
		echo "<td>".substr($s[$i]['s'], 0, 28)."</td>\n";
		echo "<td>".$s[$i]['c']."</td>\n";
		echo "<td>".date("y-m-d H:i:s", $s[$i]['t'])."</td>\n";
		if(strlen($s[$i]['s']) > 0)
		{
		echo "<td><a href=\"$request_uri?id=$i\">".$u[$i]['m']."</td>\n";
		}
		else
		{
			echo "<td>".$u[$i]['m']."</td>\n";
		}
		echo "</tr>\n";
	}
	echo "</table>\n";
}
echo "</body>\n";
echo "</html>\n";

?>
