<?php

function get_nicohistory($video_id, $user_session)
{
	$url = "http://nicovideo.jp/watch/$video_id";
	$options = array('http'=>array('method'=>"HEAD", 'header'=>"Accept-language: ja\r\nCookie: user_session=$user_session\r\n"));
	$context = stream_context_create($options);
	$data = file_get_contents($url, false, $context);

	$logined = false;
	foreach($http_response_header as $header)
	{
		if(preg_match("(Set-Cookie: nicohistory=.+?;)", $header, $matches) === 1)
		{
			return true;
		}
		if(preg_match("/x-niconico-id:\s*\d+/", $header, $matches) === 1)
		{
			$logined = true;
		}
	}
	return $logined;
}

?>
