<?php

require "./login.php";
require "./watchlib.php";
require "./define.php";

date_default_timezone_set('Asia/Tokyo');
$start = time();
//echo date("Y-m-d H:i:s", $start)."\n";

$mysqli = new mysqli($host, $user, "", $db, $port);
if(count($argv) >= 2)
{
	for($i = 1; $i < count($argv); $i++)
	{
		echo "$i:$argv[$i]\n";
		switch($argv[$i])
		{
		case 'vv':
			$v = unserialize(file_get_contents('v'));
			echo "cnt=".count($v)."\n";
			$vmap = array();
			foreach($v as $key)
			{
				echo "[$key]\n";
				$vmap[$key] = false;
			}
			$vmap['sm32563463'] = true;
			echo "\n";
			$tmp = array();
			foreach($vmap as $key=>$val)
			{
				echo "[$key:$val]\n";
				if($val == true)$tmp[] = $key;
			}
			file_put_contents('v', serialize($tmp));
			break;
		case 'v':
			$result = $mysqli->query("SELECT video_id FROM $tb WHERE flag&$flag");
			$v = array();
			while($row = $result->fetch_row())
			{
				$v[] = $row[0];
				echo "$row[0]\n";
			}
			file_put_contents('v', serialize($v));
			break;
		case 'u':
			$result = $mysqli->query("SELECT mail, password FROM $acc_db where id>=$min AND id<=$max");
			$u = array();
			while($row = $result->fetch_row())
			{
				echo sprintf("%s %s\n", $row[0], $row[1]);
				$u[] = array('m'=>$row[0], 'p'=>$row[1]);
			}
			file_put_contents('u', serialize($u));
			break;
		case 's':
			$result = $mysqli->query("SELECT user_session, UNIX_TIMESTAMP(timestamp) FROM $acc_db where id>=$min AND id<=$max");
			$s = array();
			while($row = $result->fetch_row())
			{
				echo sprintf("%s %2d %d\n", $row[0], 0, $row[1]);
				$s[] = array('s'=>$row[0], 'c'=>0, 't'=>(int)$row[1]);
			}
			file_put_contents('s', serialize($s));
			break;
		default:
			break;
		}
	}
	exit;
}

if(isset($_POST['cnt']))
{
	$cnt = $_POST['cnt'];
}
$rnd = isset($_POST['rnd']);

{
	$v = unserialize(file_get_contents('v'));
	$n = count($v);
	$u = unserialize(file_get_contents('u'));
	$s = unserialize(file_get_contents('s'));

//	echo "cnt=$n\n";
//	foreach($v as $val)echo $val."\n";
//	foreach($u as $val)echo sprintf("%s:%s\n", $val['m'], $val['p']);
//	foreach($s as $val)echo sprintf("%s:%2d:%d\n", $val['s'], $val['c'], $val['t']);
//	foreach($s as $val)echo sprintf("%s:%2d:%s\n", $val['s'], $val['c'], date("m-d H:i:s", $val['t']));
}

if($rnd === true)
{
	$id = rand($min, $max);
}
else
{
	$id = (int)file_get_contents($idpath);
}

for($i = 0; $i < $cnt; $i++)
{
	$id++;
	if($id >= count($s))$id = 0;
	{
		$user_session = $s[$id]['s'];
		$mail = $u[$id]['m'];
		$video_id = $v[rand(0, $n-1)];
		$diff = (time()-$s[$id]['t'])/60/60;
		if(get_nicohistory($video_id, $user_session) === false)
		{
			if($diff/24 >= 24)
			{
				$password = $u[$id]['p'];
				$user_session = login($mail, $password);
				if(get_nicohistory($video_id, $user_session) === false)
				{
					echo "login error $id:$mail\n";
					break;
				}
				$s[$id]['s'] = $user_session;
				$s[$id]['c']++;
				$s[$id]['t'] = time();
				file_put_contents('s', serialize($s));
				echo "login $id:$mail\n";
				break;
			}
			else
			{
				$video_id = 'skip';
			}
		}
		echo sprintf("%2d %s(%+3d) %-10s %3d(%2d:%02d) %16s\n", $i, date("H:i:s"), time()-$start, $video_id, $id, $diff/24, $diff%24, $mail);
		sleep(1);
	}
}
file_put_contents($idpath, $id);
$mysqli->close();

?>
