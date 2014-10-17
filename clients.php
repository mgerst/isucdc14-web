<?php
$con=mysqli_connect("localhost","root","cdc", "site");

if(mysqli_connect_errno()) {
	echo "Failed to connect to MySQL: mysqli_connect(\"10.0.55.18\",\"root\",\"cdc\",\"site\"); --> " . mysqli_connect_error();
}
$sanitized_query = $_GET["query"];
$result = mysqli_query($con,$sanitized_query);
$toreturn = "[";
while ($row = mysqli_fetch_array($result)) {
	if ($toreturn != "[") {$toreturn .= ",";}
	$toreturn .= '{"client":"' . $row["client"] . '",';
	$toreturn .= '"description":"' . $row["description"] . '",';
	$toreturn .= '"pic":"' . $row["pic"] . '",';
	$toreturn .= '"outcome":"' . $row["outcome"] . '"}';
}
$toreturn .= ']';
echo $toreturn . " ";
mysqli_close($con);
?>
