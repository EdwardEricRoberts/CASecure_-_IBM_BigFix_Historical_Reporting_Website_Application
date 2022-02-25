<?php
	
	header('Content-type: application/xml');

	$timestamp = date("Y-m-d H:i:sO");
	//echo "\nTime = ".$timestamp."\n";//"<br />";
	
	$updater = $_GET['cid'];
	$userName = $_GET['name'];
	$password = $_GET['pass'];
	$welcomeName = $_GET['wel'];
	$admin = $_GET['admin'];
	$bigFixUser = $_GET['bigfix'];
	$primaryEmail = $_GET['email'];
	$defaultSite = $_GET['ds'];
	$defaultComputerGroup = $_GET['dcg'];
	
	$db_host = "localhost";
	$db_name = "CASecure1";
	$db_username = "postgres";
	$db_password = "abc.123";
	
	$xml= new SimpleXMLElement('<PDO/>'); 
	$connectionXML = $xml->addChild('Connection');
	try {
		$connectResultXML = $connectionXML->addChild('Result');
		
		//$dsn = 'pgsql:host='.$db_host.';dbname='.$db_name
		$db = new PDO('pgsql:host='.$db_host.';dbname='.$db_name,$db_username,$db_password);
		//$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		//$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
		//$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
		//$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		
		$connectResultXML->addChild('Status', 'Connected');
		$connectResultXML->addChild('Message', "Database Connection Successful");
		$connectResultXML->addChild('Host', $db_host);
		$connectResultXML->addChild('Database', $db_name);
		
		try {
			$queryXML = $xml->addChild('Query');
			
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$db->beginTransaction();
				
				$metaXML = $queryXML->addChild('Meta');
				$metaXML->addChild('Name', "Create User");
				$metaXML->addChild('Description', "This creates a new User Account.");
				$paramsXML = $metaXML->addChild('Parameters');
				$param1XML = $paramsXML->addChild('Parameter');
				$param1XML->addChild('Name', 'Current User ID');
				$param1XML->addChild('URL', 'cid');
				$param1XML->addChild('Value', $updater);
				$param1XML->addChild('Description', 'The User ID of the current User');
				$param2XML = $paramsXML->addChild('Parameter');
				$param2XML->addChild('Name', 'User Name');
				$param2XML->addChild('URL', 'name');
				$param2XML->addChild('Value', $userName);
				$param2XML->addChild('Description', 'The User Name of the User account being created. Note that this is what the user will use for logging in');
				$param3XML = $paramsXML->addChild('Parameter');
				$param3XML->addChild('Name', 'Password');
				$param3XML->addChild('URL', 'pass');
				$param3XML->addChild('Value', $password);
				$param3XML->addChild('Description', 'Password for the User account.');
				$param4XML = $paramsXML->addChild('Parameter');
				$param4XML->addChild('Name', 'User First Name');
				$param4XML->addChild('URL', 'wel');
				$param4XML->addChild('Value', $welcomeName);
				$param4XML->addChild('Description', 'Typically the First Name of the User.  The User will be greeted with this name.');
				$param5XML = $paramsXML->addChild('Parameter');
				$param5XML->addChild('Name', 'User Administrative Status');
				$param5XML->addChild('URL', 'admin');
				$param5XML->addChild('Value', $admin);
				$param5XML->addChild('Description', 'Boolean expressing whether the the User will be grated Administrative Priviledges.');
				$param6XML = $paramsXML->addChild('Parameter');
				$param6XML->addChild('Name', 'BigFix User Name');
				$param6XML->addChild('URL', 'bigfix');
				$param6XML->addChild('Value', $bigFixUser);
				$param6XML->addChild('Description', 'The name of the BigFix Console user to be attached to the User account being created.');
				$param7XML = $paramsXML->addChild('Parameter');
				$param7XML->addChild('Name', 'Primary Email Address');
				$param7XML->addChild('URL', 'email');
				$param7XML->addChild('Value', $primaryEmail);
				$param7XML->addChild('Description', 'Primary Email Address of the User account being created.');
				$param8XML = $paramsXML->addChild('Parameter');
				$param8XML->addChild('Name', 'Default Site');
				$param8XML->addChild('URL', 'ds');
				$param8XML->addChild('Value', $defaultSite);
				$param8XML->addChild('Description', 'The Default Site that will be used to access report data when the User first logs into their account.');
				$param9XML = $paramsXML->addChild('Parameter');
				$param9XML->addChild('Name', 'Default Computer Group');
				$param9XML->addChild('URL', 'dcg');
				$param9XML->addChild('Value', $defaultComputerGroup);
				$param9XML->addChild('Description', 'The Default Computer Group that will be used to access report data when the User first logs into their account.');
				$resultXML = $queryXML->addChild('Result');
				
				$start = microtime(true);
				
				$actionsXML = $resultXML->addChild('Actions');
				
				$welcomeNameSQL = (($welcomeName == null) ? null : $welcomeName);
				$updaterSQL = (($updater == null) ? null : $updater);
				
				$createUserSQL =
					"INSERT INTO users ".
					"(user_name, password, welcome_name, last_updated_by_id, timestamp, is_admin) ".
					"VALUES (:userName, :password, :welcomeName, :updater, :timestamp, :admin);";
				
				$createUserQuery = $db->prepare($createUserSQL);
				$createUserQuery->bindParam(':userName', $userName, PDO::PARAM_STR);
				$createUserQuery->bindParam(':password', $password, PDO::PARAM_STR);
				$createUserQuery->bindParam(':welcomeName', $welcomeNameSQL, PDO::PARAM_STR);
				$createUserQuery->bindParam(':updater', $updaterSQL, PDO::PARAM_INT);
				$createUserQuery->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
				$createUserQuery->bindParam(':admin', $admin, PDO::PARAM_STR);
				$createUserQuery->execute();
				
				$newUserId = $db->lastInsertId();
				
				$createUserXML = $actionsXML->addChild('Action');
				$createUserXML->addAttribute('type', 'User Creation');
				$createUserXML->addChild('Status', "User Account Created");
				$createUserXML->addChild('Details', 'New User "'.$userName.'" was successfully created with new User ID = '.$newUserId.'.');
				$usersInsertedRowsXML = $createUserXML->addChild('Inserted_Rows');
				$usersInsertedRowsXML->addAttribute('table', 'users');
				
				$userRowXML = $usersInsertedRowsXML->addChild('Row');
				$userRowXML->addChild('user_id', $newUserId);
				$userRowXML->addChild('user_name', $userName);
				$userRowXML->addChild('password', $password);
				$userRowXML->addChild('welcome_name', $welcomeName);
				$userRowXML->addChild('last_updated_by_id', $updater);
				$userRowXML->addChild('timestamp', $timestamp);
				$userRowXML->addChild('is_admin', $admin);
				
				$createUserXML->addChild('Count', 1);
				
				$consoleToPortalSQL = 
					"INSERT INTO console_to_portal ".
					"(user_id, bigfix_user_name, last_updated_by_id, timestamp) ".
					"VALUES (:newUserId, :bigFixUser, :updater, :timestamp);";
				$consoleToPortalQuery = $db->prepare($consoleToPortalSQL);
				$consoleToPortalQuery->bindParam(':newUserId', $newUserId, PDO::PARAM_STR);
				$consoleToPortalQuery->bindParam(':bigFixUser', $bigFixUser, PDO::PARAM_STR);
				$consoleToPortalQuery->bindParam(':updater', $updaterSQL, PDO::PARAM_INT);
				$consoleToPortalQuery->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
				$consoleToPortalQuery->execute();
				
				$consoleToPortalXML = $actionsXML->addChild('Action');
				$consoleToPortalXML->addAttribute('type', 'Connect to BigFix Console User');
				$consoleToPortalXML->addChild('Status', "Connected BigFix Console User Established");
				$consoleToPortalXML->addChild('Details', 'New User "'.$userName.'" was successfully connected to BigFix Console User "'.$bigFixUser.'".');
				$consoleToPortalInsertedRowsXML = $consoleToPortalXML->addChild('Inserted_Rows');
				$consoleToPortalInsertedRowsXML->addAttribute('table', 'console_to_portal');
				
				$consoleToPortalRowXML = $consoleToPortalInsertedRowsXML->addChild('Row');
				$consoleToPortalRowXML->addChild('user_id', $newUserId);
				$consoleToPortalRowXML->addChild('bigfix_user_name', $bigFixUser);
				$consoleToPortalRowXML->addChild('last_updated_by_id', $updater);
				$consoleToPortalRowXML->addChild('timestamp', $timestamp);
				
				$consoleToPortalXML->addChild('Count', 1);
				
				$primaryEmailSQL = 
					"INSERT INTO email_info ".
					"(user_id, email_address, priority) ".
					"VALUES (:newUserId, :primaryEmail, 1);";
					
				$primaryEmailQuery = $db->prepare($primaryEmailSQL);
				$primaryEmailQuery->bindParam(':newUserId', $newUserId, PDO::PARAM_STR);
				$primaryEmailQuery->bindParam(':primaryEmail', $primaryEmail, PDO::PARAM_STR);
				$primaryEmailQuery->execute();
				
				$primaryEmailXML = $actionsXML->addChild('Action');
				$primaryEmailXML->addAttribute('type', 'Set Primary Email Address');
				$primaryEmailXML->addChild('Status', "Email Set");
				$primaryEmailXML->addChild('Details', 'Primary Email Address for New User "'.$userName.'" has been set as "'.$primaryEmail.'".');
				$emailInsertedRowsXML = $primaryEmailXML->addChild('Inserted_Rows');
				$emailInsertedRowsXML->addAttribute('table', 'email_info');
				
				$emailRowXML = $emailInsertedRowsXML->addChild('Row');
				$emailRowXML->addChild('user_id', $newUserId);
				$emailRowXML->addChild('email_address', $primaryEmail);
				$emailRowXML->addChild('priority', 1);
				
				$primaryEmailXML->addChild('Count', 1);
				
				$userDefaultsSQL = 
					"INSERT INTO user_defaults ".
					"(user_id, default_site, default_computer_group) ".
					"VALUES (:newUserId, :defaultSite, :defaultComputerGroup)";
				$userDefaultsQuery = $db->prepare($userDefaultsSQL);
				$userDefaultsQuery->bindParam(':newUserId', $newUserId, PDO::PARAM_STR);
				$userDefaultsQuery->bindParam(':defaultSite', $defaultSite, PDO::PARAM_STR);
				$userDefaultsQuery->bindParam(':defaultComputerGroup', $defaultComputerGroup, PDO::PARAM_STR);
				$userDefaultsQuery->execute();
				
				$userDefaultsXML = $actionsXML->addChild('Action');
				$userDefaultsXML->addAttribute('type', 'Set User Default Site and Computer Group');
				$userDefaultsXML->addChild('Status', "User Defaults Set");
				$userDefaultsXML->addChild('Details', 'Default Site and Computer Groups have been set for New User "'.$userName.'".');
				$userDefaultsInsertedRowsXML = $userDefaultsXML->addChild('Inserted_Rows');
				$userDefaultsInsertedRowsXML->addAttribute('table', 'user_defaults');
				
				$userDefaultsRowXML = $userDefaultsInsertedRowsXML->addChild('Row');
				$userDefaultsRowXML->addChild('user_id', $newUserId);
				$userDefaultsRowXML->addChild('default_site', $defaultSite);
				$userDefaultsRowXML->addChild('default_computer_group', $defaultComputerGroup);
				
				$userDefaultsXML->addChild('Count', 1);
				
				$changeLogMessage = 'Created new portal user "'.$userName.'" linked to BigFix user "'.$bigFixUser.'".';
				
				$changeLogSQL = 
					"INSERT INTO database_change_log ".
					"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
					"VALUES (:updater, :timestamp, 'User Creation', :message, 'users, console_to_portal, email_info, user_defaults');";
				$changeLogQuery = $db->prepare($changeLogSQL);
				$changeLogQuery->bindParam(':updater', $updaterSQL, PDO::PARAM_STR);
				$changeLogQuery->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
				$changeLogQuery->bindParam(':message', $changeLogMessage, PDO::PARAM_STR);
				$changeLogQuery->execute();
				
				$end = microtime(true);
				
				$changeLogXML = $resultXML->addChild('Change_Log');
				$changeLogXML->addChild('Status', 'Transaction Logged');
				$changeDetailsXML = $changeLogXML->addChild('Details');
				$changeDetailsXML->addChild('timestamp', $timestamp);
				$changeDetailsXML->addChild('type_of_change', 'User Creation');
				$changeDetailsXML->addChild('action_taken', $changeLogMessage);
				$changeDetailsXML->addChild('affected_tables', 'users, console_to_portal, email_info, user_defaults');
				
				$changeLogXML->addChild('Count', 1);
				
				$evaluationXML = $queryXML->addChild('Evaluation');
				$evaluationXML->addChild('Time', round((($end - $start) * 1000), 3).'ms');
				$evaluationXML->addChild('Total_Number_of_Altered_Rows', 4);
				
				/*if ($query->errorCode() == 0 && $query2->errorCode() == 0 && $query3->errorCode() == 0 && $query4->errorCode() == 0) {
					echo "New User '".$userName."' was successfully created.<br />";
					echo 'Database User Id = '.$newUserId.'<br/>';
					echo "New User '".$userName."' linked to BigFix User '".$bigFixUser."'.<br/>";
					echo "Pimary Email '".$primaryEmail."' added.<br/>";
					//echo "Site Privileges established.<br/>";
					echo "User Defaults Set.<br/>";
					echo "Transaction Logged";
				}	*/		
				
			$db->commit();
		}
		catch (\PDOException $e) {
			$errorXML = $queryXML->addChild('Error');
			if ($createUserQuery->errorCode() != 0) {
				$createUserXML = $actionsXML->addChild('Action');
				$createUserXML->addAttribute('type', 'User Creation');
				$createUserXML->addChild('Status', "Failed to Create new User");
				if ($createUserQuery->errorCode() == 23502) {  // not_null_violation
					$errorDescription = 'Username and Password cannot be empty.';
				}
				else if ($createUserQuery->errorCode() == 23503) {   // foreign_key_violation
					$errorDescription = 'You are not able to create users.';
				}
				else if ($createUserQuery->errorCode() == 23505) {  // unique_violation
					$errorDescription = 'Username "'.$userName.'" already exists. Enter a differnt value for Username.';
				}
				else {
					$errorDescription = 'An Error occured when trying to Create User.';
				}
				$createUserXML->addChild('Details', $errorDescription);
			}
			else if ($consoleToPortalQuery->errorCode() != 0) {
				$consoleToPortalXML = $actionsXML->addChild('Action');
				$consoleToPortalXML->addAttribute('type', 'Connect to BigFix Console User');
				$consoleToPortalXML->addChild('Status', "Failed to set BigFix Console User");
				if ($consoleToPortalQuery->errorCode() == 23503) {   // foreign_key_violation
					$errorDescription = 'Incorrect User IDs and/or BigFix Username.';
				}
				else if ($consoleToPortalQuery->errorCode() == 23505) {  // unique_violation
					$errorDescription = 'User "'.$userName.'" can only have one have one BigFix Console User.';
				}
				else {
					$errorDescription = 'An Error occured setting the BigFix console user.';
				}
				$consoleToPortalXML->addChild('Details', $errorDescription);
			}
			else if ($primaryEmailQuery->errorCode() != 0) {
				$primaryEmailXML = $actionsXML->addChild('Action');
				$primaryEmailXML->addAttribute('type', 'Set Primary Email Address');
				$primaryEmailXML->addChild('Status', "Failed to set Primary Email Address");
				if ($primaryEmailQuery->errorCode() == 23502) {  // not_null_violation
					$errorDescription = 'Email Address cannot be empty.';
				}
				else if ($primaryEmailQuery->errorCode() == 23505) {  // unique_violation
					$errorDescription = 'Email Address "'.$primaryEmail.'" is already being used.  Enter a different Email Address.';
				}
				else {
					$errorDescription = 'An Error occured setting the Primary Email Address';
				}
				$primaryEmailXML->addChild('Details', $errorDescription);
			}
			else if ($userDefaultsQuery->errorCode() != 0) {
				$userDefaultsXML = $actionsXML->addChild('Action');
				$userDefaultsXML->addAttribute('type', 'Set User Default Site and Computer Group');
				$userDefaultsXML->addChild('Status', "Failed to set User Defaults");
				$errorDescription = 'Site Privilages could not be Established due to Error.';
				$userDefaultsXML->addChild('Details', $errorDescription);
			}
			else if ($changeLogQuery->errorCode() != 0) {
				$changeLogXML = $resultXML->addChild('Change_Log');
				$changeLogXML->addChild('Status', 'Failed to log Transaction');
				$errorDescription = 'Unable to Log Transaction due to Error, entire transaction has been Undone.';
				$changeLogXML->addChild('Details', );
			}
			$errorCode = $e->getCode();
			$errorMessage = $e->getMessage();
			
			$errorXML->addChild('Error_Code', $errorCode);
			$errorXML->addChild('Details', $errorMessage);
			
			$db->rollBack();
			//throw $e;
			
			try {
				$errorLogSQL = 
					"INSERT INTO error_log ".
					"(user_id, description, error_code, error_message, exception_type, timestamp) ".
					"VALUES (:userID, :description, :errorCode, :errorMessage, 'PDO Query', :timestamp);";
				$errorLogQuery = $db->prepare($errorLogSQL);
				$errorLogQuery->bindParam(':userID', $updaterSQL, PDO::PARAM_STR);
				$errorLogQuery->bindParam(':description', $errorDescription, PDO::PARAM_STR);
				$errorLogQuery->bindParam(':errorCode', $errorCode, PDO::PARAM_STR);
				$errorLogQuery->bindParam(':errorMessage', $errorMessage, PDO::PARAM_STR);
				$errorLogQuery->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
				$errorLogQuery->execute();
				
				$errorLogXML = $errorXML->addChild('Error_Log');
				$errorLogXML->addChild('Status', 'Error Logged');
				$errorLogDetailsXML = $errorLogXML->addChild('Details');
				$errorLogDetailsXML->addChild('description', $errorDescription);
				$errorLogDetailsXML->addChild('error_code', $e->getCode());
				$errorLogDetailsXML->addChild('error_message', $e->getMessage());
				$errorLogDetailsXML->addChild('exception_type', 'PDO Query');
				$errorLogDetailsXML->addChild('timestamp', $timestamp);
			}
			catch(\PDOException $e2) {
				$errorLogXML = $errorXML->addChild('Error_Log');
				$errorLogXML->addChild('Status', 'Failed to Log Error');
				$errorLogXML->addChild('Error_Code', $e2->getCode());
				$errorLogXML->addChild('Details', $e2->getMessage());
			}
		}
	}
	catch(PDOException $e) {
		$connectResultXML->addChild('Status', 'Failure');
		$connectResultXML->addChild('Message', "Failed to Connect to Database.  Please Email Site Administrator.");
		$connectResultXML->addChild('Host', $db_host);
		$connectResultXML->addChild('Database', $db_name);
		
		$connectErrorXML = $connectionXML->addChild('Error');
		$connectErrorXML->addChild('Error_Code', $e->getCode());
		$connectErrorXML->addChild('Details', $e->getMessage());
	}
	catch(Exception $e) {
		$connectResultXML->addChild('Status', 'Failure');
		$connectResultXML->addChild('Message', "An Unexpected Error Occured.  Please try again. If Issue Persists, Please Email Site Administrator.");
		$connectResultXML->addChild('Host', $db_host);
		$connectResultXML->addChild('Database', $db_name);
		
		$connectErrorXML = $connectionXML->addChild('Error');
		$connectErrorXML->addChild('Error_Code', $e->getCode());
		$connectErrorXML->addChild('Details', $e->getMessage());
	}
	finally {
		echo $xml->asXML();
	}

	

	
	function pdoMultiInsert($tableName, $data, $pdoObject) {
		$rowSQL = array();
		
		$toBind = array();
		
		$columnNames = array_keys($data[0]);
		
		foreach($data as $arrayIndex => $row) { // $arrayIndex = the numbers 0 through 42
			$params = array();
			foreach($row as $columnName => $columnValue) { //$columnName = user_id, site_name, or user_privilege
				$param = ":".$columnName.$arrayIndex;
				$params[] = $param;
				$toBind[$param] = $columnValue;
			}
			$rowsSQL[] = "(".implode(", ", $params).")";
		}
		
		$sql = "INSERT INTO ".$tableName." (".implode(", ", $columnNames).") VALUES ".implode(", ", $rowsSQL);
		
		$pdoStatement = $pdoObject->prepare($sql);
		
		foreach($toBind as $param => $val) {
			$pdoStatement->bindValue($param, $val); //bindParam
		}
		
		return $pdoStatement;//->execute();
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