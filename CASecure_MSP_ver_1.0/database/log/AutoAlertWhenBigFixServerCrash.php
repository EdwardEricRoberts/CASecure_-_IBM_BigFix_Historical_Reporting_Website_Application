<?php
	
	header('Content-type: application/xml');
	
	$timestamp = date("Y-m-d H:i:sO");
	//echo "\nTime = ".$timestamp."\n";//"<br />";
	
	$fileName = basename(__FILE__, '.php').'.php';
	$fileDirectory = getcwd();
	$requestURI = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http")."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	
	//$userName = implode(" ", array_slice($argv, 1, 1));
	//$password = implode(" ", array_slice($argv, 2, 1));
	//$server = implode(" ", array_slice($argv, 3, 1)); // Must be entered with periods "."s instead of "%2E"s
	
	$userID = $_GET['cid'];
	
	//$db_host = implode(" ", array_slice($argv, 4, 1));
	//$db_name = implode(" ", array_slice($argv, 5, 1));
	//$db_username = implode(" ", array_slice($argv, 6, 1));
	//$db_password = implode(" ", array_slice($argv, 7, 1));
	
	$db_host = "localhost";
	$db_name = "CASecure1";
	$db_username = "postgres";
	$db_password = "abc.123";
	
	// Run the following command from the Command Prompt to run this file manually
	//php C:\Bitnami\wappstack-7.1.18-0\apache2\htdocs\CASecure_MSP_ver_1.0\database\MicrosoftPatchComplianceSummaryData.php eer AllieCat5 bigfix.internal.cassevern.com localhost postgres postgres abc.123
	
	$xml= new SimpleXMLElement('<PDO/>'); 
	$connectionXML = $xml->addChild('Connection');
	try {
		$connectResultXML = $connectionXML->addChild('Result');
		
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
				$metaXML->addChild('Name', "Auto-Alert When BigFix Server Crash");
				$metaXML->addChild('Description', "Checks to see if the computers in the network have updated their info on the BigFix server in the last 24 hours.  IF none of them have, then it issues a series of Alerts to all User Groups.");
				$paramsXML = $metaXML->addChild('Parameters');
				$param1XML = $paramsXML->addChild('Parameter');
				$param1XML->addChild('Name', 'Current User ID');
				$param1XML->addChild('URL', 'cid');
				$param1XML->addChild('Value', $userID);
				$param1XML->addChild('Description', 'The User ID of the current User');
				$resultXML = $queryXML->addChild('Result');
				
				$start = microtime(true);
				
				$actionsXML = $resultXML->addChild('Actions');
				
				$bigFixLoginSQL = 
					"SELECT bigfix_user_name, bigfix_password, bigfix_server ".
					"FROM bigfix_logins ".
					"WHERE bigfix_user_name IN (".
						"SELECT bigfix_user_name ".
						"FROM console_to_portal ".
						"WHERE user_id = :userID".
					");";
				$bigFixLoginQuery = $db->prepare($bigFixLoginSQL);
				$bigFixLoginQuery->bindParam(':userID', $userID, PDO::PARAM_STR);
				$bigFixLoginQuery->execute();
				$bigFixLogin = $bigFixLoginQuery->fetch(PDO::FETCH_ASSOC);
				
				$bigFixUser = $bigFixLogin['bigfix_user_name'];
				$bigFixPassword = $bigFixLogin['bigfix_password'];
				$bigFixServer = $bigFixLogin['bigfix_server'];
				
				$bigFixLoginXML = $actionsXML->addChild('Action');
				$bigFixLoginXML->addAttribute('type', 'Fetch BigFix Server credentials for Current User.');
				$bigFixLoginXML->addChild('Status', "Success");
				$bigFixLoginXML->addChild('Details', 'Fetched credentials for the current user to log into the BigFix API.');
				
				
				$computerInventoryURL = "http://localhost/CASecure_MSP_ver_1.0/proxies/InventoryReport.php?user=".$bigFixUser."&pass=".$bigFixPassword."&serv=".$bigFixServer."&cg=All Machines";
				
				$computerInventoryXML = simplexml_load_file($computerInventoryURL);
				
				$currentDateTime = new DateTime("now");
				$currentTime = strtotime($currentDateTime->format('D, M d, Y h:i:s A'));
				//echo($currentTime)."<br/>";
				$updateStatusGood = array();
				$machineData = array();
				foreach($computerInventoryXML->Query->Result->Tuple as $key => $besComputer) {
					$computerID = $besComputer->Answer[0]->__toString();
					$computerName = $besComputer->Answer[1]->__toString();
					$computerName = str_replace(" <br>", ", ", $computerName);
					$lastReportTime = $besComputer->Answer[6]->__toString();
					$lastReportTimeString = str_replace("<br>", " ", $lastReportTime);
					//$lastReportTimeDateTime = DateTime::createFromFormat('D, M d, Y h:i:s A', $lastReportTimeString);
					$lastReportTimeTime = strtotime($lastReportTimeString);
					$updateHoursLag = abs($currentTime - $lastReportTimeTime)/(60*60);
					
					if($updateHoursLag >= 24) {
						$updateStat = false;
					}
					else {
						$updateStat = true;
					}
					
					$updateStatusGood[] = $updateStat;
					
					$machineData[] = array(
						"computer_id" => $computerID, 
						"computer" => $computerName, 
						"last_report_time" => $lastReportTimeString,
						"up_to_date" => ($updateStat) ? "true" : "false"
					);
				}
				//print_r($updateStatusGood);
				//echo "<br/><br/>";
				//print_r($machineData);
				
				$checkServerStausXML = $actionsXML->addChild('Action');
				$checkServerStausXML->addAttribute('type', 'Collect Data from BigFix API on Computers in network and determin if they have updated in the last 24 hours.');
				$checkServerStausXML->addChild('Status', "Success");
				$checkServerStausXML->addChild('Details', 'Collected Data from the BigFix API reguarding all machines that the User has access to, and determined whether each machine had been updated within the last 24 hours.');
				$machineStatusRows = $checkServerStausXML->addChild('Collected_Rows');
				
				foreach ($machineData as $computer) {
					$machineRowXML = $machineStatusRows->addChild('Row');
					$machineRowXML->addAttribute('computer_id', $computer['computer_id']);
					$machineRowXML->addChild('computer', $computer['computer']);
					$machineRowXML->addChild('last_report_time', $computer['last_report_time']);
					$machineRowXML->addChild('up_to_date', $computer['up_to_date']);
				}
				
				if (count(array_unique($updateStatusGood)) === 1) {
					if (array_unique($updateStatusGood)[0] == false) {
						
						
						//echo "BigFix Server out of date.  Alert Created and sent to all User Groups.<br/>";
						
						$userGroupsListSQL = 
							"SELECT user_group_id, user_group_name ".
							"From user_groups";
						$userGroupsListQuery = $db->query($userGroupsListSQL);
						$userGroupsListArray = $userGroupsListQuery->fetchAll(PDO::FETCH_ASSOC);
						//print_r($userGroupsListArray);
						
						$newAlertIDs = array();
						foreach($userGroupsListArray as $userGroup) { 
							$alertSQL = 
								"INSERT INTO alerts ".
								"(user_group_id, issued_by_id, title, message, timestamp, active) ".
								"VALUES (:userGroupID, :userID, 'BigFix Server Stagnation', 'The server has not refreshed in 24 hours, contact the administrator to reset the server.', :timestamp, true);";
							$alertQuery = $db->prepare($alertSQL);
							$alertQuery->bindParam(':userGroupID', $userGroup['user_group_id'], PDO::PARAM_STR);
							$alertQuery->bindParam(':userID', $userID, PDO::PARAM_STR);
							$alertQuery->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
							$alertQuery->execute();
							
							$newAlertIDs[] = array(
								'alert_id' => $db->lastInsertId(), 
								'user_group_name' => $userGroup['user_group_name']
							);
						}
						
						//$newAlertId = $db->lastInsertId();
						
						$autoAlertXML = $actionsXML->addChild('Action');
						$autoAlertXML->addAttribute('type', 'Send Alerts to all User Groups if all Computers have not updated.');
						$autoAlertXML->addChild('Status', 'Success');
						$autoAlertXML->addChild('Details', 'The BigFix server has not updated in over 24 hours.  An Alert has been sent to every User Group.');
						$autoAlertXML->addChild('Alert_Title', 'BigFix Server Stagnation');
						$autoAlertXML->addChild('Alert_Message', 'The server has not refreshed in 24 hours, contact the administrator to reset the server.');
						$insertedAlertIDsXML = $autoAlertXML->addChild('Inserted_Alerts');
						
						foreach ($newAlertIDs as $alertID ) {
							$alertRow = $insertedAlertIDsXML->addChild('Alert');
							$alertRow->addChild('alert_id', $alertID['alert_id']);
							$alertRow->addChild('user_group_name', $alertID['user_group_name']);
						}
						
						$autoAlertXML->addChild('Count', sizeOf($newAlertIDs));
						
						$changeLogMessage = 'Auto Alert "BigFix Server Stagnation" created for All User Groups.  BigFix Server had not refreshed in 24 hours.';
						
						$changeLogSQL = 
							"INSERT INTO database_change_log ".
							"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
							"VALUES (:userID, :timestamp, 'Auto Alert', :message, 'alerts');";
						$changeLogQuery = $db->prepare($changeLogSQL);
						$changeLogQuery->bindParam(':userID', $userID, PDO::PARAM_STR);
						$changeLogQuery->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
						$changeLogQuery->bindParam(':message', $changeLogMessage, PDO::PARAM_STR);
						$changeLogQuery->execute();
						
						$changeLogXML = $resultXML->addChild('Change_Log');
						$changeLogXML->addChild('Status', 'Transaction Logged');
						$changeDetailsXML = $changeLogXML->addChild('Details');
						$changeDetailsXML->addChild('timestamp', $timestamp);
						$changeDetailsXML->addChild('type_of_change', 'Auto Alert');
						$changeDetailsXML->addChild('action_taken', $changeLogMessage);
						$changeDetailsXML->addChild('affected_tables', 'alerts');
						
						$changeLogXML->addChild('Count', 1);
						
						$end = microtime(true);
				
						$evaluationXML = $queryXML->addChild('Evaluation');
						$evaluationXML->addChild('Time', round((($end - $start) * 1000), 3).'ms');
						$evaluationXML->addChild('Total_Number_of_Altered_Rows', sizeOf($newAlertIDs));
						
					}
					else {
						$autoAlertXML = $actionsXML->addChild('Action');
						$autoAlertXML->addAttribute('type', 'Send Alerts to all User Groups if all Computers have not updated.');
						$autoAlertXML->addChild('Status', 'Success');
						$autoAlertXML->addChild('Details', 'BigFix Server is completely up to date.  No Alerts were created.');
						//echo "BigFix Server OK.<br/>";
						
						$end = microtime(true);
				
						$evaluationXML = $queryXML->addChild('Evaluation');
						$evaluationXML->addChild('Time', round((($end - $start) * 1000), 3).'ms');
						$evaluationXML->addChild('Total_Number_of_Altered_Rows', 0);
					}
				}
				else {
					$autoAlertXML = $actionsXML->addChild('Action');
					$autoAlertXML->addAttribute('type', 'Send Alerts to all User Groups if all Computers have not updated.');
					$autoAlertXML->addChild('Status', 'Success');
					$autoAlertXML->addChild('Details', 'BigFix Server is mostly up to date.  No Alerts were created.');
					//echo "BigFix Server OK, but some macines require updating.<br/>";
					
					$end = microtime(true);
				
					$evaluationXML = $queryXML->addChild('Evaluation');
					$evaluationXML->addChild('Time', round((($end - $start) * 1000), 3).'ms');
					$evaluationXML->addChild('Total_Number_of_Altered_Rows', 0);
				}
				
			$db->commit();
		}
		catch (\PDOException $e) {
			$errorXML = $queryXML->addChild('Error');
			
			if ($bigFixLoginQuery->errorCode() != 0) {
				$bigFixLoginXML = $actionsXML->addChild('Action');
				$bigFixLoginXML->addAttribute('type', 'Fetch BigFix Server credentials for Current User.');
				$bigFixLoginXML->addChild('Status', "Failure");
				//if ($bigFixLoginQuery->errorCode() == ) {
				//	$errorDescription = '';
				//}
				//else {
					$errorDescription = 'Unable to access BigFix Server credentials';
				//}
				$bigFixLoginXML->addChild('Details', $errorDescription);
			}
			else if ($userGroupsListQuery->errorCode() != 0) {
				$autoAlertXML = $actionsXML->addChild('Action');
				$autoAlertXML->addAttribute('type', 'Send Alerts to all User Groups if all Computers have not updated.');
				$autoAlertXML->addChild('Status', 'Failure');
				//if ($userGroupsListQuery->errorCode() == ) {
				//	$errorDescription = '';
				//}
				//else {
					$errorDescription = 'An Error occured while trying fetch User Group Info.';
				//}
				$autoAlertXML->addChild('Details', $errorDescription);
			}
			else if ($alertQuery->errorCode() != 0) {
				$autoAlertXML = $actionsXML->addChild('Action');
				$autoAlertXML->addAttribute('type', 'Send Alerts to all User Groups if all Computers have not updated.');
				$autoAlertXML->addChild('Status', 'Failure');
				//if ($alertQuery->errorCode() == ) {
				//	$errorDescription = '';
				//}
				//else {
					$errorDescription = 'An Error occured while creating the Alerts.  The action has been undone.';
				//}
				$autoAlertXML->addChild('Details', $errorDescription);
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
			
			$db->rollback();
			
			try {
				//$fileName = basename(__FILE__, '.php').'.php';
				//$fileDirectory = getcwd().'\\'.basename(__FILE__, '.php').'.php';
				
				$errorLogSQL = 
					"INSERT INTO error_log ".
					"(description, error_code, error_message, exception_type, timestamp, file_name, file_directory, request_uri) ".
					"VALUES (:description, :errorCode, :errorMessage, 'PDO Query', :timestamp, :fileName, :fileDirectory, :requestURI);";
				$errorLogQuery = $db->prepare($errorLogSQL);
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
				$errorLogDetailsXML->addChild('fileName', htmlspecialchars($fileName));
				$errorLogDetailsXML->addChild('fileDirectory', htmlspecialchars($fileDirectory));
				$errorLogDetailsXML->addChild('requestURI', htmlspecialchars($requestURI));
			}
			catch(\PDOException $e2) {
				$errorLogXML = $errorXML->addChild('Error_Log');
				$errorLogXML->addChild('Status', 'Failed to Log Error');
				$errorLogXML->addChild('Error_Code', $e2->getCode());
				$errorLogXML->addChild('Details', $e2->getMessage());
			}
		}
	}
	catch (PDOException $e) {
		$connectResultXML->addChild('Status', 'Failure');
		$connectResultXML->addChild('Message', "Failed to Connect to Database.  Please Email Site Administrator.");
		$connectResultXML->addChild('Host', $db_host);
		$connectResultXML->addChild('Database', $db_name);
		
		$connectErrorXML = $connectionXML->addChild('Error');
		$connectErrorXML->addChild('Error_Code', $e->getCode());
		$connectErrorXML->addChild('Details', $e->getMessage());
		
		$errorDescription = "Failed to Connect to Database.";
		
		$errorArray = array(
			"user_id" => $userID,
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
		$errorLogDetailsXML->addChild('file_name', $fileName);
		$errorLogDetailsXML->addChild('file_directory', $fileDirectory);
		$errorLogDetailsXML->addChild('request_uri', $requestURI);
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