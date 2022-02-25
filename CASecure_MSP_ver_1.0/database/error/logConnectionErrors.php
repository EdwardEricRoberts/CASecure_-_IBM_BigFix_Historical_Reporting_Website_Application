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
				$metaXML->addChild('Name', "Log Connection Errors");
				$metaXML->addChild('Description', "Collects the data for Connection based Errors from the CSV file that stores them, and logs them in the database.");
				$paramsXML = $metaXML->addChild('Parameters');
				$param1XML = $paramsXML->addChild('Parameter');
				$param1XML->addChild('Name', 'Current User ID');
				$param1XML->addChild('URL', 'cid');
				$param1XML->addChild('Value', $userID);
				$param1XML->addChild('Description', 'The User ID of the current User');
				$resultXML = $queryXML->addChild('Result');
				
				$start = microtime(true);
				
				$actionsXML = $resultXML->addChild('Actions');
				
				if(file_exists('C:\Bitnami\wappstack-7.3.9-0\apache2\htdocs\CASecure_MSP_ver_1.0\database\error\ConnectionErrors.csv')) {
				
					$connectionErrorsFile = fopen('C:\Bitnami\wappstack-7.3.9-0\apache2\htdocs\CASecure_MSP_ver_1.0\database\error\ConnectionErrors.csv', 'r');
					
					if ($connectionErrorsFile !== FALSE) {
						
						$connectionErrorsArray = array();
						while(($rowData = fgetcsv($connectionErrorsFile)) !== FALSE) { //(! feof($connectionErrorsFile)) {
							$connectionErrorsArray[] = array(
								"user_id" => ($rowData[0] == "") ? null : $rowData[0], 
								"description" => $rowData[1], 
								"error_code" => ($rowData[2] == "") ? null : $rowData[2], 
								"error_message" => $rowData[3], 
								"exception_type" => $rowData[4], 
								"timestamp" => $rowData[5], 
								"file_name" => $rowData[6], 
								"file_directory" => $rowData[7], 
								"request_uri" => $rowData[8] 
							);
						}
						fclose($connectionErrorsFile);
						//print_r($connectionErrorsArray);
						
						$getConnectionErrorsXML = $actionsXML->addChild('Action');
						$getConnectionErrorsXML->addAttribute('type', 'Collect Connection Errors from CSV Log File');
						$getConnectionErrorsXML->addChild('Status', "Success");
						$getConnectionErrorsXML->addChild('Details', 'Error Data collected from Error Log File.');
						
						$logConnectionErrorsQuery = pdoMultiInsert('error_log', $connectionErrorsArray, $db);
						$logConnectionErrorsQuery->execute();
						
						$logConnectionErrorsXML = $actionsXML->addChild('Action');
						$logConnectionErrorsXML->addAttribute('type', 'Log the collected Connection Errors in the database');
						$logConnectionErrorsXML->addChild('Status', "Success");
						$logConnectionErrorsXML->addChild('Details', 'Connection Errors were successfully logged in the error_log table.');
						$connectionErrorRowsXML = $logConnectionErrorsXML->addChild('Inserted_Rows');
						$connectionErrorRowsXML->addAttribute('table', 'error_log');
						
						foreach($connectionErrorsArray as $connectionError) {
							$rowXML = $connectionErrorRowsXML->addChild('Row');
							$rowXML->addChild('user_id', $connectionError['user_id']);
							$rowXML->addChild('description', $connectionError['description']);
							$rowXML->addChild('error_code', $connectionError['error_code']);
							$rowXML->addChild('error_message', $connectionError['error_message']);
							$rowXML->addChild('exception_type', $connectionError['exception_type']);
							$rowXML->addChild('timestamp', $connectionError['timestamp']);
							$rowXML->addChild('file_name', htmlspecialchars($connectionError['file_name']));
							$rowXML->addChild('file_directory', htmlspecialchars($connectionError['file_directory']));
							$rowXML->addChild('request_uri', htmlspecialchars($connectionError['request_uri']));
						}
						
						$logConnectionErrorsXML->addChild('Count', sizeOf($connectionErrorsArray));
						
						unlink('ConnectionErrors.csv');
						
						$deleteCSVErrorFileXML = $actionsXML->addChild('Action');
						$deleteCSVErrorFileXML->addAttribute('type', 'Delete CSV Log File to prevent logging duplicate Errors');
						$deleteCSVErrorFileXML->addChild('Status', "Success");
						$deleteCSVErrorFileXML->addChild('Details', 'The CSV File was successfully Deleted.');
						
						$changeLogMessage = 'Logged Connection Errors stored in the "ConnectionErrors.csv" file.';
						
						$changeLogSQL = 
							"INSERT INTO database_change_log ".
							"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
							"VALUES (:userID, :timestamp, 'Log Connection Errors', :message, 'error_log');";
						$changeLogQuery = $db->prepare($changeLogSQL);
						$changeLogQuery->bindParam(':userID', $userID, PDO::PARAM_STR);
						$changeLogQuery->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
						$changeLogQuery->bindParam(':message', $changeLogMessage, PDO::PARAM_STR);
						$changeLogQuery->execute();
						
						$changeLogXML = $resultXML->addChild('Change_Log');
						$changeLogXML->addChild('Status', 'Transaction Logged');
						$changeDetailsXML = $changeLogXML->addChild('Details');
						$changeDetailsXML->addChild('timestamp', $timestamp);
						$changeDetailsXML->addChild('type_of_change', 'Log Connection Errors');
						$changeDetailsXML->addChild('action_taken', $changeLogMessage);
						$changeDetailsXML->addChild('affected_tables', 'error_log');
						
						$changeLogXML->addChild('Count', 1);
						
						$end = microtime(true);
				
						$evaluationXML = $queryXML->addChild('Evaluation');
						$evaluationXML->addChild('Time', round((($end - $start) * 1000), 3).'ms');
						$evaluationXML->addChild('Total_Number_of_Altered_Rows', sizeOf($connectionErrorsArray));
					}
				}
				else {
					$getConnectionErrorsXML = $actionsXML->addChild('Action');
					$getConnectionErrorsXML->addAttribute('type', 'Collect Connection Errors from CSV Log File');
					$getConnectionErrorsXML->addChild('Status', "Failure");
					$getConnectionErrorsXML->addChild('Details', 'No Connection Errors Need to be Logged.');
				}
				
			$db->commit();
		}
		catch (\PDOException $e) {
			$errorXML = $queryXML->addChild('Error');
			
			if ($logConnectionErrorsQuery->errorCode() != 0) {
				$logConnectionErrorsXML = $actionsXML->addChild('Action');
				$logConnectionErrorsXML->addAttribute('type', 'Log the collected Connection Errors in the database');
				$logConnectionErrorsXML->addChild('Status', "Failure");
				$errorDescription = "";
				$logConnectionErrorsXML->addChild('Details', $errorDescription);
				
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
		$errorLogDetailsXML->addChild('file_name', htmlspecialchars($fileName));
		$errorLogDetailsXML->addChild('file_directory', htmlspecialchars($fileDirectory));
		$errorLogDetailsXML->addChild('request_uri', htmlspecialchars($requestURI));
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