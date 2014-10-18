<?php
session_start();
require_once __DIR__ . "/ldap.php";

$postdata = file_get_contents("php://input");
$request = json_decode($postdata);

$ldap = ldap_login($request->username, $request->password);

$_SESSION["username"] = $request->username;
$_SESSION["role"] = $ldap["role"];

echo json_encode($ldap);
