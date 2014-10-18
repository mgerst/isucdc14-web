<?php
session_start();

require __DIR__ . '/database.php';
require __DIR__ . '/ldap.php';

$username = $_SESSION["username"];
$roles = get_roles($username);

$postdata = file_get_contents("php://input");
$request = json_decode($postdata);

if (!isset($request)) {
	die('{"error": "There was an error with your request"}
		');
}

if (!in_array("Lawyer", $roles) && !in_array("ITTeam", $roles)) {
	die('{"error": "Only Lawyers may create cases"}');
}

$db = get_db_connection();

switch($request->pic) {
	case "yes":
		$pic = "yes.jpg";
		break;
	case "no":
		$pic = "no.jpg";
		break;
	case "open":
		$pic = "open.jpg";
		break;
	default:
		$pic = "open.jpg";
}

switch($request->type) {
	case "Negligence":
		$type = "Negligence";
		break;
	case "Divorce":
		$type = "Divorce";
		break;
	case "TaxEvasion":
		$type = "TaxEvasion";
		break;
	default:
		$type = "";
}

$query = "INSERT INTO candc (client, description, pic, outcome, type) VALUES(?, ?, ?, ?, ?);";
// echo "Query: " . var_dump($query);
$stmt = $db->prepare($query);
// echo "Error: " . mysqli_error($db);
// echo "Statment: " . var_dump($stmt);
$stmt->bind_param("sssss", $request->name, $request->description, $pic, $request->outcome, $type);
$stmt->execute();

die('{"success": "The case was added"}');