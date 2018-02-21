<?php

require "./login.php";
require "./watchlib.php";
require "./define.php";

date_default_timezone_set('Asia/Tokyo');
$start = time();
//echo date("Y-m-d H:i:s", $start)."\n";

function exception_error_handler($errno, $errstr, $file, $line)
{
	throw new ErrorException($errstr, 0, $errno, $file, $line);
}
set_error_handler("exception_error_handler");

$mysqli = new mysqli($host, $user, "", $db, $port);
if(isset($argv) && count($argv) >= 2)
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
			}
			break;
		case 'vu':
			$u = unserialize(file_get_contents('u'));
			$s = unserialize(file_get_contents('s'));
			$cnt = count($u);
			for($i = 0; $i < $cnt; $i++)
			{
				echo sprintf("%2d %s %2d [%s] %s\n", $i, $s[$i]['s'], $s[$i]['c'], date("m-d H:i:s", $s[$i]['t']), $u[$i]['m']);
			}
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
	$u = unserialize(file_get_contents('u'));
	$s = unserialize(file_get_contents('s'));

//	foreach($v as $val)echo $val."\n";
//	foreach($u as $val)echo sprintf("%s:%s\n", $val['m'], $val['p']);
//	foreach($s as $val)echo sprintf("%s:%2d:%d\n", $val['s'], $val['c'], $val['t']);
//	foreach($s as $val)echo sprintf("%s:%2d:%s\n", $val['s'], $val['c'], date("m-d H:i:s", $val['t']));
}

if($rnd === true)
{
	$id = rand(0, count($s)-1);
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
		$idx = rand(0, count($v)-1);
		$video_id = $v[$idx];
		$diff = (time()-$s[$id]['t'])/60/60;
		if(get_nicohistory($idx, $video_id, $user_session) === false)
		{
			{
				$password = $u[$id]['p'];
				$user_session = login($mail, $password);
				if(get_nicohistory($idx, $video_id, $user_session) === false)
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
		}
		echo sprintf("%2d %s(%+3d) %-10s %3d(%2d:%02d) %16s\n", $i, date("H:i:s"), time()-$start, $video_id, $id, $diff/24, $diff%24, $mail);
		sleep(1);
	}
}
file_put_contents($idpath, $id);
$mysqli->close();

?>
