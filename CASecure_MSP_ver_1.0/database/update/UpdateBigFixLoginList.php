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
	$db_name = "CASecure2";
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
				$metaXML->addChild('Name', "Update BigFix Login");
				$metaXML->addChild('Description', "This query checks to see if the list of BigFix Logins in the database is current with the BigFix Server.");
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
				
				$liveBigFixUsersListURL = "http://localhost/CASecure_MSP_ver_1.0/proxies/BigFixUsersList.php?user=".$bigFixUser."&pass=".$bigFixPassword."&serv=".$bigFixServer;
		
				$liveBigFixUsersListXML = simplexml_load_file($liveBigFixUsersListURL);
				
				$rowCount = 0;
				$liveUsers = array();
				
				foreach ($liveBigFixUsersListXML->Query->Result->Tuple as $user) {
					
					$bigFixUserName = "";
					$serverName = "";
					$i = 0;
					
					foreach ($user->Answer as $key => $value) {
						if ($i == 1) {
							$bigFixUserName = $value;
						}
						else if ($i == 2) {
							$serverName = $value;
						}
						$i++;
					}
					
					$liveUsers[$rowCount] = 
						array(
							"bigfix_user_name" => $bigFixUserName->__toString(), 
							"bigfix_server" => $serverName->__toString()
						);
					
					$rowCount++;
				}
				//print_r($liveUsers);
				$liveUserNames = array();
				for($i = 0; $i< sizeOf($liveUsers); $i++) {
					$liveUserNames[$i] = $liveUsers[$i]["bigfix_user_name"];
				}
				//print_r($liveUserNames);
				
				$liveBigFixUserNamesXML = $actionsXML->addChild('Action');
				$liveBigFixUserNamesXML->addAttribute('type', 'List of Live BigFix User Names');
				$liveBigFixUserNamesXML->addChild('Status', 'Success');
				$liveBigFixUserNamesXML->addChild('Details', 'Fetched List of all BigFix Console Users currently Live on the BigFix API.');
				$liveBigFixUserNamesRowsXML = $liveBigFixUserNamesXML->addChild('Live_BigFix_Users');
				foreach ($liveUserNames as $liveUserName) {
					$liveBigFixUserNamesRowsXML->addChild('bigfix_user_name', $liveUserName);
				}
				$liveBigFixUserNamesXML->addChild('Count', sizeOf($liveUserNames));
				
				//echo "<br/><br/>";
				$databaseUsers = array();
				
				$userFetchSQL = 
					"SELECT * ".
					"FROM bigfix_logins;";
				
				$userFetchQuery = $db->prepare($userFetchSQL);
				$userFetchQuery->execute();
				
				$databaseUsers = $userFetchQuery->fetchAll(PDO::FETCH_ASSOC);
				//print_r($databaseUsers);
				
				$databaseUserNames = array();
				for($i = 0; $i< sizeOf($databaseUsers); $i++) {
					$databaseUserNames[$i] = $databaseUsers[$i]["bigfix_user_name"];
				}
				
				$databaseBigFixUserNamesXML = $actionsXML->addChild('Action');
				$databaseBigFixUserNamesXML->addAttribute('type', 'List of BigFix User Names in the database');
				$databaseBigFixUserNamesXML->addChild('Status', 'Success');
				$databaseBigFixUserNamesXML->addChild('Details', 'Fetched List of all BigFix Console Users listed in the database.');
				$databaseBigFixUserNamesRowsXML = $databaseBigFixUserNamesXML->addChild('Database_BigFix_Users');
				$databaseBigFixUserNamesRowsXML->addAttribute('table', 'bigfix_logins');
				foreach ($databaseUserNames as $databaseUserName) {
					$databaseBigFixUserNamesRowsXML->addChild('bigfix_user_name', $databaseUserName);
				}
				$databaseBigFixUserNamesXML->addChild('Count', sizeOf($liveUserNames));
				
				$missingUserNames = array_diff($liveUserNames, $databaseUserNames);
				$outdatedUserNames = array_diff($databaseUserNames, $liveUserNames);
				//print_r($missingUserNames);
				
				$compareBigFixUserNamesXML = $actionsXML->addChild('Action');
				$compareBigFixUserNamesXML->addAttribute('type', 'Compare lists of Live and Database BigFix Users');
				$compareBigFixUserNamesXML->addChild('Status', 'Success');
				$compareBigFixUserNamesXML->addChild('Details', 'Compared the lists of Live and Database BigFix Users to see which ones need to be added to or removed from the database.');
				$missingBigFixUserNamesXML = $compareBigFixUserNamesXML->addChild('Missing_Database_Users');
				foreach ($missingUserNames as $missingUserName) {
					$missingBigFixUserNamesXML->addChild('bigfix_user_name', $missingUserName);
				}
				$compareBigFixUserNamesXML->addChild('Missing_Count', sizeOf($missingUserNames));
				$outdatedBigFixUserNamesXML = $compareBigFixUserNamesXML->addChild('Outdated_Database_Users');
				foreach ($outdatedUserNames as $outdatedUserName) {
					$outdatedBigFixUserNamesXML->addChild('bigfix_user_name', $outdatedUserName);
				}
				$compareBigFixUserNamesXML->addChild('Outdated_Count', sizeOf($outdatedUserNames));
				
				$missingUsers = array();
				$outdatedUsers = array();
				
				$updateUsersQuery = $db->query('SELECT');
				$deleteUsersQuery = $db->query('SELECT');
				
				if ($missingUserNames == array() && $outdatedUserNames == array()) {
					//echo "Database BigFix Logins list is currently up to date.<br/>";
					$updateDatabaseXML = $actionsXML->addChild('Action');
					$updateDatabaseXML->addAttribute('type', 'Rectify the Database');
					$updateDatabaseXML->addChild('Status', 'N/A');
					$updateDatabaseXML->addChild('Description', 'Database is already currently Up to Date.  No Action Required.');
				}
				else {
					//echo "Database BigFix Logins List Updated.<br/>";
					if ($missingUserNames != array()) {
						$j = 0;
						foreach($missingUserNames as $liveIndex => $missingUserName) {
							$missingUsers[$j] = $liveUsers[$liveIndex];
							$j++;
						}
						//print_r($missingUsers);
						//echo "<br/>";
						
						$updateUsersQuery = pdoMultiInsert("bigfix_logins", $missingUsers, $db);
						$updateUsersQuery->execute();
						
						$InsertNewBigFixUsersXML = $actionsXML->addChild('Action');
						$InsertNewBigFixUsersXML->addAttribute('type', 'Rectify the Database');
						$InsertNewBigFixUsersXML->addChild('Status', 'Success');
						$InsertNewBigFixUsersXML->addChild('Description', 'Inserted the missing BigFix Users into the database.');
						
						$changeLogMessage = 'Added new BigFix User(s) ';
						foreach($missingUsers as $key => $missingUser) {
							if($key != 0) {
								$changeLogMessage .= ', ';
							}
							$changeLogMessage .= '"'.$missingUser["bigfix_user_name"].'" ('.$missingUser["bigfix_server"].')';
						}
						$changeLogMessage .= ' to the database. ';
						
						$changeLogSQL = 
							"INSERT INTO database_change_log ".
							"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
							"VALUES (:userID, :timestamp, 'BigFix Data Synchronization', :message, 'bigfix_logins');";
						$changeLogQuery = $db->prepare($changeLogSQL);
						$changeLogQuery->bindParam(':userID', $userID, PDO::PARAM_STR);
						$changeLogQuery->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
						$changeLogQuery->bindParam(':message', $message, PDO::PARAM_STR);
						$changeLogQuery->execute();
						
						$changeLogXML = $resultXML->addChild('Change_Log');
						$changeLogXML->addChild('Status', 'Transaction Logged');
						$changeDetailsXML = $changeLogXML->addChild('Details');
						$changeDetailsXML->addChild('timestamp', $timestamp);
						$changeDetailsXML->addChild('type_of_change', 'BigFix Data Synchronization');
						$changeDetailsXML->addChild('action_taken', $changeLogMessage);
						$changeDetailsXML->addChild('affected_tables', 'bigfix_logins');
					}
					if ($outdatedUserNames != array()) {
						$j = 0;
						foreach($outdatedUserNames as $dbIndex => $outdatedUserName) {
							$outdatedUsers[$j] = $databaseUsers[$dbIndex];
							$j++;
						}
						//print_r($outdatedUsers);
						//echo "<br/>";
						foreach($outdatedUsers as $key => $outdatedUser) {
							$deleteSQL = 
								"DELETE ".
								"FROM bigfix_logins ".
								"WHERE bigfix_user_name = :outdatedUserName";
							$deleteUsersQuery = $db->prepare($deleteSQL);
							$deleteUsersQuery->bindParam(':outdatedUserName', $outdatedUser["bigfix_user_name"], PDO::PARAM_STR);
							$deleteUsersQuery->execute();
						}
						
						$RemoveOutdatedBigFixUsersXML = $actionsXML->addChild('Action');
						$RemoveOutdatedBigFixUsersXML->addAttribute('type', 'Rectify the Database');
						$RemoveOutdatedBigFixUsersXML->addChild('Status', 'Success');
						$RemoveOutdatedBigFixUsersXML->addChild('Description', 'Deleted the outdated BigFix Users from the database.');
						//foreach($outdatedUsers as $outdatedUser) {
						//	echo 'BigFix User "'.$outdatedUser["bigfix_user_name"].'" of server "'.$outdatedUser["bigfix_server"].'" removed from the database, as it is no longer supported by BigFix.<br/>';
						//}
						
						$changeLogMessage = 'Removed Outdated BigFix User(s) ';
						foreach ($outdatedUsers as $key => $outdatedUser) {
							if($key != 0) {
													$changeLogMessage .= ', ';
							}
							$changeLogMessage .= '"'.$outdatedUser["bigfix_user_name"].'" ('.$outdatedUser["bigfix_server"].')';
						}
						$changeLogMessage .= ' from the database. ';
						
						$changeLogSQL = 
							"INSERT INTO database_change_log ".
							"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
							"VALUES (:userID, :timestamp, 'BigFix Data Synchronization', :message, 'bigfix_logins');";
						$changeLogQuery = $db->prepare($changeLogSQL);
						$changeLogQuery->bindParam(':userID', $userID, PDO::PARAM_STR);
						$changeLogQuery->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
						$changeLogQuery->bindParam(':message', $message, PDO::PARAM_STR);
						$changeLogQuery->execute();
						
						$changeLogXML = $resultXML->addChild('Change_Log');
						$changeLogXML->addChild('Status', 'Transaction Logged');
						$changeDetailsXML = $changeLogXML->addChild('Details');
						$changeDetailsXML->addChild('timestamp', $timestamp);
						$changeDetailsXML->addChild('type_of_change', 'BigFix Data Synchronization');
						$changeDetailsXML->addChild('action_taken', $changeLogMessage);
						$changeDetailsXML->addChild('affected_tables', 'bigfix_logins');
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
			else if ($userFetchQuery->errorCode() != 0) {
				$databaseBigFixUserNamesXML = $actionsXML->addChild('Action');
				$databaseBigFixUserNamesXML->addAttribute('type', 'List of BigFix User Names in the Database');
				$databaseBigFixUserNamesXML->addChild('Status', 'Failure');
				$errorDescription = 'Failed to retrieve List of all BigFix Console Users in the Database.';
				$databaseBigFixUserNamesXML->addChild('Details', $errorDescription);
			}
			else if (isset($updateUsersQuery) && $updateUsersQuery->errorCode() != 0) {
				$InsertNewBigFixUsersXML = $actionsXML->addChild('Action');
				$InsertNewBigFixUsersXML->addAttribute('type', 'Rectify the Database');
				$InsertNewBigFixUsersXML->addChild('Status', 'Failure');
				$errorDescription = 'Failed to Insert the missing BigFix Users into the database.';
				$InsertNewBigFixUsersXML->addChild('Description', $errorDescription);
			}
			else if (isset($deleteUsersQuery) && $deleteUsersQuery->errorCode() != 0) {
				$RemoveOutdatedBigFixUsersXML = $actionsXML->addChild('Action');
				$RemoveOutdatedBigFixUsersXML->addAttribute('type', 'Rectify the Database');
				$RemoveOutdatedBigFixUsersXML->addChild('Status', 'Success');
				$errorDescription = 'Deleted the outdated BigFix Users from the database.';
				$RemoveOutdatedBigFixUsersXML->addChild('Description', $errorDescription);
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
			
			$db->rollback();
			
			try {
				$errorLogSQL = 
					"INSERT INTO error_log ".
					"(user_id, description, error_code, error_message, exception_type, timestamp) ".
					"VALUES (:userID, :description, :errorCode, :errorMessage, 'PDO Query', :timestamp);";
				$errorLogQuery = $db->prepare($errorLogSQL);
				$errorLogQuery->bindParam(':userID', $updaterSQL, PDO::PARAM_STR);
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