<?php
	//$id = 2;
	$user = $_GET['user'];
	$timestamp = date("Y-m-d h:i:sO");
	$subject = $_GET['subj'];
	$requestType = $_GET['type'];
	$criticality = $_GET['crit'];
	$description = $_GET['desc'];
	
	//echo $description."\n";
	
	//$description = str_replace("'", '\'', $description);
	
	$db_host = "localhost";
	$db_name = "postgres";
	$db_username = "postgres";
	$db_password = "abc.123";
	
	$db = new PDO('pgsql:host='.$db_host.';dbname='.$db_name,$db_username,$db_password);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	
	//$lastId = $db->lastInsertId();
	//$id = $lastId + 1;
	
	$sql =
		"INSERT INTO requests ".
		"(user_id, timestamp, subject, type_id, criticality_id, description) ".
		"VALUES (:user, :timestamp, :subject, :requestType, :criticality, :description)";
	
	//echo $sql."\n";
	
	$query = $db->prepare($sql);
	
	$query->bindParam(':user', $user, PDO::PARAM_STR);
	$query->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
	$query->bindParam(':subject', $subject, PDO::PARAM_STR);
	$query->bindParam(':requestType', $requestType, PDO::PARAM_STR);
	$query->bindParam(':criticality', $criticality, PDO::PARAM_STR);
	$query->bindParam(':description', $description, PDO::PARAM_STR);
	
	try {
		$query->execute();
		echo "Data was successfully sent to the Database!\nuser = ".$user."\ntimestamp = ".$timestamp."\nsubject = ".$subject."\ntype id = ".$requestType."\ncriticality id = ".$criticality."\ndescription = ".$description;
	}
	catch (PDOException $e) {
		$db->rollback();
		echo errorHandle($e);
	}
	
	function errorHandle(Exception $e) {
		$trace = $e->getTrace();
		if ($trace[0]['class'] != "") {
			$class = $trace[0]['class'];
		}
			$method = $trace[0]['function'];
			$file = $trace[0]['file'];
			$line = $trace[0]['line'];
			$Exception_Output = $e->getMessage().
			"<br />Class & Method: ".$class."->".$method.
			"<br />File: ".$file.
			"<br />Line: ".$line;
			return $Exception_Output;
	}
	//
?>