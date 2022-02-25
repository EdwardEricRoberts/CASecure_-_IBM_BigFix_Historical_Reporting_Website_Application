<?php
	$timestamp = date("Y-m-d H:i:sO");
	//echo "\nTime = ".$timestamp."\n";//"<br />";
	
	$userName = $_GET['name'];
	$password = $_GET['pass'];
	$welcomeName = $_GET['wel'];
	$updater = $_GET['up'];
	$admin = $_GET['admin'];
	$bigFixUser = $_GET['bigfix'];
	$primaryEmail = $_GET['email'];
	
	$db_host = "localhost";
	$db_name = "CASecure1";
	$db_username = "postgres";
	$db_password = "abc.123";
	
	try {
	//$dsn = 'pgsql:host='.$db_host.';dbname='.$db_name
	$db = new PDO('pgsql:host='.$db_host.';dbname='.$db_name,$db_username,$db_password);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	//$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
	//$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	
	echo "Database connection successful.<br/>";
	}
	catch(PDOException $e) {
		echo $e->getMessage();
		//echo errorHandle($e);
		$db->rollback();
	}
	
	$sqlTableName = "users";
	if ($updater == null && $welcomeName != null) {
		$sql =
			"INSERT INTO ".$sqlTableName." ".
			"(user_name, password, welcome_name, timestamp, is_admin) ".
			"VALUES (:userName, :password, :welcomeName, :timestamp, :admin)";
		
		$query = $db->prepare($sql);
		
		$query->bindParam(':userName', $userName, PDO::PARAM_STR);
		$query->bindParam(':password', $password, PDO::PARAM_STR);
		$query->bindParam(':welcomeName', $welcomeName, PDO::PARAM_STR);
		$query->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
		$query->bindParam(':admin', $admin, PDO::PARAM_STR);
	}
	else if ($welcomeName == null && $updater != null) {
		$sql =
			"INSERT INTO ".$sqlTableName." ".
			"(user_name, password, last_updated_by_id, timestamp, is_admin) ".
			"VALUES (:userName, :password, :updater, :timestamp, :admin)";
		
		$query = $db->prepare($sql);
	
		$query->bindParam(':userName', $userName, PDO::PARAM_STR);
		$query->bindParam(':password', $password, PDO::PARAM_STR);
		$query->bindParam(':updater', $updater, PDO::PARAM_STR);
		$query->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
		$query->bindParam(':admin', $admin, PDO::PARAM_STR);
	}
	else if ($updater == null && $welcomeName == null) {
		$sql =
			"INSERT INTO ".$sqlTableName." ".
			"(user_name, password, timestamp, is_admin) ".
			"VALUES (:userName, :password, :timestamp, :admin)";
		
		$query = $db->prepare($sql);
	
		$query->bindParam(':userName', $userName, PDO::PARAM_STR);
		$query->bindParam(':password', $password, PDO::PARAM_STR);
		$query->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
		$query->bindParam(':admin', $admin, PDO::PARAM_STR);
	}
	else {
		$sql =
			"INSERT INTO ".$sqlTableName." ".
			"(user_name, password, welcome_name, last_updated_by_id, timestamp, is_admin) ".
			"VALUES (:userName, :password, :welcomeName, :updater, :timestamp, :admin)";
		
		$query = $db->prepare($sql);
	
		$query->bindParam(':userName', $userName, PDO::PARAM_STR);
		$query->bindParam(':password', $password, PDO::PARAM_STR);
		$query->bindParam(':welcomeName', $welcomeName, PDO::PARAM_STR);
		$query->bindParam(':updater', $updater, PDO::PARAM_STR);
		$query->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
		$query->bindParam(':admin', $admin, PDO::PARAM_STR);
	}	
	
	$query->execute();
	
	if ($query->errorCode() == 0) {
		echo "New User '".$userName."' was successfully created.<br />";
		
		$newUserId = $db->lastInsertId();
		
		echo 'Database User Id = '.$newUserId.'<br/>';
		
		if ($updater == null) {
			$consoleToPortalSQL = 
				"INSERT INTO console_to_portal ".
				"(user_id, bigfix_user_name, timestamp) ".
				"VALUES (:newUserId, :bigFixUser, :timestamp)";
			
			$query2 = $db->prepare($consoleToPortalSQL);
			
			$query2->bindParam(':newUserId', $newUserId, PDO::PARAM_STR);
			$query2->bindParam(':bigFixUser', $bigFixUser, PDO::PARAM_STR);
			$query2->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
			
			$changeLogSQL = 
				"INSERT INTO database_change_log ".
				"(timestamp, type_of_change, action_taken, affected_tables ) ".
				"VALUES (:timestamp, 'User Creation', :message, 'users, console_to_portal')";
			
			$query3 = $db->prepare($changeLogSQL);
			
			$message = 'Created new portal user "'.$userName.'" linked to BigFix user "'.$bigFixUser.'".';
			
			$query3->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
			$query3->bindParam(':message', $message, PDO::PARAM_STR);
		}
		else {
			$consoleToPortalSQL = 
				"INSERT INTO console_to_portal ".
				"(user_id, bigfix_user_name, last_updated_by_id, timestamp) ".
				"VALUES (:newUserId, :bigFixUser, :updater, :timestamp)";
			
			$query2 = $db->prepare($consoleToPortalSQL);
			
			$query2->bindParam(':newUserId', $newUserId, PDO::PARAM_STR);
			$query2->bindParam(':bigFixUser', $bigFixUser, PDO::PARAM_STR);
			$query2->bindParam(':updater', $updater, PDO::PARAM_STR);
			$query2->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
			
			$changeLogSQL = 
				"INSERT INTO database_change_log ".
				"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
				"VALUES (:updater, :timestamp, 'User Creation', :message, 'users, console_to_portal')";
		
			$query3 = $db->prepare($changeLogSQL);
			
			$message = 'Created new portal user "'.$userName.'" linked to BigFix user "'.$bigFixUser.'".';
			
			$query3->bindParam(':updater', $updater, PDO::PARAM_STR);
			$query3->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
			$query3->bindParam(':message', $message, PDO::PARAM_STR);
		}
		
		$query2->execute();
		
		if ($query2->errorCode() == 0) {
			echo "New User '".$userName."' linked to BigFix User '".$bigFixUser."'.<br/>";
		}
		else {
			$sqlErrors2 = $query2->errorInfo();
			echo ($sqlErrors2[2]);
			$db->rollback();
		}
		
		$primaryEmailSQL = 
			"INSERT INTO email_info ".
			"(user_id, email_address, priority) ".
			"VALUES (:newUserId, :primaryEmail, 1)";
			
		$query4 = $db->prepare($primaryEmailSQL);
		
		$query4->bindParam(':newUserId', $newUserId, PDO::PARAM_STR);
		$query4->bindParam(':primaryEmail', $primaryEmail, PDO::PARAM_STR);
		
		$query4->execute();
		
		if ($query4->errorCode() == 0) {
			echo "Pimary Email '".$primaryEmail."' added.<br/>";
		}
		else {
			$sqlErrors4 = $query4->errorInfo();
			echo ($sqlErrors4[2]);
			$db->rollback();
		}
		
		$query3->execute();
		
		if ($query3->errorCode() == 0) {
			echo "Transaction Logged";
		}
		else {
			$sqlErrors3 = $query3->errorInfo();
			echo ($sqlErrors3[2]);
			$db->rollback();
		}
		
	}
	else {
		$sqlErrors = $query->errorInfo();
		echo ($sqlErrors[2]);
		$db->rollback();
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
	
	function ArrayBinder(&$pdoStatement, &$array) {  //& operator is used because function binds values
		foreach ($array as $k=>$v) { // short for "key value"
			$pdoStatement->bindValue(':'.$k, $v);
		}
	}
?>