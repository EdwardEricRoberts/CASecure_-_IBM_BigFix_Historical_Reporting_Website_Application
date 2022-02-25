<?php
	
	header('Content-type: application/xml');
	
	$timestamp = date("Y-m-d H:i:sO");
	//echo "\nTime = ".$timestamp."\n";//"<br />";
	
	$issuer = $_GET['cid'];
	$userGroupIDs = json_decode($_GET['ugids']);
	$title = $_GET['title'];
	$message = $_GET['mess'];
	$expiration = $_GET['exp'];
	$priorityColor = $_GET['col'];
	
	if (sizeof($userGroupIDs) == 0) {
		$userGroupIDs = array(0);
	}
	//print_r($userGroupIDs);
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
				$metaXML->addChild('Name', "Create Alert");
				$metaXML->addChild('Description', "This query Logs a new Alert submitted by the Current User.");
				$paramsXML = $metaXML->addChild('Parameters');
				$param1XML = $paramsXML->addChild('Parameter');
				$param1XML->addChild('Name', 'Current User ID');
				$param1XML->addChild('URL', 'cid');
				$param1XML->addChild('Value', $issuer);
				$param1XML->addChild('Description', 'The User ID of the current User');
				$param2XML = $paramsXML->addChild('Parameter');
				$param2XML->addChild('Name', 'User Group IDs');
				$param2XML->addChild('URL', 'ugids');
				$param2XML->addChild('Value',"[".join(", ", $userGroupIDs)."]");
				$param2XML->addChild('Description', 'Array of User Group IDs of the selected User Groups that the Alert will apply to.');
				$param3XML = $paramsXML->addChild('Parameter');
				$param3XML->addChild('Name', 'Alert Title');
				$param3XML->addChild('URL', 'title');
				$param3XML->addChild('Value',$title);
				$param3XML->addChild('Description', 'Title Information of the Alert being Created.');
				$param4XML = $paramsXML->addChild('Parameter');
				$param4XML->addChild('Name', 'Alert Message');
				$param4XML->addChild('URL', 'mess');
				$param4XML->addChild('Value',$message);
				$param4XML->addChild('Description', 'Message Information of the Alert being Created.');
				$param5XML = $paramsXML->addChild('Parameter');
				$param5XML->addChild('Name', 'Alert Exiration Date');
				$param5XML->addChild('URL', 'exp');
				$param5XML->addChild('Value',$expiration);
				$param5XML->addChild('Description', 'The Expiration Date of the Alert being Created');
				$param5XML = $paramsXML->addChild('Parameter');
				$param5XML->addChild('Name', 'Priority Color ID');
				$param5XML->addChild('URL', 'col');
				$param5XML->addChild('Value',$priorityColor);
				$param5XML->addChild('Description', 'The ID of the priority that this alert will be color coded to. (optional)');
				$resultXML = $queryXML->addChild('Result');
				
				$start = microtime(true);
				//
				$actionsXML = $resultXML->addChild('Actions');
				
				$messageSQL = ($message == "")?(null):($message);
				$expirationSQL = ($expiration == "")?(null):($expiration);
				
				$logAlertSQL = 
					"INSERT INTO ALERTS ".
					"(issued_by_id, title, message, timestamp, expiration, active, priority_color) ".
					"VALUES (:issuer, :title, :message, :timestamp, :expiration, TRUE, :priorityColor);";
				$logAlertQuery = $db->prepare($logAlertSQL);
				$logAlertQuery->bindParam(":issuer", $issuer, PDO::PARAM_STR);
				$logAlertQuery->bindParam(":title", $title, PDO::PARAM_STR);
				$logAlertQuery->bindParam(":message", $messageSQL, PDO::PARAM_STR);
				$logAlertQuery->bindParam(":timestamp", $timestamp, PDO::PARAM_STR);
				$logAlertQuery->bindParam(":expiration", $expirationSQL, PDO::PARAM_STR);
				$logAlertQuery->bindParam(":priorityColor", $priorityColor, PDO::PARAM_STR);
				$logAlertQuery->execute();
				
				$newAlertId = $db->lastInsertId();
				
				$priorityColorDataSQL =
					"SELECT priority_name, color_name ".
					"FROM alert_priorities ".
					"WHERE priority_id = :priorityColor;";
				$priorityColorDataQuery = $db->prepare($priorityColorDataSQL);
				$priorityColorDataQuery->bindParam(':priorityColor', $priorityColor, PDO::PARAM_STR);
				$priorityColorDataQuery->execute();
				$priority = $priorityColorDataQuery->fetch(PDO::FETCH_ASSOC);
				$priorityName = $priority['priority_name'];
				$priorityColorName = $priority['color_name'];
				
				
				$logAlertXML = $actionsXML->addChild('Action');
				$logAlertXML->addAttribute('type', 'Log Alert');
				$logAlertXML->addChild('Status', "Alert Created");
				$logAlertXML->addChild('Details', 'New Alert "'.$title.': '.$message.'" was successfully created.');
				
				$alertRowXML = $logAlertXML->addChild('Alert');
				$alertRowXML->addAttribute('table', 'alerts');
				$alertRowXML->addChild('alert_id', $newAlertId);
				$alertRowXML->addChild('title', $title);
				$alertRowXML->addChild('message', $message);
				$alertRowXML->addChild('timestamp', $timestamp);
				$alertRowXML->addChild('expiration', $expiration);
				$alertRowXML->addChild('issued_by_id', $issuer);
				$alertRowXML->addChild('priority_value', $priorityName);
				$alertRowXML->addChild('priority_color', $priorityColorName);
				
				$logAlertXML->addChild('Count', 1);
				
				
				if($userGroupIDs != array(0)){ 
					
					for ($i = 0; $i < sizeof($userGroupIDs); $i++) {
						$userGroupAlertData[$i] =
							array(
								'user_group_id' => $userGroupIDs[$i], 
								'alert_id' => $newAlertId,
								'active_for_group' => TRUE 
							);
					}
					
					//print_r(userGroupAlertData);
					
					$logUserGroupsQuery = pdoMultiInsert('user_group_alerts', $userGroupAlertData, $db);
					$logUserGroupsQuery->execute();
					
					$userGroupInfoSQL = 
						"SELECT user_group_id, user_group_name ".
						"FROM user_groups ".
						"WHERE user_group_id IN (".join(", ", array_map(function() {return "?";}, $userGroupIDs)).");";
					$userGroupInfoQuery = $db->prepare($userGroupInfoSQL);
					$userGroupInfoQuery->execute($userGroupIDs);
					$userGroupsArray = $userGroupInfoQuery->fetchAll(PDO::FETCH_ASSOC);
					
					$logAlertUserGroupsXML = $actionsXML->addChild('Action');
					$logAlertUserGroupsXML->addAttribute('type', 'Log Alert User Groups');
					$logAlertUserGroupsXML->addChild('Status', "Success");
					$logAlertUserGroupsXML->addChild('Details', 'Associated User Groups "'.join(", ", array_map(function($row) {return $row['user_group_name'];}, $userGroupsArray)).'" logged for Alert "'.$title.': '.$message.'".');
					$userGroupsInsertedRowsXML = $logAlertUserGroupsXML->addChild('User_Groups');
					$userGroupsInsertedRowsXML->addAttribute('table', 'user_group_alerts');
					foreach ($userGroupsArray as $userGroup) {
						$userGroupXML = $userGroupsInsertedRowsXML->addChild('user_group');
						$userGroupXML->addChild('user_group_id', $userGroup['user_group_id']);
						$userGroupXML->addChild('user_group_name', $userGroup['user_group_name']);
					}
					$logAlertUserGroupsXML->addChild('Count', sizeOf($userGroupIDs));
					
					$usersSQL = 
						"SELECT DISTINCT user_id ".
						"FROM user_group_users ".
						"WHERE user_group_id IN (";
					foreach ($userGroupIDs as $key => $userGroupID) {
						if ($key != 0) {
							$usersSQL .= ", ";
						}
						$usersSQL .= "?";
					}
					$usersSQL .= ")";
					
					$usersQuery = $db->prepare($usersSQL);
					$usersQuery->execute($userGroupIDs);
					$userIDs = array_column($usersQuery->fetchAll(PDO::FETCH_ASSOC), "user_id");
					//print_r($userIDs);
					
					$userAlertData = [];
					foreach ($userIDs as $key => $userId) {
						$userAlertData[] = 
							array(
								'user_id' => $userId, 
								'alert_id' => $newAlertId, 
								'active_for_user' => TRUE 
							);
					}
					
					$logUsersQuery = pdoMultiInsert('user_alerts', $userAlertData, $db);
					$logUsersQuery->execute();
					
					$userInfoSQL = 
						"SELECT user_id, user_name, welcome_name ".
						"FROM users ".
						"WHERE user_id IN (".join(", ", array_map(function() {return "?";}, $userIDs)).");";
					$userInfoQuery = $db->prepare($userInfoSQL);
					$userInfoQuery->execute($userIDs);
					$usersArray = $userInfoQuery->fetchAll(PDO::FETCH_ASSOC);
					
					$logAlertUsersXML = $actionsXML->addChild('Action');
					$logAlertUsersXML->addAttribute('type', 'Log Alert Users');
					$logAlertUsersXML->addChild('Status', "Success");
					$logAlertUsersXML->addChild('Details', 'Associated Users "'.join(", ", array_map(function($row) {return $row['user_name'];}, $usersArray)).'" logged for Alert "'.$title.': '.$message.'".');
					$usersInsertedRowsXML = $logAlertUsersXML->addChild('Users');
					$usersInsertedRowsXML->addAttribute('table', 'user_alerts');
					foreach ($usersArray as $user) {
						$userGroupXML = $usersInsertedRowsXML->addChild('user');
						$userGroupXML->addChild('user_id', $user['user_id']);
						$userGroupXML->addChild('user_name', $user['user_name']);
						$userGroupXML->addChild('welcome_name', $user['welcome_name']);
					}
					$logAlertUsersXML->addChild('Count', sizeOf($userIDs));
				}
				
				$issuerUserNameSQL = 
					"SELECT user_name ".
					"FROM users ".
					"WHERE user_id = :issuer;";
				$issuerUserNameQuery = $db->prepare($issuerUserNameSQL);
				$issuerUserNameQuery->bindParam(":issuer", $issuer, PDO::PARAM_STR);
				$issuerUserNameQuery->execute();
				$issuerUserName = ($issuerUserNameQuery->fetch(PDO::FETCH_ASSOC))['user_name'];
				
				$changeLogMessage = 'New Alert "'.$title.': '.$message.'" created by user "'.$issuerUserName.'" for User Groups "'.join(", ", array_map(function($row) {return $row['user_group_name'];}, $userGroupsArray)).'" and Users "'.join(", ", array_map(function($row) {return $row['user_name'];}, $usersArray)).'".';
				
				$changeLogSQL = 
					"INSERT INTO database_change_log ".
					"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
					"VALUES (:issuer, :timestamp, 'Alert Creation', :message, 'alerts, user_group_alerts, user_alerts');";
				$changeLogQuery = $db->prepare($changeLogSQL);
				$changeLogQuery->bindParam(':issuer', $issuer, PDO::PARAM_STR);
				$changeLogQuery->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
				$changeLogQuery->bindParam(':message', $changeLogMessage, PDO::PARAM_STR);
				$changeLogQuery->execute();
				
				$end = microtime(true);
				
				$changeLogXML = $resultXML->addChild('Change_Log');
				$changeLogXML->addChild('Status', 'Transaction Logged');
				$changeDetailsXML = $changeLogXML->addChild('Details');
				$changeDetailsXML->addChild('timestamp', $timestamp);
				$changeDetailsXML->addChild('type_of_change', 'Alert Creation');
				$changeDetailsXML->addChild('action_taken', $changeLogMessage);
				$changeDetailsXML->addChild('affected_tables', 'alerts, user_group_alerts, user_alerts');
				
				$evaluationXML = $queryXML->addChild('Evaluation');
				$evaluationXML->addChild('Time', round((($end - $start) * 1000), 3).'ms');
				$evaluationXML->addChild('Total_Number_of_Altered_Rows', (1 + sizeOf($userGroupIDs) + sizeOf($userIDs)));
				
			$db->commit();
		}
		catch (\PDOException $e) {
			$errorXML = $queryXML->addChild('Error');
			
			if ($logAlertQuery->errorCode() != 0) {
				$logAlertXML = $actionsXML->addChild('Action');
				$logAlertXML->addAttribute('type', 'Log Alert');
				$logAlertXML->addChild('Status', "Failed to Create Alert");
				if ($logAlertQuery->errorCode() == 23502) {  // not_null_violation	
					$errorDescription = 'Alert Title cannot be empty.';
				}
				else if ($logAlertQuery->errorCode() == 23503) {  // foreign_key_violation
					$errorDescription = 'Selected User Group Does Not Exist.';
				}
				else {
					$errorDescription = 'An Error occured when trying to Create Alert.';
				}
				$logAlertXML->addChild('Details', $errorDescription);
			}
			else if ($priorityColorDataQuery->errorCode() != 0) {
				$logAlertXML = $actionsXML->addChild('Action');
				$logAlertXML->addAttribute('type', 'Log Alert');
				$logAlertXML->addChild('Status', "Failed to Create Alert");
				$errorDescription = 'An Error occured when trying to Create Alert.';
				$logAlertXML->addChild('Details', $errorDescription);
			}
			else if ($logUserGroupsQuery->errorCode() != 0) {
				$logAlertUserGroupsXML = $actionsXML->addChild('Action');
				$logAlertUserGroupsXML->addAttribute('type', 'Log Alert User Groups');
				$logAlertUserGroupsXML->addChild('Status', "Failure");
				$errorDescription = 'An Error occured when trying to Log User Groups.';
				$logAlertUserGroupsXML->addChild('Details', $errorDescription);
			}
			else if ($userGroupInfoQuery->errorCode() != 0) {
				$logAlertUserGroupsXML = $actionsXML->addChild('Action');
				$logAlertUserGroupsXML->addAttribute('type', 'Log Alert User Groups');
				$logAlertUserGroupsXML->addChild('Status', "Failure");
				$errorDescription = 'An Error occured when trying to Log User Groups.';
				$logAlertUserGroupsXML->addChild('Details', $errorDescription);
			}
			else if ($usersQuery->errorCode() != 0) {
				$logAlertUsersXML = $actionsXML->addChild('Action');
				$logAlertUsersXML->addAttribute('type', 'Log Alert Users');
				$logAlertUsersXML->addChild('Status', "Failure");
				$errorDescription = 'An Error occured when trying to Log Users.';
				$logAlertUsersXML->addChild('Details', $errorDescription);
			}
			else if ($logUsersQuery->errorCode() != 0) {
				$logAlertUsersXML = $actionsXML->addChild('Action');
				$logAlertUsersXML->addAttribute('type', 'Log Alert Users');
				$logAlertUsersXML->addChild('Status', "Failure");
				$errorDescription = 'An Error occured when trying to Log Users.';
				$logAlertUsersXML->addChild('Details', $errorDescription);
			}
			else if ($userInfoQuery->errorCode() != 0) {
				$logAlertUsersXML = $actionsXML->addChild('Action');
				$logAlertUsersXML->addAttribute('type', 'Log Alert Users');
				$logAlertUsersXML->addChild('Status', "Failure");
				$errorDescription = 'An Error occured when trying to Log Users.';
				$logAlertUsersXML->addChild('Details', $errorDescription);
			}
			else if ($issuerUserNameQuery->errorCode != 0) {
				$changeLogXML = $resultXML->addChild('Change_Log');
				$changeLogXML->addChild('Status', 'Failed to log Transaction');
				$errorDescription = 'Unable to Log Transaction due to Error, entire transaction has been Undone.';
				$changeLogXML->addChild('Details', $errorDescription);
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
				$errorLogQuery->bindParam(':userID', $issuer, PDO::PARAM_STR);
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
			//throw $e;
			
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