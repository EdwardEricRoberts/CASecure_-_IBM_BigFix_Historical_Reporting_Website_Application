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
				$metaXML->addChild('Name', "Update Computer Group List");
				$metaXML->addChild('Description', "This query checks to see if the list of Computer Groups in the database is current with the BigFix Server.");
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
				
				$fetchBigFixLoginXML = $actionsXML->addChild('Action');
				$fetchBigFixLoginXML->addAttribute('type', 'BigFix Console credentials for the Current User');
				$fetchBigFixLoginXML->addChild('Status', "Success");
				$fetchBigFixLoginXML->addChild('Details', 'Fetched the login credentials for the BigFix API.');
				
				$cgURL = "http://localhost/CASecure_MSP_ver_1.0/proxies/ComputerGroupsBySiteName.php?user=".$bigFixUser."&pass=".$bigFixPassword."&serv=".$bigFixServer;
		
				$cgXML = simplexml_load_file($cgURL);
				
				$rowCount = 0;
				$liveComputerGroups = array();
				
				foreach ($cgXML->Query->Result->Tuple as $computerGroup) {
					
					$cgName = "";
					$parentSite = "";
					$type = "";
					$i = 0;
					
					foreach ($computerGroup->Answer as $key => $value) {
						if ($i == 1) {
							$cgName = $value;
						}
						else if ($i == 3) {
							$parentSite = $value;
						}
						else if ($i == 2) {
							$type = $value;
						}
						$i++;
					}
					
					$liveComputerGroups[$rowCount] = 
						array(
							"computer_group_name" => $cgName->__toString(), 
							"parent_site" => $parentSite->__toString(), 
							"computer_group_type" => $type->__toString()
						);
					
					$rowCount++;
				}
				//print_r($liveComputerGroups);
				
				$liveComputerGroupsXML = $actionsXML->addChild('Action');
				$liveComputerGroupsXML->addAttribute('type', 'List of Live Computer Groups');
				$liveComputerGroupsXML->addChild('Status', 'Success');
				$liveComputerGroupsXML->addChild('Details', 'Fetched List of all Computer Groups currently Live on the BigFix API.');
				$liveComputerGroupsRowsXML = $liveComputerGroupsXML->addChild('Live_Computer_Groups');
				foreach ($liveComputerGroups as $liveComputerGroup) {
					$liveComputerGroupsRowXML = $liveComputerGroupsRowsXML->addChild('Row');
					$liveComputerGroupsRowXML->addChild('computer_group_name', $liveComputerGroup['computer_group_name']);
					$liveComputerGroupsRowXML->addChild('parent_site', $liveComputerGroup['parent_site']);
					$liveComputerGroupsRowXML->addChild('computer_group_type', $liveComputerGroup['computer_group_type']);
				}
				$liveComputerGroupsXML->addChild('Count', sizeOf($liveComputerGroups));
				
				$liveComputerGroupNames = array();
				for($i = 0; $i< sizeOf($liveComputerGroups); $i++) {
					$liveComputerGroupNames[$i] = $liveComputerGroups[$i]["computer_group_name"];
				}
				//print_r($liveComputerGroupNames);
				
				//echo "<br/><br/>";
				$databaseComputerGroups = array();
				
				$cgFetchSQL = 
					"SELECT * ".
					"FROM computer_groups;";
				$cgFetchQuery = $db->query($cgFetchSQL);
				$databaseComputerGroups = $cgFetchQuery->fetchAll(PDO::FETCH_ASSOC);
				//print_r($databaseComputerGroups);
				
				$databaseComputerGroupsXML = $actionsXML->addChild('Action');
				$databaseComputerGroupsXML->addAttribute('type', 'List of Computer Groups originally in the Database');
				$databaseComputerGroupsXML->addChild('Status', 'Success');
				$databaseComputerGroupsXML->addChild('Details', 'Fetched List of all Computer Groups from the database.');
				$databaseComputerGroupsRowsXML = $databaseComputerGroupsXML->addChild('Database_Computer_Groups');
				foreach ($databaseComputerGroups as $databaseComputerGroup) {
					$databaseComputerGroupsRowXML = $databaseComputerGroupsRowsXML->addChild('Row');
					$databaseComputerGroupsRowXML->addChild('computer_group_name', $databaseComputerGroup['computer_group_name']);
					$databaseComputerGroupsRowXML->addChild('parent_site', $databaseComputerGroup['parent_site']);
					$databaseComputerGroupsRowXML->addChild('computer_group_type', $databaseComputerGroup['computer_group_type']);
				}
				$databaseComputerGroupsXML->addChild('Count', sizeOf($databaseComputerGroups));
				
				$databaseComputerGroupNames = array();
				for($i = 0; $i< sizeOf($databaseComputerGroups); $i++) {
					$databaseComputerGroupNames[$i] = $databaseComputerGroups[$i]["computer_group_name"];
				}
				
				$missingComputerGroupNames = array_diff($liveComputerGroupNames, $databaseComputerGroupNames);
				$outdatedComputerGroupNames = array_diff($databaseComputerGroupNames, $liveComputerGroupNames);
				//print_r($missingComputerGroupNames);
				
				$compareComputerGroupsXML = $actionsXML->addChild('Action');
				$compareComputerGroupsXML->addAttribute('type', 'Compare lists of Live and Database Computer Groups');
				$compareComputerGroupsXML->addChild('Status', 'Success');
				$compareComputerGroupsXML->addChild('Details', 'Compared the lists of Live and Database Computer Groups to see which ones need to be added to or removed from the database.');
				
				$missingComputerGroups = array();
				$outdatedComputerGroups = array();
				
				if ($missingComputerGroupNames == array() && $outdatedComputerGroupNames == array()) {
					$compareComputerGroupsXML->addChild('Missing_Database_Computer_Groups');
					$compareComputerGroupsXML->addChild('Missing_Count', 0);
					$compareComputerGroupsXML->addChild('Outdated_Database_Computer_Groups');
					$compareComputerGroupsXML->addChild('Outdated_Count', 0);
					
					//echo "Database Computer Group list is currently up to date.<br/>";
					$updateDatabaseXML = $actionsXML->addChild('Action');
					$updateDatabaseXML->addAttribute('type', 'Rectify the Database');
					$updateDatabaseXML->addChild('Status', 'N/A');
					$updateDatabaseXML->addChild('Description', 'Database is already currently Up to Date.  No Action Required.');
				}
				else {
					//echo "Database Computer Group List Updated.<br/>";
					if ($missingComputerGroupNames != array()) {
						$j = 0;
						foreach($missingComputerGroupNames as $liveIndex => $missingComputerGroupName) {
							$missingComputerGroups[$j] = $liveComputerGroups[$liveIndex];
							$j++;
						}
						//print_r($missingComputerGroups);
						//echo "<br/>";
						
						$missingComputerGroupsXML = $compareComputerGroupsXML->addChild('Missing_Database_Computer_Groups');
						foreach ($missingComputerGroups as $missingComputerGroup) {
							$missingComputerGroupRowXML = $missingComputerGroupsXML->addChild('Row');
							$missingComputerGroupRowXML->addChild('computer_group_name', $missingComputerGroup['computer_group_name']);
							$missingComputerGroupRowXML->addChild('parent_site', $missingComputerGroup['parent_site']);
							$missingComputerGroupRowXML->addChild('computer_group_type', $missingComputerGroup['computer_group_type']);
						}
						$compareComputerGroupsXML->addChild('Missing_Count', sizeOf($missingComputerGroupNames));
						
						$updateCGsQuery = pdoMultiInsert("computer_groups", $missingComputerGroups, $db);
						$updateCGsQuery->execute();
						
						//foreach($missingComputerGroups as $missingComputerGroup) {
						//	echo 'Computer Group "'.$missingComputerGroup["computer_group_name"].'" of parent Site "'.$missingComputerGroup["parent_site"].'" added to the database.<br/>';
						//}
						
						$insertComputerGroupsXML = $actionsXML->addChild('Action');
						$insertComputerGroupsXML->addAttribute('type', 'Rectify the Database');
						$insertComputerGroupsXML->addChild('Status', 'Success');
						$insertComputerGroupsXML->addChild('Description', 'Inserted the missing Computer Groups into the database.');
						
						$changeLogMessage = 'Added new Computer Group(s) ';
						foreach($missingComputerGroups as $key => $missingComputerGroup) {
							if($key != 0) {
								$changeLogMessage .= ', ';
							}
							$changeLogMessage .= '"'.$missingComputerGroup["computer_group_name"].'" ('.$missingComputerGroup["parent_site"].')';
						}
						$changeLogMessage .= ' to the database. ';
						
						$changeLogSQL = 
							"INSERT INTO database_change_log ".
							"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
							"VALUES (:userID, :timestamp, 'BigFix Data Synchronization', :message, 'computer_groups');";
						$changeLogQuery = $db->prepare($changeLogSQL);
						$changeLogQuery->bindParam(':userID', $userID, PDO::PARAM_STR);
						$changeLogQuery->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
						$changeLogQuery->bindParam(':message', $changeLogMessage, PDO::PARAM_STR);
						$changeLogQuery->execute();
						
						$changeLogXML = $resultXML->addChild('Change_Log');
						$changeLogXML->addChild('Status', 'Transaction Logged');
						$changeDetailsXML = $changeLogXML->addChild('Details');
						$changeDetailsXML->addChild('timestamp', $timestamp);
						$changeDetailsXML->addChild('type_of_change', 'BigFix Data Synchronization');
						$changeDetailsXML->addChild('action_taken', $changeLogMessage);
						$changeDetailsXML->addChild('affected_tables', 'computer_groups');
						
					}
					if ($outdatedComputerGroupNames != array()) {
						$j = 0;
						foreach($outdatedComputerGroupNames as $dbIndex => $outdatedComputerGroupName) {
							$outdatedComputerGroups[$j] = $databaseComputerGroups[$dbIndex];
							$j++;
						}
						//print_r($outdatedComputerGroups);
						//echo "<br/>";
						$outdatedComputerGroupsXML = $compareComputerGroupsXML->addChild('Outdated_Database_Computer_Groups');
						foreach ($outdatedComputerGroups as $outdatedComputerGroup) {
							$outdatedComputerGroupRowXML = $outdatedComputerGroupsXML->addChild('Row');
							$outdatedComputerGroupRowXML->addChild('computer_group_name', $outdatedComputerGroup['computer_group_name']);
							$outdatedComputerGroupRowXML->addChild('parent_site', $outdatedComputerGroup['parent_site']);
							$outdatedComputerGroupRowXML->addChild('computer_group_type', $outdatedComputerGroup['computer_group_type']);
						}
						$compareComputerGroupsXML->addChild('Outdated_Count', sizeOf($outdatedComputerGroupNames));
						
						foreach($outdatedComputerGroups as $key => $outdatedComputerGroup) {
							$deleteSQL = 
								"DELETE ".
								"FROM computer_groups ".
								"WHERE computer_group_name = :outdatedComputerGroupName";
							$deleteCGsQuery = $db->prepare($deleteSQL);
							$deleteCGsQuery->bindParam(':outdatedComputerGroupName', $outdatedComputerGroup["computer_group_name"], PDO::PARAM_STR);
							$deleteCGsQuery->execute();
						}
						
						$RemoveOutdatedComputerGroupsXML = $actionsXML->addChild('Action');
						$RemoveOutdatedComputerGroupsXML->addAttribute('type', 'Rectify the Database');
						$RemoveOutdatedComputerGroupsXML->addChild('Status', 'Success');
						$RemoveOutdatedComputerGroupsXML->addChild('Description', 'Removed the outdated Computer Groups from the database.');
						
						//foreach($outdatedComputerGroups as $outdatedComputerGroup) {
						//	echo ' Computer Group "'.$outdatedComputerGroup["computer_group_name"].'" of parent Site "'.$outdatedComputerGroup["parent_site"].'" removed from the database, as it is no longer supported by BigFix.<br/>';
						//}
						
						
						
						$changeLogMessage = 'Removed Outdated Computer Group(s) ';
						foreach ($outdatedComputerGroups as $key => $outdatedComputerGroup) {
							if($key != 0) {
								$changeLogMessage .= ', ';
							}
							$changeLogMessage .= '"'.$outdatedComputerGroup["computer_group_name"].'" ('.$outdatedComputerGroup["parent_site"].')';
						}
						$changeLogMessage .= ' from the database. ';
						
						$changeLogSQL = 
							"INSERT INTO database_change_log ".
							"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
							"VALUES (:userID, :timestamp, 'BigFix Data Synchronization', :message, 'computer_groups');";
						$changeLogQuery = $db->prepare($changeLogSQL);
						$changeLogQuery->bindParam(':userID', $userID, PDO::PARAM_STR);
						$changeLogQuery->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
						$changeLogQuery->bindParam(':message', $changeLogMessage, PDO::PARAM_STR);
						$changeLogQuery->execute();
						
						$changeLogXML = $resultXML->addChild('Change_Log');
						$changeLogXML->addChild('Status', 'Transaction Logged');
						$changeDetailsXML = $changeLogXML->addChild('Details');
						$changeDetailsXML->addChild('timestamp', $timestamp);
						$changeDetailsXML->addChild('type_of_change', 'BigFix Data Synchronization');
						$changeDetailsXML->addChild('action_taken', $changeLogMessage);
						$changeDetailsXML->addChild('affected_tables', 'computer_groups');
					}
				}
			$db->commit();
		}
		catch (\PDOException $e) {
			$errorXML = $queryXML->addChild('Error');
			if ($bigFixLoginQuery->errorCode() != 0) {
				$fetchBigFixLoginXML = $actionsXML->addChild('Action');
				$fetchBigFixLoginXML->addAttribute('type', 'BigFix Console credentials for the Current User');
				$fetchBigFixLoginXML->addChild('Status', "Failure");
				$errorDescription = 'Unable to access BigFix Server credentials.';
				$fetchBigFixLoginXML->addChild('Details', $errorDescription);
			}
			else if ($cgFetchQuery->errorCode() != 0) {
				$databaseComputerGroupsXML = $actionsXML->addChild('Action');
				$databaseComputerGroupsXML->addAttribute('type', 'List of Computer Groups originally in the Database');
				$databaseComputerGroupsXML->addChild('Status', 'Failure');
				$errorDescription = 'Unable to retrieve list of Computer Groups from database.';
				$databaseComputerGroupsXML->addChild('Details', $errorDescription);
			}
			else if (isset($updateCGsQuery) && $updateCGsQuery->errorCode() != 0) {
				$insertComputerGroupsXML = $actionsXML->addChild('Action');
				$insertComputerGroupsXML->addAttribute('type', 'Rectify the Database');
				$insertComputerGroupsXML->addChild('Status', 'Failure');
				$errorDescription = 'Failed to Insert new Computer Groups into database.';
				$insertComputerGroupsXML->addChild('Description', $errorDescription);
			}
			else if (isset($deleteCGsQuery) && $deleteCGsQuery->errorCode() != 0) {
				$RemoveOutdatedComputerGroupsXML = $actionsXML->addChild('Action');
				$RemoveOutdatedComputerGroupsXML->addAttribute('type', 'Rectify the Database');
				$RemoveOutdatedComputerGroupsXML->addChild('Status', 'Failure');
				$errorDescription = 'Failed to Remove Outdated Computer Groups from database.';
				$RemoveOutdatedComputerGroupsXML->addChild('Description', $errorDescription);
			}
			else if (isset($changeLogQuery) && $changeLogQuery->errorCode() != 0) {
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