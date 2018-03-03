<?php

function get_nicohistory($idx, $video_id, $user_session)
{
	global $v;

	$url = "http://nicovideo.jp/watch/$video_id";
	$options = array('http'=>array('method'=>"HEAD", 'header'=>"Accept-language: ja\r\nCookie: user_session=$user_session\r\n"));
	$context = stream_context_create($options);
	try
	{
		$data = file_get_contents($url, false, $context);
	}
	catch(Exception $e)
	{
		$v[$idx] = $v[count($v)-1];
		array_pop($v);
		file_put_contents('v', serialize($v));
		echo "remove $video_id\n";
		return true;
	}

	$logined = false;
	foreach($http_response_header as $header)
	{
		if(preg_match("(Set-Cookie: nicohistory=.+?;)", $header, $matches) === 1)
		{
//			return true;
		}
		if(preg_match("/x-niconico-id:\s*\d+/", $header, $matches) === 1)
		{
			$logined = true;
		}
	}
	return $logined;
}

?>
