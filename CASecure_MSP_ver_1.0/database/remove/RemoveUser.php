<?php
	
	header('Content-type: application/xml');
	
	$timestamp = date("Y-m-d H:i:sO");
	//echo "\nTime = ".$timestamp."\n";//"<br />";
	
	$userId = $_GET['uid'];
	$updater = $_GET['cid'];
	
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
		
		$connectResultXML->addChild('Status', 'Connected');
		$connectResultXML->addChild('Message', "Database Connection Successful");
		$connectResultXML->addChild('Host', $db_host);
		$connectResultXML->addChild('Database', $db_name);
		
		try {
			$queryXML = $xml->addChild('Query');
			
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$db->beginTransaction();
				
				$metaXML = $queryXML->addChild('Meta');
				$metaXML->addChild('Name', "Remove User");
				$metaXML->addChild('Description', "This query Deletes specified User accounts from the database.");
				$paramsXML = $metaXML->addChild('Parameters');
				$param1XML = $paramsXML->addChild('Parameter');
				$param1XML->addChild('Name', 'Current User ID');
				$param1XML->addChild('URL', 'cid');
				$param1XML->addChild('Value', $updater);
				$param1XML->addChild('Description', 'The User ID of the current User');
				$param2XML = $paramsXML->addChild('Parameter');
				$param2XML->addChild('Name', 'User ID');
				$param2XML->addChild('URL', 'uid');
				$param2XML->addChild('Value',$userId);
				$param2XML->addChild('Description', 'The User ID of the User account that is to be deleted');
				$resultXML = $queryXML->addChild('Result');
				
				$start = microtime(true);
				
				$fetchUserNameSQL = 
					"SELECT user_name ".
					"FROM users ".
					"WHERE user_id = :userId;";
				$fetchUserNameQuery = $db->prepare($fetchUserNameSQL);
				$fetchUserNameQuery->bindParam(":userId", $userId, PDO::PARAM_STR);
				$fetchUserNameQuery->execute();
				$userName = ($fetchUserNameQuery->fetch(PDO::FETCH_ASSOC))['user_name'];
				//print_r($fetchUserNameQuery->fetch(PDO::FETCH_ASSOC));
				//echo "<br/><br/>";
				
				$actionsXML = $resultXML->addChild('Actions');
				
				$fetchEmailInfoSQL = 
					"SELECT email_id, email_address, undeliverable, priority ".
					"FROM email_info ".
					"WHERE user_id = :userId;";
				$fetchEmailInfoQuery = $db->prepare($fetchEmailInfoSQL);
				$fetchEmailInfoQuery->bindParam(":userId", $userId, PDO::PARAM_STR);
				$fetchEmailInfoQuery->execute();
				$emailInfoArray = $fetchEmailInfoQuery->fetchAll(PDO::FETCH_ASSOC);
				//print_r($emailInfoArray);
				//echo "<br/>";	
				$emailInfoCount = sizeOf($emailInfoArray);
				
				$deleteEmailInfoSQL = 
					"DELETE FROM email_info ".
					"WHERE user_id = :userId";
				$deleteEmailInfoQuery = $db->prepare($deleteEmailInfoSQL);
				$deleteEmailInfoQuery->bindParam(":userId", $userId, PDO::PARAM_STR);
				$deleteEmailInfoQuery->execute();
				
				$deleteEmailInfoXML = $actionsXML->addChild('Action');
				$deleteEmailInfoXML->addAttribute('type', 'Delete Email Info');
				$deleteEmailInfoXML->addChild('Status', 'User Email Addresses Deleted');
				$deleteEmailInfoXML->addChild('Details', 'Email Addresses for User '.$userId.' "'.$userName.'" have been Deleted');
				$emailInfoDeletedRowsXML = $deleteEmailInfoXML->addChild('Deleted_Rows');
				$emailInfoDeletedRowsXML->addAttribute('table', 'email_info');
				foreach($emailInfoArray as $emailInfo) {
					$emailInfoRowXML = $emailInfoDeletedRowsXML->addChild('Row');
					$emailInfoRowXML->addChild('email_id', $emailInfo['email_id']);
					$emailInfoRowXML->addChild('email_address', $emailInfo['email_address']);
					$emailInfoRowXML->addChild('undeliverable', (($emailInfo['undeliverable'] == true)?("true"):("false")));
					$emailInfoRowXML->addChild('priority', $emailInfo['priority']);
				}
				$deleteEmailInfoXML->addChild('Count', $emailInfoCount);
				
				$fetchUserGroupsSQL = 
					"SELECT ugu.user_group_id, ug.user_group_name, ugu.group_admin ".
					"FROM user_group_users ugu, user_groups ug ".
					"WHERE ugu.user_group_id = ug.user_group_id AND ugu.user_id = :userId;";
				$fetchUserGroupsQuery = $db->prepare($fetchUserGroupsSQL);
				$fetchUserGroupsQuery->bindParam(":userId", $userId, PDO::PARAM_STR);
				$fetchUserGroupsQuery->execute();
				$userGroupsArray = $fetchUserGroupsQuery->fetchAll(PDO::FETCH_ASSOC);
				//print_r($userGroupsArray);
				//echo "<br/>";
				$userGroupsCount = sizeOf($userGroupsArray);
				
				$deleteUserGroupsSQL = 
					"DELETE FROM user_group_users ".
					"WHERE user_id = :userId;";
				$deleteUserGroupsQuery = $db->prepare($deleteUserGroupsSQL);
				$deleteUserGroupsQuery->bindParam(":userId", $userId, PDO::PARAM_STR);
				$deleteUserGroupsQuery->execute();
				
				$deleteUserGroupsXML = $actionsXML->addChild('Action');
				$deleteUserGroupsXML->addAttribute('type', 'Delete User from User Groups');
				$deleteUserGroupsXML->addChild('Status', 'User Removed From all User Groups');
				$deleteUserGroupsXML->addChild('Details', 'User '.$userId.' "'.$userName.'" removed from User Groups that the User was a member of.');
				$userGroupUsersDeletedRowsXML = $deleteUserGroupsXML->addChild('Deleted_Rows');
				$userGroupUsersDeletedRowsXML->addAttribute('table', 'user_group_users');
				foreach($userGroupsArray as $userGroup) {
					$userGroupUserRowXML = $userGroupUsersDeletedRowsXML->addChild('Row');
					$userGroupUserRowXML->addChild('user_group_id', $userGroup['user_group_id']);
					$userGroupUserRowXML->addChild('user_group_name', $userGroup['user_group_name']);
					$userGroupUserRowXML->addChild('group_admin', (($userGroup['group_admin'] == true) ? "true" : "false"));
				}
				$deleteUserGroupsXML->addChild('Count', $userGroupsCount);
				
				$fetchUserDefaultsSQL = 
					"SELECT default_site, default_computer_group ".
					"FROM user_defaults ".
					"WHERE user_id = :userId;";
				$fetchUserDefaultsQuery = $db->prepare($fetchUserDefaultsSQL);
				$fetchUserDefaultsQuery->bindParam(":userId", $userId, PDO::PARAM_STR);
				$fetchUserDefaultsQuery->execute();
				$userDefaultsArray = $fetchUserDefaultsQuery->fetchALL(PDO::FETCH_ASSOC);
				//print_r($userDefaultsArray);
				//echo "<br/>";
				$userDefaultsCount = sizeOf($userDefaultsArray);
				
				$deleteUserDefaultsSQL = 
					"DELETE FROM user_defaults ".
					"WHERE user_id = :userId;";
				$deleteUserDefaultsQuery = $db->prepare($deleteUserDefaultsSQL);
				$deleteUserDefaultsQuery->bindParam(":userId", $userId, PDO::PARAM_STR);
				$deleteUserDefaultsQuery->execute();
				
				$deleteUserDefaultsXML = $actionsXML->addChild('Action');
				$deleteUserDefaultsXML->addAttribute('type', 'Delete User Defauts');
				$deleteUserDefaultsXML->addChild('Status', 'User Defaults Deleted');
				$deleteUserDefaultsXML->addChild('Details', 'Default Site and Computer Group for User '.$userId.' "'.$userName.'" have been Deleted.');
				$userDefaultsDeletedRowsXML = $deleteUserDefaultsXML->addChild('Deleted_Rows');
				$userDefaultsDeletedRowsXML->addAttribute('table', 'user_defaults');
				foreach($userDefaultsArray as $userDefaults) {
					$userDefaultRowXML = $userDefaultsDeletedRowsXML->addChild('Row');
					$userDefaultRowXML->addChild('default_site', $userDefaults['default_site']);
					$userDefaultRowXML->addChild('default_computer_group', $userDefaults['default_computer_group']);
				}
				$deleteUserDefaultsXML->addChild('Count', $userDefaultsCount);
				
				$fetchConsoleToPortalSQL = 
					"SELECT bigfix_user_name ".
					"FROM console_to_portal ".
					"WHERE user_id = :userId;";
				$fetchConsoleToPortalQuery = $db->prepare($fetchConsoleToPortalSQL);
				$fetchConsoleToPortalQuery->bindParam(":userId", $userId, PDO::PARAM_STR);
				$fetchConsoleToPortalQuery->execute();
				$consoleToPortalArray = $fetchConsoleToPortalQuery->fetchAll(PDO::FETCH_ASSOC);
				//print_r($consoleToPortalArray);
				//echo "<br/>";
				$consoleToPortalCount = sizeOf($consoleToPortalArray);
				
				$deleteConsoleToPortalSQL = 
					"DELETE FROM console_to_portal ".
					"WHERE user_id = :userId;";
				$deleteConsoleToPortalQuery = $db->prepare($deleteConsoleToPortalSQL);
				$deleteConsoleToPortalQuery->bindParam(":userId", $userId, PDO::PARAM_STR);
				$deleteConsoleToPortalQuery->execute();
				
				$deleteConsoleToPortalXML = $actionsXML->addChild('Action');
				$deleteConsoleToPortalXML->addAttribute('type', 'Delete Consol To Portal');
				$deleteConsoleToPortalXML->addChild('Status', 'BigFix Console Users Removed');
				$deleteConsoleToPortalXML->addChild('Details', 'BigFix Console User info for User '.$userId.' "'.$userName.'" has been Removed');
				$consoleToPortalDeletedRowsXML = $deleteConsoleToPortalXML->addChild('Deleted_Rows');
				$consoleToPortalDeletedRowsXML->addAttribute('table', 'console_to_portal');
				foreach($consoleToPortalArray as $consoleToPortal) {
					$consoleToPortalRowXML = $consoleToPortalDeletedRowsXML->addChild('Row');
					$consoleToPortalRowXML->addChild('bigfix_user_name', $consoleToPortal['bigfix_user_name']);
				}
				$deleteConsoleToPortalXML->addChild('Count', $consoleToPortalCount);
				
				$fetchUserSQL = 
					"SELECT user_name, password, welcome_name, is_admin ".
					"FROM users ".
					"WHERE user_id = :userId;";
				$fetchUserQuery = $db->prepare($fetchUserSQL);
				$fetchUserQuery->bindParam(":userId", $userId, PDO::PARAM_STR);
				$fetchUserQuery->execute();
				$userArray = $fetchUserQuery->fetchAll(PDO::FETCH_ASSOC);
				//print_r($userArray);
				//echo "<br/>";
				$userCount = sizeOf($userArray);
				
				$deleteUserSQL = 
					"DELETE FROM users ".
					"WHERE user_id = :userId;";
				$deleteUserQuery = $db->prepare($deleteUserSQL);
				$deleteUserQuery->bindParam(":userId", $userId, PDO::PARAM_STR);
				$deleteUserQuery->execute();
				//echo 'User "'.$userArray[0]['user_name'].'" has been permanently deleted. <br/>';
				
				$deleteUserXML = $actionsXML->addChild('Action');
				$deleteUserXML->addAttribute('type', 'Delete User');
				$deleteUserXML->addChild('Status', 'User Deleted');
				$deleteUserXML->addChild('Details', 'User account '.$userId.' "'.$userName.'" has been permanently Deleted');
				$userDeletedRowsXML = $deleteUserXML->addChild('Deleted_Rows');
				$userDeletedRowsXML->addAttribute('table', 'users');
				foreach($userArray as $user) {
					$userRowXML = $userDeletedRowsXML->addChild('Row');
					$userRowXML->addChild('user_name', $user['user_name']);
					$userRowXML->addChild('password', $user['password']);
					$userRowXML->addChild('welcome_name', $user['welcome_name']);
					$userRowXML->addChild('is_admin', (($user['is_admin'] == true) ? "true" : "false"));
				}
				$deleteUserXML->addChild('Count', $userCount);
				
				$message = 'Removed user '.$userId.' from the database. ';
				$message .= '[users] = (';
				foreach($userArray as $userIndex => $user) {
					if ($userIndex != 0) {
						$message .= ', ';
					}
					$message .= '[user_name] = "'.$user['user_name'].'", [password] = "'.$user['password'].'", [welcome_name] = "'.$user['welcome_name'].'", [is_admin] = '.(($user['is_admin'] == true)?("true"):("false"));
				}
				$message .= '), ';
				$message .= '[console_to_portal] = (';
				foreach($consoleToPortalArray as $consoleToPortalIndex => $consoleToPortal) {
					if ($consoleToPortalIndex != 0) {
						$message .= ', ';
					}
					$message .= '[bigfix_user_name] = "'.$consoleToPortal['bigfix_user_name'].'"';
				}
				$message .= '), ';
				$message .= '[user_defaults] = (';
				foreach($userDefaultsArray as $userDefaultIndex => $userDefault) {
					if ($userDefaultIndex != 0) {
						$message .= ', ';
					}
					$message .= '[default_site] = "'.$userDefault['default_site'].'", [default_computer_group] = "'.$userDefault['default_computer_group'].'"';
				}
				$message .= '), ';
				$message .= '[user_group_users] = (';
				foreach($userGroupsArray as $userGroupIndex => $userGroup) {
					if ($userGroupIndex != 0) {
						$message .= ', ';
					}
					$message .= '([user_group_id] = '.$userGroup['user_group_id'].', [user_group_name] = "'.$userGroup['user_group_name'].'", [group_admin] = '.(($userGroup['group_admin'] == true)?("true"):("false")).')';
				}
				$message .= '), ';
				$message .= '[email_info] = (';
				foreach($emailInfoArray as $emailIndex => $emailInfo) {
					if ($emailIndex != 0) {
						$message .= ', ';
					}
					$message .= '([email_id] = '.$emailInfo['email_id'].', [email_address] = "'.$emailInfo['email_address'].'", [undeliverable] = '.(($emailInfo['undeliverable'] == true)?("true"):("false")).', [priority] = '.$emailInfo['priority'].')';
				}
				$message .= ')';
				//echo $message."<br/>";
				
				$changeLogSQL = 
					"INSERT INTO database_change_log ".
					"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
					"VALUES (:updater, :timestamp, 'Deleted User', :message, 'users, console_to_portal, user_defaults, user_group_users, email_info');";
				$changeLogQuery = $db->prepare($changeLogSQL);
				$changeLogQuery->bindParam(":updater", $updater , PDO::PARAM_STR);
				$changeLogQuery->bindParam(":timestamp", $timestamp, PDO::PARAM_STR);
				$changeLogQuery->bindParam(":message", $message, PDO::PARAM_STR);
				$changeLogQuery->execute();
				//echo "Transaction Logged.<br/>";
				
				$end = microtime(true);
				
				$changeLogXML = $resultXML->addChild('Change_Log');
				$changeLogXML->addChild('Status', 'Transaction Logged');
				$changeDetails = $changeLogXML->addChild('Details');
				$changeDetails->addChild('timestamp', $timestamp);
				$changeDetails->addChild('type_of_change', 'Deleted User Group');
				$changeDetails->addChild('action_taken', $message);
				$changeDetails->addChild('affected_tables', 'user_groups, user_group_users, alerts');
				
				$evaluationXML = $queryXML->addChild('Evaluation');
				$evaluationXML->addChild('Time', round((($end - $start) * 1000), 3).'ms');
				$evaluationXML->addChild('Total_Number_of_Altered_Rows', ($emailInfoCount + $userGroupsCount + $userDefaultsCount + $consoleToPortalCount + $userCount));
				
			$db->commit();
		}
		catch (\PDOException $e) {
			$errorXML = $queryXML->addChild('Error');
			//$errorXML->addChild('Code', $fetchAllAlertsQuery->errorCode());
			$errorXML->addChild('Error_Code', $e->getCode());
			$errorXML->addChild('Details', $e->getMessage());
			$db->rollBack();
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
	
	echo $xml->asXML();

	
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
		//print_r($rowsSQL);
		//echo "<br/>";
		$sql = "INSERT INTO ".$tableName." (".implode(", ", $columnNames).") VALUES ".implode(", ", $rowsSQL);
		
		$pdoStatement = $pdoObject->prepare($sql);
		
		foreach($toBind as $param => $val) {
			$pdoStatement->bindValue($param, $val); //bindParam
		}
		
		return $pdoStatement;//->execute();
	}
	
	function pdoMultiDelete($tableName, $data, $pdoObject) { //$dataFilters,
		$rowSQL = array();
		
		$toBind = array();
		//print_r($data);
		//echo "<br/>";
		$columnNames = array_keys($data[0]);
		//print_r($columnNames);
		//echo "<br/>";
		
		
		$sql = "DELETE FROM ".$tableName." WHERE ";
		
		foreach($columnNames as $key => $columnName) {
			if($key != 0) {
				$sql .= " AND ";
			}
			$columnArray = array();
			$columnArray = array_column($data, $columnName);
			print_r($columnArray);
			echo "<br/>";
			$columnArrayLength = sizeof($columnArray);
			echo $columnArrayLength."<br/>";
			$sql .= $columnName;
			if ($columnArrayLength == 1) {
				$sql .= " = ";
			}
			else {
				$sql .= " IN (";
			}
			foreach($columnArray as $arrayIndex => $columnValue) {
				if($arrayIndex != 0) {
					$sql .= ", ";
				}
				$param = ":".$columnName.$arrayIndex;
				$sql .= $param;
				$toBind[$param] = $columnValue;
			}
			if ($columnArrayLength > 1) {
				$sql .= ")";
			}
		}
		$sql .= ";";
		echo $sql;
		echo "<br/>";
		//print_r($toBind);
		//echo "<br/>";
		
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