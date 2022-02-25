<?php
	
	header('Content-type: application/xml');
	
	$timestamp = date("Y-m-d H:i:sO");
	//echo "\nTime = ".$timestamp."\n";//"<br />";
	
	$alertId = $_GET['aid'];
	$userId = $_GET['uid'];
	$updater = $_GET['cid'];
	
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
				$metaXML->addChild('Name', "Remove Alert for User");
				$metaXML->addChild('Description', "This query Deactivates a specified Alert for specified User.");
				$paramsXML = $metaXML->addChild('Parameters');
				$param1XML = $paramsXML->addChild('Parameter');
				$param1XML->addChild('Name', 'Current User ID');
				$param1XML->addChild('URL', 'cid');
				$param1XML->addChild('Value', $updater);
				$param1XML->addChild('Description', 'The User ID of the current User');
				$param2XML = $paramsXML->addChild('Parameter');
				$param2XML->addChild('Name', 'Alert ID');
				$param2XML->addChild('URL', 'aid');
				$param2XML->addChild('Value',$alertId);
				$param2XML->addChild('Description', 'The Alert ID of the selected Alert');
				$param3XML = $paramsXML->addChild('Parameter');
				$param3XML->addChild('Name', 'User ID');
				$param3XML->addChild('URL', 'uid');
				$param3XML->addChild('Value',$userId);
				$param3XML->addChild('Description', 'The User ID of the selected User');
				$resultXML = $queryXML->addChild('Result');
				
				
				$start = microtime(true);
				//
				$fetchAlertsSQL = 
					"SELECT a.alert_id, a.title, a.message, a.timestamp, a.expiration, a.issued_by_id, ".
						"u.user_id, u.user_name, u.welcome_name, ua.active_for_user ".
					"FROM alerts a, user_alerts ua, users u ".
					"WHERE a.alert_id = ua.alert_id AND ".
						"u.user_id = ua.user_id AND ".
						"ua.active_for_user = TRUE AND ".
						"ua.alert_id = :alertId AND ".
						"ua.user_id = :userId;";
				$fetchAlertsQuery = $db->prepare($fetchAlertsSQL);
				$fetchAlertsQuery->bindParam(":alertId", $alertId, PDO::PARAM_STR);
				$fetchAlertsQuery->bindParam(":userId", $userId, PDO::PARAM_STR);
				$fetchAlertsQuery->execute();
				$alertsArray = $fetchAlertsQuery->fetch(PDO::FETCH_ASSOC);
				$alertTitle = $alertsArray['title'];
				$alertMessage = $alertsArray['message'];
				$alertTimestamp = $alertsArray['timestamp'];
				$alertExpiration = $alertsArray['expiration'];
				$alertIssuedById = $alertsArray['issued_by_id'];
				$userName = $alertsArray['user_name'];
				$welcomeName = $alertsArray['welcome_name'];
				//
				//print_r($alertsArray);
				//echo "<br/>";
				//echo $alertTitle."<br/>";
				//echo $alertMessage."<br/>";
				//echo $alertTimestamp."<br/>";
				//echo $alertExpiration."<br/>";
				
				$actionsXML = $resultXML->addChild('Actions');
				//
				$deactivateUserAlertSQL = 
					"UPDATE user_alerts ".
					"SET active_for_user = FALSE ".
					"WHERE active_for_user = TRUE AND ".
						"alert_id = :alertId AND ".
						"user_id = :userId";
				$deactivateUserAlertQuery = $db->prepare($deactivateUserAlertSQL);
				$deactivateUserAlertQuery->bindParam(":alertId", $alertId, PDO::PARAM_STR);
				$deactivateUserAlertQuery->bindParam(":userId", $userId, PDO::PARAM_STR);
				$deactivateUserAlertQuery->execute();
				
				//$resultXML->addChild('Status', 'Alert Deactived');
				//$resultXML->addChild('Details', 'Alert "'.$alertTitle.'" for User Group "'.$userGroupName.'" has been deactivated.');
				$deactivateUserAlertXML = $actionsXML->addChild('Action');
				$deactivateUserAlertXML->addAttribute('type', 'Deactivate Alert for User');
				$deactivateUserAlertXML->addChild('Status', "Alert Deactivated");
				$deactivateUserAlertXML->addChild('Details', 'Alert "'.$alertTitle.'" has been Deactivated for User "'.$userName.'".');
				$deactivateUserAlertRowsXML = $deactivateUserAlertXML->addChild('Deleted_rows');
				$deactivateUserAlertRowsXML->addAttribute('table', 'alerts');
				
				//$deletedRowsXML = $resultXML->addChild('Deleted_Rows');
				//$row = $deletedRowsXML->addChild('Row');
				//$alertXML = $row->addChild('alert');
				//$alertXML->addChild('alert_id', $alertId);
				//$alertXML->addChild('title', $alertTitle);
				//$alertXML->addChild('message', $alertMessage);
				//$alertXML->addChild('user_group_id', $userGroupID);
				//$alertXML->addChild('user_group_name', $userGroupName);
				//$alertXML->addChild('timestamp', $alertTimestamp);
				//$alertXML->addChild('expiration', $alertExpiration);
				/*
				$alertRowXML = $alertsDeletedRowsXML->addChild('Row');
				$alertRowXML->addChild('alert_id', $alertId);
				$alertRowXML->addChild('title', $alertTitle);
				$alertRowXML->addChild('message', $alertMessage);
				$alertRowXML->addChild('user_group_id', $userGroupID);
				$alertRowXML->addChild('user_group_name', $userGroupName);
				$alertRowXML->addChild('timestamp', $alertTimestamp);
				$alertRowXML->addChild('expiration', $alertExpiration);
				$alertRowXML->addChild('issued_by_id', $alertIssuedById);
				
				$deactivateAlertsXML->addChild('Count', 1);
				*/
				
				$activeStatusCheckSQL =
					"SELECT uga.user_group_id, uga.active_for_group, ua.user_id, ua.active_for_user, ua.alert_id, a.active ".
					"FROM user_alerts AS ua ".
						"INNER JOIN user_group_users AS ugu ".
							"ON ua.user_id = ugu.user_id ".
						"INNER JOIN user_group_alerts AS uga ".
							"ON ua.alert_id = uga.alert_id ".
							"AND ugu.user_group_id = uga.user_group_id ".
						"INNER JOIN alerts AS a ".
							"ON ua.alert_id = a.alert_id ".
							"AND uga.alert_id = a.alert_id ".
					"WHERE ua.alert_id = :alertId ".
					"ORDER BY uga.user_group_id;";
				$activeStatusCheckQuery = $db->prepare($activeStatusCheckSQL);
				$activeStatusCheckQuery->bindParam(":alertId", $alertId, PDO::PARAM_STR);
				$activeStatusCheckQuery->execute();
				$activeStatusArray = $activeStatusCheckQuery->fetchAll(PDO::FETCH_ASSOC);
				//print_r($activeStatusArray);
				//echo "<br/><br/>";
				
				$currentUserGroup = "";
				$deactivatedUserGroupAlerts = [];
				foreach ($activeStatusArray as $groupUserCombo) {
					if ($groupUserCombo['user_group_id'] != $currentUserGroup && $groupUserCombo['active_for_group'] == TRUE) {
						$currentUserGroup = $groupUserCombo['user_group_id'];
						$userGroupRowKeys = array_keys(array_column($activeStatusArray, "user_group_id"), $currentUserGroup);
						//print_r($userGroupRowKeys);
						//echo "<br/>";
						$userGroupActiveStatusArray = array_slice($activeStatusArray, $userGroupRowKeys[array_key_first($userGroupRowKeys)], $userGroupRowKeys[array_key_last($userGroupRowKeys)] + 1);
						//print_r($userGroupActiveStatusArray);
						//echo "<br/>";
						$userStatusesArray = array_column($userGroupActiveStatusArray, "active_for_user");
						if (count(array_unique($userStatusesArray)) == 1 && end($userStatusesArray) == FALSE) {
							$deactivateUserGroupAlertSQL = 
								"UPDATE user_group_alerts ".
								"SET active_for_group = FALSE ".
								"WHERE user_group_id = :currentUserGroup AND alert_id = :alertId;";
							$deactivateUserGroupAlertQuery = $db->prepare($deactivateUserGroupAlertSQL);
							$deactivateUserGroupAlertQuery->bindParam(":currentUserGroup", $currentUserGroup, PDO::PARAM_STR);
							$deactivateUserGroupAlertQuery->bindParam(":alertId", $alertId, PDO::PARAM_STR);
							$deactivateUserGroupAlertQuery->execute();
							
							$deactivatedUserGroupAlerts[] = $currentUserGroup;
						}
						else {
							//echo "No changes to be made.";
						}
						//echo "<br/><br/>";
					}
				}
				if (sizeOf($deactivatedUserGroupAlerts) != 0) {
					$deactivatedUserGroupAlertNamesSQL = 
						"SELECT user_group_name ".
						"FROM user_groups ".
						"WHERE user_group_id IN (".join(", ", array_map(function() {return "?";}, $deactivatedUserGroupAlerts)).");";
					$deactivatedUserGroupAlertNamesQuery = $db->prepare($deactivatedUserGroupAlertNamesSQL);
					$deactivatedUserGroupAlertNamesQuery->execute($deactivatedUserGroupAlerts);
					$deactivatedUserGroupAlertNames = array_column($deactivatedUserGroupAlertNamesQuery->fetchAll(PDO::FETCH_ASSOC), "user_group_name");
					
					$deactivatedUserGroupAlertNamesString = implode(", ", $deactivatedUserGroupAlertNames);
					
					$deactivatedUserGroupAlertsString = implode(", ", $deactivatedUserGroupAlerts);
					$userGroupsAlertChangeLogMessage = 'Alert '.$alertId.' "'.$alertTitle.'" has been deactivated for User Group'.((sizeOf($deactivatedUserGroupAlerts) != 1)?"s":"").' "'.$deactivatedUserGroupAlertNamesString.'".';
					
					$deactivateUserGroupAlertXML = $actionsXML->addChild('Action');
					$deactivateUserGroupAlertXML->addAttribute('type', 'Deactivate Alert for User Group');
					$deactivateUserGroupAlertXML->addChild('Status', "User Group Alert Deactivated");
					$deactivateUserGroupAlertXML->addChild('Details', 'Alert "'.$alertTitle.'" has been deactivated for User Group'.((sizeOf($deactivatedUserGroupAlerts) != 1)?"s":"").' "'.$deactivatedUserGroupAlertNamesString.'".');
					//$deactivateUserGroupAlertXML->
					$deactivateUserGroupAlertXML->addChild('Count', sizeOf($deactivatedUserGroupAlerts));
				}
				
				$newActiveStatusCheckQuery = $db->prepare($activeStatusCheckSQL);
				$newActiveStatusCheckQuery->bindParam(":alertId", $alertId, PDO::PARAM_STR);
				$newActiveStatusCheckQuery->execute();
				$newActiveStatusArray = $newActiveStatusCheckQuery->fetchAll(PDO::FETCH_ASSOC);
				
				$currentUserGroup = "";
				$userGroupActiveStatusArray = [];
				$alertDeactivated = FALSE;
				if ($newActiveStatusArray != $activeStatusArray) {
					foreach ($newActiveStatusArray as $groupUserCombo) {
						if ($groupUserCombo['user_group_id'] != $currentUserGroup){
							$currentUserGroup = $groupUserCombo['user_group_id'];
							$userGroupActiveStatusArray[] = 
								array(
									'user_group_id' => $groupUserCombo['user_group_id'],
									'active_for_group' => $groupUserCombo['active_for_group'],
									'alert_id' => $groupUserCombo['alert_id'],
									'active' => $groupUserCombo['active']
								);
						}
					}
					//print_r($userGroupActiveStatusArray);
					//echo "<br/><br/>";
					if (sizeOf($userGroupActiveStatusArray) != 0) {
						$userGroupStatusesArray = array_column($userGroupActiveStatusArray, "active_for_group");
						//print_r($userGroupStatusesArray);
						if (count(array_unique($userGroupStatusesArray)) == 1 && end($userGroupStatusesArray) == FALSE) {
							$alertDeactivated = TRUE;
							$deactivateAlertSQL = 
								"UPDATE alerts ".
								"SET active = FALSE ".
								"WHERE alert_id = :alertId;";
							$deactivateAlertQuery = $db->prepare($deactivateAlertSQL);
							$deactivateAlertQuery->bindParam(":alertId", $alertId, PDO::PARAM_STR);
							$deactivateAlertQuery->execute();
							
							$deactivateAlertXML = $actionsXML->addChild('Action');
							$deactivateAlertXML->addAttribute('type', 'Deactivate Alert');
							$deactivateAlertXML->addChild('Status', "Alert Deactivated");
							$deactivateAlertXML->addChild('Details', 'Alert "'.$alertTitle.'" has been deactivated.');
							//$deactivateAlertXML->
							
							
						}
						else {
							//echo "No changes to be made.";
						}
					}
				}
				else {
					//echo "Arrays are the Same.";
				}
				
				$changeLogsXML = $resultXML->addChild('Change_Logs');
				
				$userAlertChangeLogMessage = 'Alert '.$alertId.' "'.$alertTitle.'" has been Deactivated for User '.$userId.' "'.$userName.'".';
				
				$userAlertChangeLogSQL = 
					"INSERT INTO database_change_log ".
					"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
					"VALUES (:updater, :timestamp, 'Deactived User Alert', :message, 'user_alerts');";
				$userAlertChangeLogQuery = $db->prepare($userAlertChangeLogSQL);
				$userAlertChangeLogQuery->bindParam(":updater", $updater , PDO::PARAM_STR);
				$userAlertChangeLogQuery->bindParam(":timestamp", $timestamp, PDO::PARAM_STR);
				$userAlertChangeLogQuery->bindParam(":message", $userAlertChangeLogMessage, PDO::PARAM_STR);
				$userAlertChangeLogQuery->execute();
				
				$userAlertChangeLogXML = $changeLogsXML->addChild('Change_Log');
				$userAlertChangeLogXML->addAttribute('type', "User Alert");
				$userAlertChangeLogXML->addChild('Status', "Transaction Logged.");
				$userAlertChangeDetailsXML = $userAlertChangeLogXML->addChild('Details');
				$userAlertChangeDetailsXML->addChild('timestamp', $timestamp);
				$userAlertChangeDetailsXML->addChild('type_of_change', "Deactived User Alert");
				$userAlertChangeDetailsXML->addChild('action_taken', $userAlertChangeLogMessage);
				$userAlertChangeDetailsXML->addChild('affected_tables', "user_alerts");
				
				if (sizeOf($deactivatedUserGroupAlerts) != 0) {
					$deactivatedUserGroupAlertsString = implode(", ", $deactivatedUserGroupAlerts);
					$userGroupsAlertChangeLogMessage = 'Alert '.$alertId.' "'.$alertTitle.'" has been deactivated for User Groups "'.$deactivatedUserGroupAlertsString.'".';
					
					$userGroupsAlertChangeLogSQL = 
						"INSERT INTO database_change_log ".
						"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
						"VALUES (:updater, :timestamp, 'Deactived User Group Alerts', :message, 'user_group_alerts');";
					$userGroupsAlertChangeLogQuery = $db->prepare($userGroupsAlertChangeLogSQL);
					$userGroupsAlertChangeLogQuery->bindParam(":updater", $updater , PDO::PARAM_STR);
					$userGroupsAlertChangeLogQuery->bindParam(":timestamp", $timestamp, PDO::PARAM_STR);
					$userGroupsAlertChangeLogQuery->bindParam(":message", $userGroupsAlertChangeLogMessage, PDO::PARAM_STR);
					$userGroupsAlertChangeLogQuery->execute();
					
					$userGroupsAlertChangeLogXML = $changeLogsXML->addChild('Change_Log');
					$userGroupsAlertChangeLogXML->addAttribute('type', "User Group Alerts");
					$userGroupsAlertChangeLogXML->addChild('Status', "Transaction Logged.");
					$userGroupsAlertChangeDetailsXML = $userGroupsAlertChangeLogXML->addChild('Details');
					$userGroupsAlertChangeDetailsXML->addChild('timestamp', $timestamp);
					$userGroupsAlertChangeDetailsXML->addChild('type_of_change', "Deactived User Group Alerts");
					$userGroupsAlertChangeDetailsXML->addChild('action_taken', $userGroupsAlertChangeLogMessage);
					$userGroupsAlertChangeDetailsXML->addChild('affected_tables', "user_group_alerts");
				}
				
				if ($alertDeactivated) {
					$alertChangeLogMessage = 'Alert '.$alertId.' "'.$alertTitle.'" has been deactivated.';
					
					$alertChangeLogSQL = 
						"INSERT INTO database_change_log ".
						"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
						"VALUES (:updater, :timestamp, 'Deactivated Alert', :message, 'alerts');";
					$alertChangeLogQuery = $db->prepare($alertChangeLogSQL);
					$alertChangeLogQuery->bindParam(":updater", $updater , PDO::PARAM_STR);
					$alertChangeLogQuery->bindParam(":timestamp", $timestamp, PDO::PARAM_STR);
					$alertChangeLogQuery->bindParam(":message", $alertChangeLogMessage, PDO::PARAM_STR);
					$alertChangeLogQuery->execute();
					
					$alertChangeLogXML = $changeLogsXML->addChild('Change_Log');
					$alertChangeLogXML->addAttribute('type', "Alert");
					$alertChangeLogXML->addChild('Status', "Transaction Logged.");
					$alertChangeDetailsXML = $alertChangeLogXML->addChild('Details');
					$alertChangeDetailsXML->addChild('timestamp', $timestamp);
					$alertChangeDetailsXML->addChild('type_of_change', "Deactivate Alert");
					$alertChangeDetailsXML->addChild('action_taken', $alertChangeLogMessage);
					$alertChangeDetailsXML->addChild('affected_tables', "alerts");
				}
				
				$end = microtime(true);
				
				$evaluationXML = $queryXML->addChild('Evaluation');
				$evaluationXML->addChild('Time', round((($end - $start) * 1000), 3).'ms');
				$evaluationXML->addChild('Number_of_Altered_Rows', 1 + sizeOf($deactivatedUserGroupAlerts) + (($alertDeactivated) ? 1 : 0));
				
			$db->commit();
		}
		catch (\PDOException $e) {
			$errorXML = $queryXML->addChild('Error');
			if ($fetchAlertsQuery->errorCode() != 0) {
				
			}
			else if ($deactivateUserAlertQuery->errorCode() != 0) {
				$deactivateUserAlertXML = $actionsXML->addChild('Action');
				$deactivateUserAlertXML->addAttribute('type', 'Deactivate Alert for User');
				$deactivateUserAlertXML->addChild('Status', "Failure");
				$errorDescription = "";
				$deactivateUserAlertXML->addChild('Details', $errorDescription);
			}
			else if ($activeStatusCheckQuery->errorCode() != 0) {
				
			}
			else if (isset($deactivateUserGroupAlertQuery) && $deactivateUserGroupAlertQuery->errorCode() != 0) {
				$deactivateUserGroupAlertXML = $actionsXML->addChild('Action');
				$deactivateUserGroupAlertXML->addAttribute('type', 'Deactivate Alert for User Group');
				$deactivateUserGroupAlertXML->addChild('Status', "Failure");
				$errorDescription = "";
				$deactivateUserGroupAlertXML->addChild('Details', $errorDescription);
			}
			
			else if (isset($deactivatedUserGroupAlertNamesQuery) && $deactivatedUserGroupAlertNamesQuery->errorCode() != 0) {
				$deactivateUserGroupAlertXML = $actionsXML->addChild('Action');
				$deactivateUserGroupAlertXML->addAttribute('type', 'Deactivate Alert for User Group');
				$deactivateUserGroupAlertXML->addChild('Status', "Failure");
				$errorDescription = "";
				$deactivateUserGroupAlertXML->addChild('Details', $errorDescription);
			}
			else if ($newActiveStatusCheckQuery->errorCode() != 0) {
				
			}
			else if (isset($deactivateAlertQuery) && $deactivateAlertQuery->errorCode() != 0) {
				$deactivateAlertXML = $actionsXML->addChild('Action');
				$deactivateAlertXML->addAttribute('type', 'Deactivate Alert');
				$deactivateAlertXML->addChild('Status', "Failure");
				$errorDescription = "";
				$deactivateAlertXML->addChild('Details', $errorDescription);
			}
			else if ($userAlertChangeLogQuery->errorCode() != 0) {
				$userAlertChangeLogXML = $resultXML->addChild('Change_Log');
				$userAlertChangeLogXML->addAttribute('type', "User Alert");
				$userAlertChangeLogXML->addChild('Status', "Failure");
				$errorDescription = "";
				$userAlertChangeLogXML->addChild('Details', $errorDescription);
			}
			else if (isset($userGroupsAlertChangeLogQuery) && $userGroupsAlertChangeLogQuery->errorCode() != 0) {
				$userGroupsAlertChangeLogXML = $resultXML->addChild('Change_Log');
				$userGroupsAlertChangeLogXML->addAttribute('type', "User Group Alerts");
				$userGroupsAlertChangeLogXML->addChild('Status', "Failure");
				$errorDescription = "";
				$userGroupsAlertChangeLogXML->addChild('Details', $errorDescription);
			}
			else if (isset($alertChangeLogQuery) && $alertChangeLogQuery->errorCode() != 0) {
				$alertChangeLogXML = $resultXML->addChild('Change_Log');
				$alertChangeLogXML->addAttribute('type', "Alert");
				$alertChangeLogXML->addChild('Status', "Failure");
				$errorDescription = "";
				$alertChangeLogXML->addChild('Details', $errorDescription);
			}
			//$errorXML->addChild('Code', $fetchAllAlertsQuery->errorCode());
			$errorXML->addChild('Error_Code', $e->getCode());
			$errorXML->addChild('Details', $e->getMessage());
			$db->rollBack();
		}
		catch(Exception $e) {
			$errorXML = $queryXML->addChild('Error');
			$errorXML->addChild('Description', "A PHP error occured.");
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