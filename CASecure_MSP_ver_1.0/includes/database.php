<?php 
	
	$db_host = "localhost";
	$db_name = "cms";
	$db_user = "Eroberts";
	$db_pass = "abc.123";
	
	$conn = pg_connect("host=" + $db_host + " dbname=" + $db_name + " user=" + $db_user + " password=" + $db_pass);
	
	if (pg_connection_status($conn) === PGSQL_CONNECTION_BAD) {
		
		
	}
	
?>