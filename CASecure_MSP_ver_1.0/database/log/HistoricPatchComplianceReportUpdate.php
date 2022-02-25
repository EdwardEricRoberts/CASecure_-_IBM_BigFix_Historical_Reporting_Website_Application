<?php
	
	//header('Content-type: application/xml');
	
	$timestamp = date("Y-m-d H:i:sO");
	//echo "\nTime = ".$timestamp."\n";//"<br />";
	
	$issuer = $_GET['cid'];
	
	//print_r($userGroupIDs);
	
	$old_db_1_name = "postgres";
	$old_db_2_name = "CASecure1";
	
	$db_host = "localhost";
	$db_name = "CASecure2";
	$db_username = "postgres";
	$db_password = "abc.123";
	
	$xml= new SimpleXMLElement('<PDO/>'); 
	$connectionXML = $xml->addChild('Connection');
	$connectionResultsXML = $connectionXML->addChild('Results');
	try {
		$connectResultXML = $connectionResultsXML->addChild('Result');
		
		//$dsn = 'pgsql:host='.$db_host.';dbname='.$db_name
		$db_old_1 = new PDO('pgsql:host='.$db_host.';dbname='.$old_db_1_name,$db_username,$db_password);
		//$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		$db_old_1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		$connectResultXML->addChild('Status', 'Connected');
		$connectResultXML->addChild('Message', "Database Connection Successful");
		$connectResultXML->addChild('Host', $db_host);
		$connectResultXML->addChild('Database', $old_db_1_name);
		
		/*
		try {
			$connectResultXML = $connectionResultsXML->addChild('Result');
			
			$db_old_2 = new PDO('pgsql:host='.$db_host.';dbname='.$old_db_2_name,$db_username,$db_password);
			//$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
			$db_old_2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$connectResultXML->addChild('Status', 'Connected');
			$connectResultXML->addChild('Message', "Database Connection Successful");
			$connectResultXML->addChild('Host', $db_host);
			$connectResultXML->addChild('Database', $old_db_2_name);
		*/	
			try {
				$connectResultXML = $connectionResultsXML->addChild('Result');
				
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
						$metaXML->addChild('Name', "Historic Patch Compliance Report Data Transfer");
						$metaXML->addChild('Description', "Transfers and combines data from past databases into the current database for the Historic Patch Compliance Report.");
						$paramsXML = $metaXML->addChild('Parameters');
						$param1XML = $paramsXML->addChild('Parameter');
						$param1XML->addChild('Name', 'Current User ID');
						$param1XML->addChild('URL', 'cid');
						$param1XML->addChild('Value', $issuer);
						$param1XML->addChild('Description', 'The User ID of the current User');
						$resultXML = $queryXML->addChild('Result');
						
						$start = microtime(true);
						//
						$actionsXML = $resultXML->addChild('Actions');
						//microsoft_patch_compliance_summary_2
						//id, timestamp, applicable, installed, outstanding, compliance, computer_group, logged_by
						$postgresDBReportDataSQL = 
							"SELECT 'Enterprise Security' AS site, computer_group, timestamp, applicable AS applicable_count, installed AS installed_count, outstanding AS outstanding_count, ".
							"( ".
								"CASE ".
									"WHEN compliance = 'NAN%' THEN '100%' ".
									"ELSE compliance ".
								"END ".
							") AS compliance, ".
							"NULL AS system_count ".
							"FROM microsoft_patch_compliance_summary_2 ".
							"WHERE computer_group <> 'All Machines' ".
							"ORDER BY timestamp;";
						$postgresDBReportDataQuery = $db_old_1->query($postgresDBReportDataSQL);
						$postgresDBReportDataArray = $postgresDBReportDataQuery->fetchAll(PDO::FETCH_ASSOC);
						//print_r($postgresDBReportDataArray);
						
						//historic_patch_compliance_report
						//log_id, site, computer_group, timestamp, applicable_count, installed_count, outstanding_count, compliance, system_count
						
						$casecure1DBReportDataSQL =
							"SELECT site, computer_group, timestamp, applicable_count, installed_count, outstanding_count, compliance, system_count ".
							"FROM historic_patch_compliance_report ".
							"ORDER BY timestamp;";
						$casecure1DBReportDataQuery = $db_old_2->query($casecure1DBReportDataSQL);
						$casecure1DBReportDataArray = $casecure1DBReportDataQuery->fetchAll(PDO::FETCH_ASSOC);
						//print_r($casecure1DBReportDataArray);
						
						function dateSort($subArrayA, $subArrayB) {
							$timestampStringA = $subArrayA['timestamp'];
							$timestampStringB = $subArrayB['timestamp'];
							
							//if ($timestampStringA == $timestampStringB) {
							//	return 0;
							//}
							
							$timestampA = strtotime($timestampStringA);
							$timestampB = strtotime($timestampStringB);
							
							//return ($timestampA < $timestampB) ? -1 : 1;
							return ($timestampA - $timestampB);
						}
						
						$casecure2DBReportDataArray = array_merge($postgresDBReportDataArray, $casecure1DBReportDataArray);
						usort($casecure2DBReportDataArray, 'dateSort');
						//$casecure2DBReportDataArray = usort($mergedArray, function ($a, $b) {return (strtotime($a['timestamp']) - strtotime($b['timestamp']));});
						
						//print_r($casecure2DBReportDataArray);
						echo sizeOf($casecure2DBReportDataArray);
						
						//$halfCount = floor(count($casecure2DBReportDataArray)/2);
						
						$seventhCount = floor(count($casecure2DBReportDataArray)/7);
						$twoSeventhCount = floor(2*(count($casecure2DBReportDataArray)/7));
						$threeSeventhCount = floor(3*(count($casecure2DBReportDataArray)/7));
						$fourSeventhCount = floor(4*(count($casecure2DBReportDataArray)/7));
						$fiveSeventhCount = floor(5*(count($casecure2DBReportDataArray)/7));
						$sixSeventhCount = floor(6*(count($casecure2DBReportDataArray)/7));
						
						$casecure2DBReportDataArray1 = array_slice($casecure2DBReportDataArray, 0, $seventhCount);
						//print_r($casecure2DBReportDataArray1);
						$casecure2DBReportDataArray2 = array_slice($casecure2DBReportDataArray, $seventhCount, $seventhCount);
						//print_r($casecure2DBReportDataArray2);
						$casecure2DBReportDataArray3 = array_slice($casecure2DBReportDataArray, $twoSeventhCount, $seventhCount);
						//print_r($casecure2DBReportDataArray3);
						$casecure2DBReportDataArray4 = array_slice($casecure2DBReportDataArray, $threeSeventhCount, $seventhCount);
						//print_r($casecure2DBReportDataArray4);
						$casecure2DBReportDataArray5 = array_slice($casecure2DBReportDataArray, $fourSeventhCount, $seventhCount);
						//print_r($casecure2DBReportDataArray5);
						$casecure2DBReportDataArray6 = array_slice($casecure2DBReportDataArray, $fiveSeventhCount, $seventhCount);
						//print_r($casecure2DBReportDataArray6);
						$casecure2DBReportDataArray7 = array_slice($casecure2DBReportDataArray, $sixSeventhCount);
						//print_r($casecure2DBReportDataArray7);
						
						
						//$casecure2DataInsertQuery1 = pdoMultiInsert('historic_patch_compliance_report', $casecure2DBReportDataArray1, $db);
						//$casecure2DataInsertQuery2 = pdoMultiInsert('historic_patch_compliance_report', $casecure2DBReportDataArray2, $db);
						//$casecure2DataInsertQuery3 = pdoMultiInsert('historic_patch_compliance_report', $casecure2DBReportDataArray3, $db);
						//$casecure2DataInsertQuery4 = pdoMultiInsert('historic_patch_compliance_report', $casecure2DBReportDataArray4, $db);
						//$casecure2DataInsertQuery5 = pdoMultiInsert('historic_patch_compliance_report', $casecure2DBReportDataArray5, $db);
						//$casecure2DataInsertQuery6 = pdoMultiInsert('historic_patch_compliance_report', $casecure2DBReportDataArray6, $db);
						//$casecure2DataInsertQuery7 = pdoMultiInsert('historic_patch_compliance_report', $casecure2DBReportDataArray7, $db);
						//$casecure2DataInsertQuery1->execute();
						//$casecure2DataInsertQuery2->execute();
						//$casecure2DataInsertQuery3->execute();
						//$casecure2DataInsertQuery4->execute();
						//$casecure2DataInsertQuery5->execute();
						//$casecure2DataInsertQuery6->execute();
						//$casecure2DataInsertQuery7->execute();
						
						/*
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
						*/
						$end = microtime(true);
						/*
						$changeLogXML = $resultXML->addChild('Change_Log');
						$changeLogXML->addChild('Status', 'Transaction Logged');
						$changeDetailsXML = $changeLogXML->addChild('Details');
						$changeDetailsXML->addChild('timestamp', $timestamp);
						$changeDetailsXML->addChild('type_of_change', 'Alert Creation');
						$changeDetailsXML->addChild('action_taken', $changeLogMessage);
						$changeDetailsXML->addChild('affected_tables', 'alerts, user_group_alerts, user_alerts');
						*/
						$evaluationXML = $queryXML->addChild('Evaluation');
						$evaluationXML->addChild('Time', round((($end - $start) * 1000), 3).'ms');
						//$evaluationXML->addChild('Total_Number_of_Altered_Rows', (1 + sizeOf($userGroupIDs) + sizeOf($userIDs)));
						
					$db->commit();
				}
				catch (\PDOException $e) {
					$errorXML = $queryXML->addChild('Error');
					/*
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
					*/
					$errorCode = $e->getCode();
					$errorMessage = $e->getMessage();
					
					$errorXML->addChild('Error_Code', $errorCode);
					$errorXML->addChild('Details', $errorMessage);
					
					$db->rollBack();
					/*
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
					*/
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
				
				echo $e->getMessage();
			}
		/*
		}
		catch(PDOException $e) {
			$connectResultXML->addChild('Status', 'Failure');
			$connectResultXML->addChild('Message', "Failed to Connect to Database.  Please Email Site Administrator.");
			$connectResultXML->addChild('Host', $db_host);
			$connectResultXML->addChild('Database', $old_db_2_name);
			
			$connectErrorXML = $connectionXML->addChild('Error');
			$connectErrorXML->addChild('Error_Code', $e->getCode());
			$connectErrorXML->addChild('Details', $e->getMessage());
		}
		*/
	}
	catch(PDOException $e) {
		$connectResultXML->addChild('Status', 'Failure');
		$connectResultXML->addChild('Message', "Failed to Connect to Database.  Please Email Site Administrator.");
		$connectResultXML->addChild('Host', $db_host);
		$connectResultXML->addChild('Database', $old_db_1_name);
		
		$connectErrorXML = $connectionXML->addChild('Error');
		$connectErrorXML->addChild('Error_Code', $e->getCode());
		$connectErrorXML->addChild('Details', $e->getMessage());
	}
	catch(Exception $e) {
		$connectResultXML->addChild('Status', 'Failure');
		$connectResultXML->addChild('Message', "An Unexpected Error Occured.  Please try again. If Issue Persists, Please Email Site Administrator.");
		$connectResultXML->addChild('Host', $db_host);
		$connectResultXML->addChild('Database', $old_db_1_name);
		
		$connectErrorXML = $connectionXML->addChild('Error');
		$connectErrorXML->addChild('Error_Code', $e->getCode());
		$connectErrorXML->addChild('Details', $e->getMessage());
	}
	finally {
		//echo $xml->asXML();
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