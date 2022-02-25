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
	$bigFixUserName = $_GET['user'];
	$bigFixPassword = $_GET['pass'];
	
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
				$param2XML = $paramsXML->addChild('Parameter');
				$param2XML->addChild('Name', 'BigFix Username');
				$param2XML->addChild('URL', 'user');
				$param2XML->addChild('Value', $bigFixUserName);
				$param2XML->addChild('Description', 'Console Username of the selected BigFix User');
				$param3XML = $paramsXML->addChild('Parameter');
				$param3XML->addChild('Name', 'BigFix Password');
				$param3XML->addChild('URL', 'pass');
				$param3XML->addChild('Value', $bigFixPassword);
				$param3XML->addChild('Description', 'Password of the selected BigFix User');
				$resultXML = $queryXML->addChild('Result');
				
				$start = microtime(true);
				
				$actionsXML = $resultXML->addChild('Actions');
				
				$bigFixLoginSQL = 
					"UPDATE bigfix_logins ".
					"SET bigfix_password = :bigFixPassword,  ".
						"confirmed = TRUE ".
					"WHERE bigfix_user_name = :bigFixUserName";
				$bigFixLoginQuery = $db->prepare($bigFixLoginSQL);
				$bigFixLoginQuery->bindParam(':bigFixPassword', $bigFixPassword, PDO::PARAM_STR);
				$bigFixLoginQuery->bindParam(':bigFixUserName', $bigFixUserName, PDO::PARAM_STR);
				$bigFixLoginQuery->execute();
				//$bigFixLogin = $bigFixLoginQuery->fetch(PDO::FETCH_ASSOC);
				
				
				$confirmBigFixLoginXML = $actionsXML->addChild('Action');
				$confirmBigFixLoginXML->addAttribute('type', 'Confirm Password for BigFix User');
				$confirmBigFixLoginXML->addChild('Status', "Success");
				$confirmBigFixLoginXML->addChild('Details', 'Logged BigFix Password for BigFix User "'.$bigFixUserName.'".');
				$confirmBigFixLoginRowsXML = $confirmBigFixLoginXML->addChild('Database_BigFix_Users');
				$confirmBigFixLoginRowsXML->addAttribute('table', 'bigfix_logins');
				//foreach ($databaseUserNames as $databaseUserName) {
					$confirmBigFixLoginRowsXML->addChild('bigfix_user_name', $bigFixUserName);
					$confirmBigFixLoginRowsXML->addChild('bigfix_password', $bigFixPassword);
					$confirmBigFixLoginRowsXML->addChild('confirmed', "TRUE");
				//}
				$confirmBigFixLoginXML->addChild('Count', 1);
				
		
				$message = 'Confirmed Password for BigFix Console User"'.$bigFixUserName.'". ';
				
				$changeLogSQL = 
					"INSERT INTO database_change_log ".
					"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
					"VALUES (:userID, :timestamp, 'BigFix User Confirmation', :message, 'bigfix_logins');";
				$changeLogQuery = $db->prepare($changeLogSQL);
				$changeLogQuery->bindParam(':userID', $userID, PDO::PARAM_STR);
				$changeLogQuery->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
				$changeLogQuery->bindParam(':message', $message, PDO::PARAM_STR);
				$changeLogQuery->execute();
				
				$changeLogXML = $resultXML->addChild('Change_Log');
				$changeLogXML->addChild('Status', 'Transaction Logged');
				$changeDetailsXML = $changeLogXML->addChild('Details');
				$changeDetailsXML->addChild('timestamp', $timestamp);
				$changeDetailsXML->addChild('type_of_change', 'BigFix User Confirmation');
				$changeDetailsXML->addChild('action_taken', $message);
				$changeDetailsXML->addChild('affected_tables', 'bigfix_logins');
			$db->commit();
		}
		catch (\PDOException $e) {
			$errorXML = $queryXML->addChild('Error');
			if ($bigFixLoginQuery->errorCode() != 0) {
				$fetchBigFixLoginXML = $actionsXML->addChild('Action');
				$fetchBigFixLoginXML->addAttribute('type', 'Confirm Password for BigFix User');
				$fetchBigFixLoginXML->addChild('Status', "Failure");
				$errorDescription = 'Failed to Log BigFix User Password.';
				$fetchBigFixLoginXML->addChild('Details', $errorDescription);
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