<?php
	
	header('Content-type: application/xml');
	
	$timestamp = date("Y-m-d H:i:sO");
	//echo "\nTime = ".$timestamp."\n";//"<br />";
	
	$fileName = basename(__FILE__, '.php').'.php';
	$fileDirectory = getcwd();
	$requestURI = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http")."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	
	$currentUserID = $_GET['cid'];
	$userGroupName = $_GET['group'];
	$userIDs = json_decode($_GET['ids']);
	//print_r($userIDs);
	if (sizeof($userIDs) == 0)
		$userIDs = array(0);
	
	$db_host = "localhost";
	$db_name = "CASecure2";
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
		
		$connectResultXML->addChild('Status', 'Connected');
		$connectResultXML->addChild('Message', "Database Connection Successful");
		$connectResultXML->addChild('Host', $db_host);
		$connectResultXML->addChild('Database', $db_name);
		
		try {
			$queryXML = $xml->addChild('Query');
			
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$db->beginTransaction();
				
				$metaXML = $queryXML->addChild('Meta');
				$metaXML->addChild('Name', "Create User Group");
				$metaXML->addChild('Description', "This query creates a new User Group named by the current User, and populates it with a list of User Members.");
				$paramsXML = $metaXML->addChild('Parameters');
				$param1XML = $paramsXML->addChild('Parameter');
				$param1XML->addChild('Name', 'Current User ID');
				$param1XML->addChild('URL', 'cid');
				$param1XML->addChild('Value', $currentUserID);
				$param1XML->addChild('Description', 'The User ID of the current User');
				$param2XML = $paramsXML->addChild('Parameter');
				$param2XML->addChild('Name', 'User Group Name');
				$param2XML->addChild('URL', 'group');
				$param2XML->addChild('Value',$userGroupName);
				$param2XML->addChild('Description', 'The Name of the User Group being created.');
				$param3XML = $paramsXML->addChild('Parameter');
				$param3XML->addChild('Name', 'User Group User IDs');
				$param3XML->addChild('URL', 'ids');
				$param3XML->addChild('Value',$_GET['ids']);
				$param3XML->addChild('Description', 'Array of the User IDs for users that will be members of the User Group being created');
				$resultXML = $queryXML->addChild('Result');
				
				$start = microtime(true);
				
				$actionsXML = $resultXML->addChild('Actions');
				if ($userGroupName != null) {
					$logUserGroupSQL =
						"INSERT INTO user_groups ".
						"(user_group_name) ".
						"VALUES (:userGroupName)";
					$logUserGroupQuery = $db->prepare($logUserGroupSQL);
					$logUserGroupQuery->bindParam(':userGroupName', $userGroupName, PDO::PARAM_STR);
					$logUserGroupQuery->execute();
				}
				else {
					$logUserGroupSQL =
						"INSERT INTO user_groups ".
						"(user_group_name) ".
						"VALUES (NULL)";
					$logUserGroupQuery = $db->query($logUserGroupSQL);
				}
				
				$newUserGroupId = $db->lastInsertId();
				
				$logUserGroupXML = $actionsXML->addChild('Action');
				$logUserGroupXML->addAttribute('type', 'Log User Group');
				$logUserGroupXML->addChild('Status', "User Group Created");
				$logUserGroupXML->addChild('Details', 'New User Group "'.$userGroupName.'" was successfully created with new User Group ID = '.$newUserGroupId.'.');
				$userGroupsInsertedRowsXML = $logUserGroupXML->addChild('Inserted_Rows');
				$userGroupsInsertedRowsXML->addAttribute('table', 'user_groups');
				
				$userGroupRowXML = $userGroupsInsertedRowsXML->addChild('Row');
				$userGroupRowXML->addChild('user_group_id', $newUserGroupId);
				$userGroupRowXML->addChild('user_group_name', $userGroupName);
				
				$logUserGroupXML->addChild('Count', 1);
				
				//print_r($userIDs);
				
				$userData = array();
				
				if ($currentUserID == null)	{
					for ($i = 0; $i < sizeof($userIDs); $i++) {
						$userData[$i] = 
							array(
								'user_group_id' => $newUserGroupId,
								'user_id' => $userIDs[$i],
								'timestamp' => $timestamp
							);
					}
					
					$logUserGroupUsersQuery = pdoMultiInsert('user_group_users', $userData, $db);
					$logUserGroupUsersQuery->execute();
					
					
					foreach ($userData as $key => $user) {
						$usernameQuery = $db->prepare('SELECT user_name FROM users WHERE user_id = :userId');
						$usernameQuery->bindParam(':userId',$user['user_id'],PDO::PARAM_STR);
						$usernameQuery->execute();
						$usernames[] = ($usernameQuery->fetch(PDO::FETCH_ASSOC))['user_name'];
					}
					$usernamesString = "";
					foreach ($usernames as $key => $username) 
					{
						if($key != 0) {
							$usernamesString .= ", ";
						}
						$usernamesString .= $username;
					}
					
					$logUserGroupUsersXML = $actionsXML->addChild('Action');
					$logUserGroupUsersXML->addAttribute('type', 'Log User Group users');
					$logUserGroupUsersXML->addChild('Status', "Users added to User Group");
					$logUserGroupUsersXML->addChild('Details', 'Users '.$usernamesString.' were successfully added to New User Group "'.$userGroupName.'".' );
					$userGroupsUsersInsertedRowsXML = $logUserGroupUsersXML->addChild('Inserted_Rows');
					$userGroupsUsersInsertedRowsXML->addAttribute('table', 'user_group_users');
					
					foreach ($userData as $key => $user) {
						$userGroupRowXML = $userGroupsUsersInsertedRowsXML->addChild('Row');
						//$userGroupRowXML->addChild('user_group_id', $user['user_group_id']);
						$userGroupRowXML->addChild('user_id', $user['user_id']);
						$usernameQuery = $db->prepare('SELECT user_name FROM users WHERE user_id = :userId');
						$usernameQuery->bindParam(':userId',$user['user_id'],PDO::PARAM_STR);
						$usernameQuery->execute();
						$username = ($usernameQuery->fetch(PDO::FETCH_ASSOC))['user_name'];
						$userGroupRowXML->addChild('user_name', $username);
						$userGroupRowXML->addChild('timestamp', $user['timestamp']);
					}
					$logUserGroupUsersXML->addChild('Count', sizeOf($userData));
					
					$changeLogMessage = 'Created new User Group "'.$userGroupName.'" populated by User IDs "'.implode(", ", $userIDs).'".';
					
					$changeLogSQL = 
						"INSERT INTO database_change_log ".
						"(timestamp, type_of_change, action_taken, affected_tables ) ".
						"VALUES (:timestamp, 'User Group Creation', :message, 'user_groups, user_group_users');";
					$changeLogQuery = $db->prepare($changeLogSQL);
					$changeLogQuery->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
					$changeLogQuery->bindParam(':message', $changeLogMessage, PDO::PARAM_STR);
					$changeLogQuery->execute();
					
				}
				else {
					for ($i = 0; $i < sizeof($userIDs); $i++) {
						$userData[$i] = 
							array(
								'user_group_id' => $newUserGroupId,
								'user_id' => $userIDs[$i],
								'last_updated_by_id' => $currentUserID,
								'timestamp' => $timestamp
							);
					}
					
					$logUserGroupUsersQuery = pdoMultiInsert('user_group_users', $userData, $db);
					$logUserGroupUsersQuery->execute();
					
					foreach ($userData as $key => $user) {
						$usernameQuery = $db->prepare('SELECT user_name FROM users WHERE user_id = :userId');
						$usernameQuery->bindParam(':userId',$user['user_id'],PDO::PARAM_STR);
						$usernameQuery->execute();
						$usernames[] = ($usernameQuery->fetch(PDO::FETCH_ASSOC))['user_name'];
					}
					$usernamesString = "";
					foreach ($usernames as $key => $username) 
					{
						if($key != 0) {
							$usernamesString .= ", ";
						}
						$usernamesString .= $username;
					}
					
					$logUserGroupUsersXML = $actionsXML->addChild('Action');
					$logUserGroupUsersXML->addAttribute('type', 'Log User Group users');
					$logUserGroupUsersXML->addChild('Status', "Users added to User Group");
					$logUserGroupUsersXML->addChild('Details', 'Users '.$usernamesString.' were successfully added to New User Group "'.$userGroupName.'".');
					$userGroupsUsersInsertedRowsXML = $logUserGroupUsersXML->addChild('Inserted_Rows');
					$userGroupsUsersInsertedRowsXML->addAttribute('table', 'user_group_users');
					
					foreach ($userData as $key => $user) {
						$userGroupRowXML = $userGroupsUsersInsertedRowsXML->addChild('Row');
						//$userGroupRowXML->addChild('user_group_id', $user['user_group_id']);
						$userGroupRowXML->addChild('user_id', $user['user_id']);
						$usernameQuery = $db->prepare('SELECT user_name FROM users WHERE user_id = :userId');
						$usernameQuery->bindParam(':userId',$user['user_id'],PDO::PARAM_STR);
						$usernameQuery->execute();
						$username = ($usernameQuery->fetch(PDO::FETCH_ASSOC))['user_name'];
						$userGroupRowXML->addChild('user_name', $username);
						$userGroupRowXML->addChild('last_updated_by_id', $user['last_updated_by_id']);
						$userGroupRowXML->addChild('timestamp', $user['timestamp']);
					}
					$logUserGroupUsersXML->addChild('Count', sizeOf($userData));
					
					$changeLogMessage = 'Created new User Group "'.$userGroupName.'" populated by User IDs "'.implode(", ", $userIDs).'".';
					
					$changeLogSQL = 
						"INSERT INTO database_change_log ".
						"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
						"VALUES (:currentUserID, :timestamp, 'User Group Creation', :message, 'user_groups, user_group_users');";
					$changeLogQuery = $db->prepare($changeLogSQL);
					$changeLogQuery->bindParam(':currentUserID', $currentUserID, PDO::PARAM_STR);
					$changeLogQuery->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
					$changeLogQuery->bindParam(':message', $changeLogMessage, PDO::PARAM_STR);
					$changeLogQuery->execute();
				}
				
				$end = microtime(true);
				
				$changeLogXML = $resultXML->addChild('Change_Log');
				$changeLogXML->addChild('Status', 'Transaction Logged');
				$changeDetailsXML = $changeLogXML->addChild('Details');
				$changeDetailsXML->addChild('timestamp', $timestamp);
				$changeDetailsXML->addChild('type_of_change', 'User Group Creation');
				$changeDetailsXML->addChild('action_taken', $changeLogMessage);
				$changeDetailsXML->addChild('affected_tables', 'user_groups, user_group_users');
				
				$evaluationXML = $queryXML->addChild('Evaluation');
				$evaluationXML->addChild('Time', round((($end - $start) * 1000), 3).'ms');
				$evaluationXML->addChild('Total_Number_of_Altered_Rows', 1 + sizeOf($userData));
				
				//echo "New User Group '".$userGroupName."' Successfully Created.<br/>";
				//echo 'Database User Group Id = '.$newUserGroupId.'<br/>';
				//echo 'Users added to UserGroup.<br/>';
				//echo "Transaction Logged";
			
			$db->commit();
		}
		catch (\PDOException $e) {
			$errorXML = $queryXML->addChild('Error');
			if ($logUserGroupQuery->errorCode() != 0) {
				$logUserGroupXML = $actionsXML->addChild('Action');
				$logUserGroupXML->addAttribute('type', 'Log User Group');
				$logUserGroupXML->addChild('Status', "Failed to Create User Group");
				if ($logUserGroupQuery->errorCode() == 23502) { //not_null_violation
					$errorDescription = 'User Group Name cannot be empty.';
				}
				else if ($logUserGroupQuery->errorCode() == 23503) {  //foreign_key_violation
					$errorDescription = 'You are not able to create user groups.';
				}
				else if ($logUserGroupQuery->errorCode() == 23505) {  //unique_violation
					$errorDescription = 'User Group Name "'.$userGroupName.'" already exists. Enter a differnt value for user group name.';
				}
				else {
					$errorDescription = 'An Error occured when trying to Create User Group.';
				}
				$logUserGroupXML->addChild('Details', $errorDescription);
			}
			else if ($logUserGroupUsersQuery->errorCode() != 0) {
				$logUserGroupUsersXML = $actionsXML->addChild('Action');
				$logUserGroupUsersXML->addAttribute('type', 'Log User Group users');
				$logUserGroupUsersXML->addChild('Status', "Failed to add Users to User Group");
				
				$errorDescription = 'Failure to set users for User Group "'.$userGroupName.'". Transaction has been terminated.  Please try again.';
				
				$logUserGroupUsersXML->addChild('Details', $errorDescription);
			}
			else if ($changeLogQuery->errorCode() != 0) {
				$changeLogXML = $resultXML->addChild('Change_Log');
				$changeLogXML->addChild('Status', 'Failed to log Transaction');
				
				$errorDescription = 'Unable to Log Transaction due to Error, entire transaction has been Undone.';
				
				$changeLogXML->addChild('Details', $errorDescription);
			}
			$errorCode = $e->getCode();
			$errorMessage = $e->getMessage();
			
			$errorXML->addChild('Error_Code', $errorCode);
			$errorXML->addChild('Details', $errorMessage);
			
			$db->rollBack();
			
			try {
				$errorLogSQL = 
					"INSERT INTO error_log ".
					"(user_id, description, error_code, error_message, exception_type, timestamp) ".
					"VALUES (:userID, :description, :errorCode, :errorMessage, 'PDO Query', :timestamp);";
				$errorLogQuery = $db->prepare($errorLogSQL);
				$errorLogQuery->bindParam(':userID', $currentUserID, PDO::PARAM_STR);
				$errorLogQuery->bindParam(':description', $errorDescription, PDO::PARAM_STR);
				$errorLogQuery->bindParam(':errorCode', $errorCode, PDO::PARAM_STR);
				$errorLogQuery->bindParam(':errorMessage', $errorMessage, PDO::PARAM_STR);
				$errorLogQuery->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
				$errorLogQuery->execute();
				
				$errorLogXML = $errorXML->addChild('Error_Log');
				$errorLogXML->addChild('Status', 'Error Logged');
				$errorLogDetailsXML = $errorLogXML->addChild('Details');
				$errorLogDetailsXML->addChild('description', $errorDescription);
				$errorLogDetailsXML->addChild('error_code', $errorCode);
				$errorLogDetailsXML->addChild('error_message', $errorMessage);
				$errorLogDetailsXML->addChild('exception_type', 'PDO Query');  //Automated, Manual
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
		
		$errorDescription = "Failed to Connect to Database.";
		
		$errorArray = array(
			"user_id" => $currentUserID,
			"description" => $errorDescription,
			"error_code" => $e->getCode(),
			"error_message" => $e->getMessage(),
			"exception_type" => "PDO Connection",
			"timestamp" => $timestamp,
			"file_name" => $fileName,
			"file_directory" => $fileDirectory,
			"request_uri" => $requestURI
		);
		//print_r($errorArray);
		$connectionErrorsFile = fopen('C:\Bitnami\wappstack-7.3.9-0\apache2\htdocs\CASecure_MSP_ver_1.0\database\error\ConnectionErrors.csv', 'a');
		fputcsv($connectionErrorsFile, $errorArray);
		fclose($connectionErrorsFile);
		
		$errorLogXML = $connectErrorXML->addChild('Error_Log');
		$errorLogXML->addChild('Status', 'Error Logged');
		$errorLogDetailsXML = $errorLogXML->addChild('Details');
		$errorLogDetailsXML->addChild('description', $errorDescription);
		$errorLogDetailsXML->addChild('error_code', $e->getCode());
		$errorLogDetailsXML->addChild('error_message', $e->getMessage());
		$errorLogDetailsXML->addChild('exception_type', 'PDO Connection');
		$errorLogDetailsXML->addChild('timestamp', $timestamp);
		$errorLogDetailsXML->addChild('file_name', htmlspecialchars($fileName));
		$errorLogDetailsXML->addChild('file_directory', htmlspecialchars($fileDirectory));
		$errorLogDetailsXML->addChild('request_uri', htmlspecialchars($requestURI));
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