<?php

function login($mail, $password)
{
	$url = "https://secure.nicovideo.jp/secure/login?site=niconico";
	$data = http_build_query(array('next_url'=>"", 'mail'=>$mail, 'password'=>$password, 'submit'=>""));
	$options = array('http'=>array('method'=>"POST", 'header'=>"Content-Type: application/x-www-form-urlencoded\r\nAccept-language: ja\r\n", 'content'=>$data));
	$context = stream_context_create($options);
	file_get_contents($url, false, $context);

	foreach($http_response_header as $res)
	{
		preg_match("(user_session=(user_session_[0-9a-z_]+))", $res, $tmp);
		if(count($tmp) >= 2)
		{
			return $tmp[1];
		}
	}
	return '';
}

?>
