<?php
	
	header('Content-type: application/xml');
	
	$timestamp = date("Y-m-d H:i:sO");
	//echo "\nTime = ".$timestamp."\n";//"<br />";
	
	$fileName = basename(__FILE__, '.php').'.php';
	$fileDirectory = getcwd();
	$requestURI = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http")."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	
	$alertId = $_GET['aid'];
	$userGroupId = $_GET['ugid'];
	$updater = $_GET['cid'];
	$alertMessage = $_GET['mess'];
	$expiration = $_GET['exp'];
	$active = $_GET['act'];
	
	$db_host = "localhost";
	$db_name = "CASecure1";
	$db_username = "postgres";
	$db_password = "abc.123";
	
	//echo $admin."<br/>";
	
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
				$metaXML->addChild('Name', "Update Alert");
				$metaXML->addChild('Description', "This query allows the current User to change the information of a specified Alert.");
				$paramsXML = $metaXML->addChild('Parameters');
				$param1XML = $paramsXML->addChild('Parameter');
				$param1XML->addChild('Name', 'Alert ID');
				$param1XML->addChild('URL', 'aid');
				$param1XML->addChild('Value', $alertId);
				$param1XML->addChild('Description', 'The ID of the Alert to be edited');
				$param2XML = $paramsXML->addChild('Parameter');
				$param2XML->addChild('Name', 'Current User ID');
				$param2XML->addChild('URL', 'cid');
				$param2XML->addChild('Value', $updater);
				$param2XML->addChild('Description', 'The User ID of the current User');
				$param3XML = $paramsXML->addChild('Parameter');
				$param3XML->addChild('Name', 'User Group ID');
				$param3XML->addChild('URL', 'ugid');
				$param3XML->addChild('Value',$userGroupId);
				$param3XML->addChild('Description', 'The User Group ID of the selected User Group that the Alert applies to.');
				$param4XML = $paramsXML->addChild('Parameter');
				$param4XML->addChild('Name', 'Alert Message');
				$param4XML->addChild('URL', 'mess');
				$param4XML->addChild('Value',$alertMessage);
				$param4XML->addChild('Description', 'Message Information of the Alert.');
				$param5XML = $paramsXML->addChild('Parameter');
				$param5XML->addChild('Name', 'Alert Exiration Date');
				$param5XML->addChild('URL', 'exp');
				$param5XML->addChild('Value',$expiration);
				$param5XML->addChild('Description', 'The Expiration Date of the Alert');
				$param6XML = $paramsXML->addChild('Parameter');
				$param6XML->addChild('Name', 'Active Status');
				$param6XML->addChild('URL', 'act');
				$param6XML->addChild('Value',($active) ? "Active" : "Inactive");
				$param6XML->addChild('Description', 'Status reguarding whether the Alert is currently active.');
				$resultXML = $queryXML->addChild('Result');
				
				$start = microtime(true);
				
				$actionsXML = $resultXML->addChild('Actions');
				
				$fetchOriginalAlertSQL = 
					"SELECT user_group_id, title, message, expiration, active ".
					"FROM alerts ".
					"WHERE alert_id = :alertId;";
				$fetchOriginalAlertQuery = $db->prepare($fetchOriginalAlertSQL);
				$fetchOriginalAlertQuery->bindParam(":alertId", $alertId, PDO::PARAM_STR);
				$fetchOriginalAlertQuery->execute();
				$originalAlertInfo = $fetchOriginalAlertQuery->fetch(PDO::FETCH_ASSOC);
				$alertTitle = $originalAlertInfo['title'];
				$originalUserGroupId = $originalAlertInfo['user_group_id'];
				$originalMessage = $originalAlertInfo['message'];
				$originalExpiration = $originalAlertInfo['expiration'];
				$originalActiveStatus = $originalAlertInfo['active'];
				//print_r($originalAlertInfo);
				//echo "<br/>".$originalUserGroupId."<br/>";	
				//echo $originalMessage." - ".$alertMessage."<br/>";
				//echo $originalExpiration." - ".$expiration."<br/>";
				//echo $originalActiveStatus." - ".$active."<br/>";
				//echo (($originalActiveStatus == TRUE)?"true":"false")."<br/>";
				
				$fetchOriginalUserGroupNameSQL = 
					"SELECT user_group_name ".
					"FROM user_groups ".
					"WHERE user_group_id = :currentUserGroupId;";
				$fetchOriginalUserGroupNameQuery = $db->prepare($fetchOriginalUserGroupNameSQL);
				$fetchOriginalUserGroupNameQuery->bindParam(":currentUserGroupId", $currentUserGroupId, PDO::PARAM_STR);
				$fetchOriginalUserGroupNameQuery->execute();
				$originalUserGroupName = ($fetchOriginalUserGroupNameQuery->fetch(PDO::FETCH_ASSOC))['user_group_name'];
				//echo $originalUserGroupName."<br/>";
				
				$fetchOriginalAlertXML = $actionsXML->addChild('Action');
				$fetchOriginalAlertXML->addAttribute('type', 'Fetch Original Alert Info');
				$fetchOriginalAlertXML->addChild('Status', "Success");
				$fetchOriginalAlertXML->addChild('Details', 'Fetched original data for Alert "'.$alertTitle.'"');
				$originalAlertInfoXML = $fetchOriginalAlertXML->addChild('Original_Alert_Info');
				$originalAlertInfoXML->addAttribute('alert_id', $alertId);
				$originalAlertInfoXML->addChild('user_group_id', $originalUserGroupId);
				$originalAlertInfoXML->addChild('user_group_name', $originalUserGroupName);
				$originalAlertInfoXML->addChild('title', $alertTitle);
				$originalAlertInfoXML->addChild('message', $originalMessage);
				$originalAlertInfoXML->addChild('expiration', $originalExpiration);
				$originalAlertInfoXML->addChild('active', ($originalActiveStatus ? "true" : "false"));
			
				$updateArray = array();
				$messageArray = array();
				
				if ($userGroupId != $originalUserGroupId && $userGroupId != null) {
					$updateUserGroupSQL = 
						"UPDATE alerts ".
						"SET user_group_id = :userGroupId ".
						"WHERE alert_id = :alertId;";
					$updateUserGroupQuery = $db->prepare($updateUserGroupSQL);
					$updateUserGroupQuery->bindParam(":userGroupId", $userGroupId, PDO::PARAM_STR);
					$updateUserGroupQuery->bindParam(":alertId", $alertId, PDO::PARAM_STR);
					$updateUserGroupQuery->execute();
					
					$fetchNewUserGroupNameSQL = 
						"SELECT user_group_name ".
						"FROM user_groups ".
						"WHERE user_group_id = :userGroupId;";
					$fetchNewUserGroupNameQuery = $db->prepare($fetchNewUserGroupNameSQL);
					$fetchNewUserGroupNameQuery->bindParam(":userGroupId", $userGroupId, PDO::PARAM_STR);
					$fetchNewUserGroupNameQuery->execute();
					$newUserGroupName = ($fetchNewUserGroupNameQuery->fetch(PDO::FETCH_ASSOC))['user_group_name'];
					
					$updateArray['user_group_id'] = $userGroupId;
					$updateArray['user_group_name'] = $newUserGroupName;
					$messageArray['user_group_id'] = 'from "'.$originalUserGroupId.'" to "'.$userGroupId.'"';
				}
				
				if ($alertMessage != $originalMessage && $alertMessage != null) {
					$updateAlertMessageSQL = 
						"UPDATE alerts ".
						"SET message = :alertMessage ".
						"WHERE alert_id = :alertId;";
					$updateAlertMessageQuery = $db->prepare($updateAlertMessageSQL);
					$updateAlertMessageQuery->bindParam(":alertMessage", $alertMessage, PDO::PARAM_STR);
					$updateAlertMessageQuery->bindParam(":alertId", $alertId, PDO::PARAM_STR);
					$updateAlertMessageQuery->execute();
					//echo 'Alert Message Updated from "'.$originalMessage.'" to "'.$alertMessage.'".<br/>';
					
					$updateArray['message'] = $alertMessage;
					$messageArray['message'] = 'from "'.$originalMessage.'" to "'.$alertMessage.'"';
				}
				
				if ($expiration != $originalExpiration && $expiration != null) {
					$updateExpirationSQL = 
						"UPDATE alerts ".
						"SET expiration = :expiration ".
						"WHERE alert_id = :alertId;";
					$updateExpirationQuery = $db->prepare($updateExpirationSQL);
					$updateExpirationQuery->bindValue(":expiration", $expiration, PDO::PARAM_STR);
					$updateExpirationQuery->bindParam(":alertId", $alertId, PDO::PARAM_STR);
					$updateExpirationQuery->execute();
					//echo 'Alert Expiration Date changed from "'.$originalExpiration.'" to "'.$expiration.'".<br/>';
					
					$updateArray['expiration'] = $expiration;
					$messageArray['expiration'] = 'from "'.$originalExpiration.'" to "'.$expiration.'"';
				}
				
				($active == "true") ? ($isActive = true) : ($isActive = false);
				//echo $isActive."<br/>";
				
				if ($isActive != $originalActiveStatus && $active != null) {
					$updateActiveStatusSQL = 
						"UPDATE alerts ".
						"SET active = :active ".
						"WHERE alert_id = :alertId;";
					$updateActiveStatusQuery = $db->prepare($updateActiveStatusSQL);
					$updateActiveStatusQuery->bindParam(":active", $active, PDO::PARAM_STR);
					$updateActiveStatusQuery->bindParam(":alertId", $alertId, PDO::PARAM_STR);
					$updateActiveStatusQuery->execute();
					if ($isActive == true) {
						//echo 'Alert activated.<br/>';
					}
					else {
						//echo 'Alert deactivated.<br/>';
					}
					
					$updateArray['active'] = $active;
					$messageArray['active'] = 'from "'.(($originalActiveStatus == true)?("true"):("false")).'" to "'.$active.'"';
				}
				
				$updateAlertXML = $actionsXML->addChild('Action');
				$updateAlertXML->addAttribute('type', 'Update Alert Info');
				if ($updateArray != array()) {
					$updateAlertXML->addChild('Status', "Success");
					$updateAlertXML->addChild('Details', 'Altered data for alert "'.$alertTitle.'" in database');
					$updatedAlertInfo = $updateAlertXML->addChild('Changed_Alert_Info');
					$updatedAlertInfo->addAttribute('table', 'alerts');
					foreach($updateArray as $columnName => $alteredColumn) {
						$updatedAlertInfo->addChild($columnName, $alteredColumn);
					}
					$updateAlertXML->addChild('Count', 1);
				}
				else {
					$updateAlertXML->addChild('Status', "N/A");
					$updateAlertXML->addChild('Details', 'No Changes Made.');
				}
		
				if ($messageArray != array()) {
					$changeLogMessage = 'Changes made to alert "'.$alertTitle.'" ('.$alertId.') for user group "'.$originalUserGroupName.'" ('.$originalUserGroupId.') = ';
					foreach($messageArray as $messageIndex => $messageRow) {
						$changeLogMessage .= $messageIndex.' changed '.$messageRow.', ';
					}
					$changeLogMessage = substr($changeLogMessage, 0 , -2);
					$changeLogMessage .= '.';
					//echo $changeLogMessage."<br/>";
					$databaseChangeLogSQL = 
						"INSERT INTO database_change_log ".
						"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables) ".
						"VALUES (:updater, :timestamp, 'Edit Alert Message', :message, 'alerts');";
					$databaseChangeLogQuery = $db->prepare($databaseChangeLogSQL);
					$databaseChangeLogQuery->bindParam(":updater", $updater, PDO::PARAM_STR);
					$databaseChangeLogQuery->bindParam(":timestamp", $timestamp, PDO::PARAM_STR);
					$databaseChangeLogQuery->bindParam(":message", $changeLogMessage, PDO::PARAM_STR);
					$databaseChangeLogQuery->execute();
					//echo "Transaction Logged.<br/>";
					
					
				
					$changeLogXML = $resultXML->addChild('Change_Log');
					$changeLogXML->addChild('Status', 'Transaction Logged');
					$changeDetailsXML = $changeLogXML->addChild('Details');
					$changeDetailsXML->addChild('timestamp', $timestamp);
					$changeDetailsXML->addChild('type_of_change', 'Edit Alert Message');
					$changeDetailsXML->addChild('action_taken', $changeLogMessage);
					$changeDetailsXML->addChild('affected_tables', 'alerts');
					
					$changeLogXML->addChild('Count', 1);
				}
				
				$end = microtime(true);
				
				$evaluationXML = $queryXML->addChild('Evaluation');
				$evaluationXML->addChild('Time', round((($end - $start) * 1000), 3).'ms');
				$evaluationXML->addChild('Total_Number_of_Altered_Rows', ($updateArray == array()) ? 0 : 1);
				
			$db->commit();
		}
		catch (\PDOException $e) {
			$errorXML = $queryXML->addChild('Error');
			if ($fetchOriginalAlertQuery->errorCode() != 0) {
				$fetchOriginalAlertXML = $actionsXML->addChild('Action');
				$fetchOriginalAlertXML->addAttribute('type', 'Fetch Original Alert Info');
				$fetchOriginalAlertXML->addChild('Status', "Failure");
				$errorDescription = 'Could not fetch Original Alert Info';
			//	if ($fetchOriginalAlertQuery->errorCode() == 23502) {
					//$errorDescription = '';
			//	}
			//	else if ($fetchOriginalAlertQuery->errorCode() == 23503) {
					//$errorDescription = '';
			//	}
			//	else if ($fetchOriginalAlertQuery->errorCode() == 23505) {
					//$errorDescription = '';
			//	}
				$fetchOriginalAlertXML->addChild('Details', $errorDescription);
			}
			else if ($fetchOriginalUserGroupNameQuery->errorCode() != 0) {
				//echo 'Failure to set BigFix user.';
				$fetchOriginalAlertXML = $actionsXML->addChild('Action');
				$fetchOriginalAlertXML->addAttribute('type', 'Fetch Original Alert Info');
				$fetchOriginalAlertXML->addChild('Status', "Failure");
				$errorDescription = 'Could not fetch Name of Original User Group.';
				$fetchOriginalAlertXML->addChild('Details', $errorDescription);
			}
			else if ((isset($updateUserGroupQuery) && $updateUserGroupQuery->errorCode() != 0) || 
					 (isset($fetchNewUserGroupNameQuery) && $fetchNewUserGroupNameQuery->errorCode() != 0) || 
					 (isset($updateAlertMessageQuery) && $updateAlertMessageQuery->errorCode() != 0) || 
					 (isset($updateExpirationQuery) && $updateExpirationQuery->errorCode() != 0) || 
					 (isset($updateActiveStatusQuery) && $updateActiveStatusQuery->errorCode() != 0)
			) {
				$updateAlertXML = $actionsXML->addChild('Action');
				$updateAlertXML->addAttribute('type', 'Update Alert Info');
				$updateAlertXML->addChild('Status', "Failure");
				if ($updateUserGroupQuery->errorCode() != 0) {
					$errorDescription = 'Failure to update User Group.';
				}
				else if ($fetchNewUserGroupNameQuery->errorCode() != 0) {
					$errorDescription = 'Could not fetch Name of new User Group.';
				}
				else if ($updateAlertMessageQuery->errorCode() != 0) {
					$errorDescription = 'Failure to Update Alert Message.';
				}
				else if ($updateExpirationQuery->errorCode() != 0) {
					$errorDescription = 'Failure to Update Alert Expiration.';
				}
				else if ($updateActiveStatusQuery->errorCode() != 0) {
					$errorDescription = 'Failure to Update Alert Active Status.';
				}
				$updateAlertXML->addChild('Details', $errorDescription);
				
			}
			else if ($databaseChangeLogQuery->errorCode() != 0) {
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
			//throw $e;
			
			try {
				$errorLogSQL = 
					"INSERT INTO error_log ".
					"(user_id, description, error_code, error_message, exception_type, timestamp, file_name, file_directory, request_uri) ".
					"VALUES (:userID, :description, :errorCode, :errorMessage, 'PDO Query', :timestamp , :fileName, :fileDirectory, :requestURI);";
				$errorLogQuery = $db->prepare($errorLogSQL);
				$errorLogQuery->bindParam(':userID', $updaterSQL, PDO::PARAM_STR);
				$errorLogQuery->bindParam(':description', $errorDescription, PDO::PARAM_STR);
				$errorLogQuery->bindParam(':errorCode', $errorCode, PDO::PARAM_STR);
				$errorLogQuery->bindParam(':errorMessage', $errorMessage, PDO::PARAM_STR);
				$errorLogQuery->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
				$errorLogQuery->bindParam(':fileName', $fileName, PDO::PARAM_STR);
				$errorLogQuery->bindParam(':fileDirectory', $fileDirectory, PDO::PARAM_STR);
				$errorLogQuery->bindParam(':requestURI', $requestURI, PDO::PARAM_STR);
				$errorLogQuery->execute();
				
				$errorLogXML = $errorXML->addChild('Error_Log');
				$errorLogXML->addChild('Status', 'Error Logged');
				$errorLogDetailsXML = $errorLogXML->addChild('Details');
				$errorLogDetailsXML->addChild('description', $errorDescription);
				$errorLogDetailsXML->addChild('error_code', $e->getCode());
				$errorLogDetailsXML->addChild('error_message', $e->getMessage());
				$errorLogDetailsXML->addChild('exception_type', 'PDO Query');
				$errorLogDetailsXML->addChild('timestamp', $timestamp);
				$errorLogDetailsXML->addChild('file_name', htmlspecialchars($fileName));
				$errorLogDetailsXML->addChild('file_directory', htmlspecialchars($fileDirectory));
				$errorLogDetailsXML->addChild('request_uri', htmlspecialchars($requestURI));
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
			"user_id" => $updater,
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
	catch (Exception $e) {
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