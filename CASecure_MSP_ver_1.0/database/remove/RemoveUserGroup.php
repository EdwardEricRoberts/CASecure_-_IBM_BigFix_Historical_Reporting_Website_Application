<?php
	
	header('Content-type: application/xml');
	
	$timestamp = date("Y-m-d H:i:sO");
	//echo "\nTime = ".$timestamp."\n";//"<br />";
	
	$userGroupId = $_GET['ugid'];
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
				$metaXML->addChild('Name', "Remove User Group");
				$metaXML->addChild('Description', "This query Deletes a specified User Group.");
				$paramsXML = $metaXML->addChild('Parameters');
				$param1XML = $paramsXML->addChild('Parameter');
				$param1XML->addChild('Name', 'Current User ID');
				$param1XML->addChild('URL', 'cid');
				$param1XML->addChild('Value', $updater);
				$param1XML->addChild('Description', 'The User ID of the current User');
				$param2XML = $paramsXML->addChild('Parameter');
				$param2XML->addChild('Name', 'User Group ID');
				$param2XML->addChild('URL', 'ugid');
				$param2XML->addChild('Value',$userGroupId);
				$param2XML->addChild('Description', 'The User Group ID of the selected User Group');
				$resultXML = $queryXML->addChild('Result');
				
				$start = microtime(true);
				
				$fetchUserGroupSQL =
					"SELECT user_group_name ".
					"FROM user_groups ".
					"WHERE user_group_id = :userGroupId;";
				$fetchUserGroupQuery = $db->prepare($fetchUserGroupSQL);
				$fetchUserGroupQuery->bindParam(":userGroupId", $userGroupId, PDO::PARAM_STR);
				$fetchUserGroupQuery->execute();
				$userGroupName = ($fetchUserGroupQuery->fetch(PDO::FETCH_ASSOC))['user_group_name'];
				//echo $userGroupName."<br/>";
				$userGroupCount = 1;
				
				$fetchUserGroupUsersSQL = 
					"SELECT g.user_id, u.user_name, g.group_admin ".
					"FROM user_group_users g, users u ".
					"WHERE g.user_id = u.user_id AND g.user_group_id = :userGroupId";
				$fetchUserGroupUsersQuery = $db->prepare($fetchUserGroupUsersSQL);
				$fetchUserGroupUsersQuery->bindParam(":userGroupId", $userGroupId, PDO::PARAM_STR);
				$fetchUserGroupUsersQuery->execute();
				$userGroupUsersArray = $fetchUserGroupUsersQuery->fetchAll(PDO::FETCH_ASSOC);
				//print_r($userGroupUsersArray);
				//echo "<br/>";
				$userGroupUsersCount = sizeOf($userGroupUsersArray);
				
				$fetchAlertsSQL = 
					"SELECT alert_id, title, message, timestamp, expiration, issued_by_id, active ".
					"FROM alerts ".
					"WHERE active = true AND user_group_id = :userGroupId;";
				$fetchAlertsQuery = $db->prepare($fetchAlertsSQL);
				$fetchAlertsQuery->bindParam(":userGroupId", $userGroupId, PDO::PARAM_STR);
				$fetchAlertsQuery->execute();
				$alertsArray = $fetchAlertsQuery->fetchAll(PDO::FETCH_ASSOC);
				//print_r($alertsArray);
				//echo "<br/>";
				$alertsCount = sizeOf($alertsArray);
				
				$actionsXML = $resultXML->addChild('Actions');
				
				$deactivateAlertsSQL = 
					"UPDATE alerts ".
					"SET active = false ".
					"WHERE active = true AND user_group_id = :userGroupId;";
				$deactivateAlertsQuery = $db->prepare($deactivateAlertsSQL);
				$deactivateAlertsQuery->bindParam(":userGroupId", $userGroupId, PDO::PARAM_STR);
				$deactivateAlertsQuery->execute();
				
				$deactivateAlertsXML = $actionsXML->addChild('Action');
				$deactivateAlertsXML->addAttribute('type', 'Deactivate Alerts');
				$deactivateAlertsXML->addChild('Status', "Alerts Deactivated");
				$deactivateAlertsXML->addChild('Details', 'Alerts for User Group "'.$userGroupName.'" have been Deactivated.');
				$alertsDeletedRowsXML = $deactivateAlertsXML->addChild('Deleted_Rows');
				$alertsDeletedRowsXML->addAttribute('table', 'alerts');
				
				foreach($alertsArray as $alert) {
					$alertRowXML = $alertsDeletedRowsXML->addChild('Row');
					$alertRowXML->addChild('alert_id', $alert['alert_id']);
					$alertRowXML->addChild('title', $alert['title']);
					$alertRowXML->addChild('message', $alert['message']);
					$alertRowXML->addChild('timestamp', $alert['timestamp']);
					$alertRowXML->addChild('expiration', $alert['expiration']);
					$alertRowXML->addChild('issued_by_id', $alert['issued_by_id']);
				}
				$deactivateAlertsXML->addChild('Count', $alertsCount);
				
				$deleteUserGroupUsersSQL = 
					"DELETE FROM user_group_users ".
					"WHERE user_group_id = :userGroupId;";
				$deleteUserGroupUsersQuery = $db->prepare($deleteUserGroupUsersSQL);
				$deleteUserGroupUsersQuery->bindParam(":userGroupId", $userGroupId, PDO::PARAM_STR);
				$deleteUserGroupUsersQuery->execute();
				
				$deleteUserGroupUsersXML = $actionsXML->addChild('Action');
				$deleteUserGroupUsersXML->addAttribute('type', 'Delete User Group Users');
				$deleteUserGroupUsersXML->addChild('Status', "User Group Users Deleted");
				$deleteUserGroupUsersXML->addChild('Details', 'Users that are members of User Group "'.$userGroupName.'" have been Removed.');
				$userGroupUsersDeletedRowsXML = $deleteUserGroupUsersXML->addChild('Deleted_rows');
				$userGroupUsersDeletedRowsXML->addAttribute('table', 'user_group_users');
				$userGroupUsersDeletedRowsXML->addAttribute('user_group_id', $userGroupId);
				$userGroupUsersDeletedRowsXML->addAttribute('user_group_name', $userGroupName);
				
				foreach($userGroupUsersArray as $userGroupUser) {
					$userGroupUserRowXML = $userGroupUsersDeletedRowsXML->addChild('Row');
					$userGroupUserRowXML->addChild('user_id', $userGroupUser['user_id']);
					$userGroupUserRowXML->addChild('user_name', $userGroupUser['user_name']);
					$userGroupUserRowXML->addChild('group_admin', (($userGroupUser['group_admin'] == true)?("true"):("false")));
				}
				$deleteUserGroupUsersXML->addChild('Count', $userGroupUsersCount);
				
				$deleteUserGroupSQL = 
					"DELETE FROM user_groups ".
					"WHERE user_group_id = :userGroupId";
				$deleteUserGroupQuery = $db->prepare($deleteUserGroupSQL);
				$deleteUserGroupQuery->bindParam(":userGroupId", $userGroupId, PDO::PARAM_STR);
				$deleteUserGroupQuery->execute();
				//echo 'User Group "'.$userGroupName.'" has been permanently deleted. <br/>';
				
				$deleteUserGroupXML = $actionsXML->addChild('Action');
				$deleteUserGroupXML->addAttribute('type', 'Delete User Group');
				$deleteUserGroupXML->addChild('Status', "User Group Deleted");
				$deleteUserGroupXML->addChild('Details', 'User Group "'.$userGroupName.'" has been permanently deleted.');
				$userGroupDeletedRowsXML = $deleteUserGroupXML->addChild('Deleted_rows');
				$userGroupDeletedRowsXML->addAttribute('table', 'user_groups');
				
				$userGroupRowXML = $userGroupDeletedRowsXML->addChild('Row');
				$userGroupRowXML->addChild('user_group_id', $userGroupId);
				$userGroupRowXML->addChild('user_group_name', $userGroupName);
				$deleteUserGroupXML->addChild('Count', $userGroupCount);
				
				$message = 'Removed User Group '.$userGroupId.' "'.$userGroupName.'" from the database, and set associated alerts to inactive. ';
				$message .= '[user_group_users] = (';
				foreach($userGroupUsersArray as $index => $userGroupUser) {
					if ($index > 0) {
						$message .= ', ';
					}
					$message .= '([user_id] = '.$userGroupUser['user_id'].', [user_name] = "'.$userGroupUser['user_name'].'", [group_admin] = '.(($userGroupUser['group_admin'] == true)?("true"):("false")).')';
				}
				$message .= '), ';
				
				$message .= '[alert_ids] = (';
				foreach($alertsArray as $index => $alert) {
					if ($index > 0) {
						$message .= ', ';
					}
					$message .= $alert['alert_id'];
				}
				$message .= ') ';
				//echo $message.'<br/>';
				
				$changeLogSQL = 
					"INSERT INTO database_change_log ".
					"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
					"VALUES (:updater, :timestamp, 'Deleted User Group', :message, 'user_groups, user_group_users, alerts');";
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
				$evaluationXML->addChild('Total_Number_of_Altered_Rows', ($userGroupCount + $userGroupUsersCount + $alertsCount));
				
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