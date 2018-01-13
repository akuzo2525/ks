<?php

function get_nicohistory($video_id, $user_session)
{
	$url = "http://nicovideo.jp/watch/$video_id";
//	$url = "http://nicovideo.jp/watch/sm32473973";
	$options = array('http'=>array('method'=>"HEAD", 'header'=>"Accept-language: ja\r\nCookie: user_session=$user_session\r\n"));
	$context = stream_context_create($options);
	$data = file_get_contents($url, false, $context);

	foreach($http_response_header as $res)
	{
	//	echo "@ $res\n";
		preg_match("(Set-Cookie: nicohistory=(.+?);)", $res, $tmp);
		if(count($tmp) >= 2)
		{
			return true;
		}
	}
	return false;
}

?>
