<?php

$host = "127.0.0.1";
$port = 3306;
$user = "nico";
$pass = "";
$db = "ks";
$tb = "acc";

echo "<html>\n";

echo "<head>\n";
echo "<title>acc</title>\n";
echo "</head>\n";

$mysqli = new mysqli($host, $user, $pass, $db);
if($mysqli->connect_errno)
{
	dir($mysqli->connect_error."\n");
}
$mysqli->query("set names utf8") or die($mysqli->error);

if(isset($_POST['cmd']))
{
	$ip = $_SERVER['REMOTE_ADDR'];
	$cmd = $_POST['cmd'];
	$mail = $_POST['mail'];
	$password = $_POST['password'];
	echo "#$ip#$cmd#$mail#$password<br>\n";

	$query = "SELECT id FROM $db.$tb WHERE mail='$mail'";
	$result = $mysqli->query($query) or die($mysqli->error);
	if($row = $result->fetch_assoc())
	{
		echo "skip $mail<br>\n";
	}
	else
	{
		$query = "INSERT INTO $db.$tb(create_date,ip,mail,password) VALUES(now(),'$ip','$mail','$password')";
		echo $query;
		$mysqli->query($query) or die($mysqli->error);
	}

}

echo "<body>\n";

$self = $_SERVER['PHP_SELF'];
echo "<form method=\"POST\" action=\"$self\">\n";
echo "<input type=\"hidden\" name=\"cmd\" value=\"add\">\n";
echo "<input type=\"text\" name=\"mail\">\n";
echo "<input type=\"text\" name=\"password\">\n";
echo "<input type=\"submit\"><br>\n";
echo "</form>\n";

echo "<table border=\"1\">\n";

$query = "SELECT * FROM $db.$tb";
$result = $mysqli->query($query) or die($mysqli->error);

while($row = $result->fetch_assoc())
{
	$id = $row['id'];
	$timestamp = $row['timestamp'];
	$ip = $row['ip'];
	$mail = $row['mail'];
	$password = $row['password'];
	$user_session = $row['user_session'];

	echo "<tr>\n";
	echo "<td>$id</td>\n";
	echo "<td>$timestamp</td>\n";
	echo "<td>$ip</td>\n";
	echo "<td>$mail</td>\n";
	echo "<td>$password</td>\n";
	echo "<td>$user_session</td>\n";
	echo "</tr>\n";
}

$result->free();
$mysqli->close();
echo "</table>\n";

echo "</body>\n";

echo "</html>\n";
?>
