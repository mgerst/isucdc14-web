<?php
session_start();
require __DIR__."/database.php";
require __DIR__."/ldap.php";
$con=get_db_connection();

if(mysqli_connect_errno()) {
    if (DEBUG) {
        die('{"error": "Error connecting to the database", "msg": "' . mysqli_connect_error() . '"}');
    } else {
        die('{"error": "Could  not connect to database"}');
    }
}

if (!(isset($_SESSION["username"])))
	die('{"error": "Need to be logged in"}');
$roles = get_roles($_SESSION["username"]);

$query = "SELECT client, description, pic, outcome FROM candc WHERE";
foreach ($roles as $role) {
 	$query .= " type=\"" . $role . "\" OR";
}
$query = substr($query, 0, strlen($query) - 3);
$query .= ";";

$results = $con->query($query);

$toreturn = "[";
while ($row = mysqli_fetch_array($results)) {
	if ($toreturn != "[") {$toreturn .= ",";}
	$toreturn .= '{"client":"' . $row["client"] . '",';
	$toreturn .= '"description":"' . $row["description"] . '",';
	$toreturn .= '"pic":"' . $row["pic"] . '",';
	$toreturn .= '"outcome":"' . $row["outcome"] . '"}';
}
$toreturn .= ']';
echo $toreturn . " ";
mysqli_close($con);

