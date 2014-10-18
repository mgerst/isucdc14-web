<?php

require_once __DIR__ . "/config.php";

function get_db_connection() {
	return new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
}
