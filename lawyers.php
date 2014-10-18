<?php
require __DIR__."/database.php";
$con=get_db_connection();

if(mysqli_connect_errno()) {
    die('{"error": "Could not connect to database"}');
}

$query = "SELECT fname, lname, image, bio FROM lawyers;";
$result = mysqli_query($con,$query);
$toreturn = "[";
while ($row = mysqli_fetch_array($result)) {
	if ($toreturn != "[") {$toreturn .= ",";}
	$toreturn .= '{"fname":"' . $row["fname"] . '",';
	$toreturn .= '"lname":"' . $row["lname"] . '",';
	$toreturn .= '"image":"' . $row["image"] . '",';
	$toreturn .= '"bio":"' . $row["bio"] . '"}';
}
$toreturn .= ']';
echo $toreturn . " ";
mysqli_close($con);
