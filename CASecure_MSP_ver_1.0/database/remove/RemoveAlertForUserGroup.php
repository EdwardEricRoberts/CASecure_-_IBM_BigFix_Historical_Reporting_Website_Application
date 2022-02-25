<?php
	
	header('Content-type: application/xml');
	
	$timestamp = date("Y-m-d H:i:sO");
	//echo "\nTime = ".$timestamp."\n";//"<br />";
	
	$alertId = $_GET['aid'];
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
				$metaXML->addChild('Name', "Remove Alert");
				$metaXML->addChild('Description', "This query Deactivates specified Alerts.");
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
				$resultXML = $queryXML->addChild('Result');
				
				
				$start = microtime(true);
				
				$fetchAlertsSQL = 
					"SELECT a.title, a.message, a.user_group_id, g.user_group_name, a.timestamp, a.expiration, a.issued_by_id, a.active ".
					"FROM alerts a, user_groups g ".
					"WHERE g.user_group_id = a.user_group_id AND a.active = true AND a.alert_id = :alertId;";
				$fetchAlertsQuery = $db->prepare($fetchAlertsSQL);
				$fetchAlertsQuery->bindParam(":alertId", $alertId, PDO::PARAM_STR);
				$fetchAlertsQuery->execute();
				$alertsArray = $fetchAlertsQuery->fetch(PDO::FETCH_ASSOC);
				$alertTitle = $alertsArray['title'];
				$alertMessage = $alertsArray['message'];
				$userGroupID = $alertsArray['user_group_id'];
				$userGroupName = $alertsArray['user_group_name'];
				$alertTimestamp = $alertsArray['timestamp'];
				$alertExpiration = $alertsArray['expiration'];
				$alertIssuedById = $alertsArray['issued_by_id'];
				//print_r($alertsArray);
				//echo "<br/>";
				//echo $alertTitle."<br/>";
				//echo $alertMessage."<br/>";
				//echo $alertTimestamp."<br/>";
				//echo $alertExpiration."<br/>";
				
				$actionsXML = $resultXML->addChild('Actions');
				
				$deactivateAlertsSQL = 
					"UPDATE alerts ".
					"SET active = false ".
					"WHERE active = true AND alert_id = :alertId;";
				$deactivateAlertsQuery = $db->prepare($deactivateAlertsSQL);
				$deactivateAlertsQuery->bindParam(":alertId", $alertId, PDO::PARAM_STR);
				$deactivateAlertsQuery->execute();
				
				//$resultXML->addChild('Status', 'Alert Deactived');
				//$resultXML->addChild('Details', 'Alert "'.$alertTitle.'" for User Group "'.$userGroupName.'" has been deactivated.');
				$deactivateAlertsXML = $actionsXML->addChild('Action');
				$deactivateAlertsXML->addAttribute('type', 'Deactivate Alert');
				$deactivateAlertsXML->addChild('Status', "Alert Deactivated");
				$deactivateAlertsXML->addChild('Details', 'Alert "'.$alertTitle.'" for User Group "'.$userGroupName.'" has been deactivated.');
				$alertsDeletedRowsXML = $deactivateAlertsXML->addChild('Deleted_rows');
				$alertsDeletedRowsXML->addAttribute('table', 'alerts');
				
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
				
				$message = 'Alert '.$alertId.' "'.$alertTitle.'" for User Group '.$userGroupID.' "'.$userGroupName.'" has been deactivated.';
				
				$changeLogSQL = 
					"INSERT INTO database_change_log ".
					"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
					"VALUES (:updater, :timestamp, 'Deactived Alert', :message, 'alerts');";
				$changeLogQuery = $db->prepare($changeLogSQL);
				$changeLogQuery->bindParam(":updater", $updater , PDO::PARAM_STR);
				$changeLogQuery->bindParam(":timestamp", $timestamp, PDO::PARAM_STR);
				$changeLogQuery->bindParam(":message", $message, PDO::PARAM_STR);
				$changeLogQuery->execute();
				
				$end = microtime(true);
				
				$changeLogXML = $resultXML->addChild('Change_Log');
				$changeLogXML->addChild('Status', "Transaction Logged.");
				$changeDetailsXML = $changeLogXML->addChild('Details');
				$changeDetailsXML->addChild('timestamp', $timestamp);
				$changeDetailsXML->addChild('type_of_change', "Deactived Alert");
				$changeDetailsXML->addChild('action_taken', $message);
				$changeDetailsXML->addChild('affected_tables', "alerts");
				
				$evaluationXML = $queryXML->addChild('Evaluation');
				$evaluationXML->addChild('Time', round((($end - $start) * 1000), 3).'ms');
				$evaluationXML->addChild('Number_of_Altered_Rows', 1);
				//
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