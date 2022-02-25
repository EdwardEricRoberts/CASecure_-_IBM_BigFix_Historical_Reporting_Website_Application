<?php
	
	header('Content-type: application/xml');
	
	$timestamp = date("Y-m-d H:i:sO");
	//echo "\nTime = ".$timestamp."\n";//"<br />";
	
	//$userName = "eer";
	//$password = "AllieCat6";
	//$server = "bigfix.internal.cassevern.com";
	
	//$userName = implode(" ", array_slice($argv, 1, 1));
	//$password = implode(" ", array_slice($argv, 2, 1));
	//$server = implode(" ", array_slice($argv, 3, 1)); // Must be entered with periods "."s instead of "%2E"s
	
	//$currentUserId = $_GET['cid'];
	
	$currentBigFixUser = $_GET['user'];
	$currentBigFixPassword = $_GET['pass'];
	$currentBigFixServer = $_GET['serv'];
	
	//$db_host = implode(" ", array_slice($argv, 4, 1));
	//$db_name = implode(" ", array_slice($argv, 5, 1));
	//$db_username = implode(" ", array_slice($argv, 6, 1));
	//$db_password = implode(" ", array_slice($argv, 7, 1));
	
	$db_host = "localhost";
	$db_name = "CASecureTierpoint1";//"CASecure2";
	$db_username = "postgres";
	$db_password = "abc.123";
	
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
				$metaXML->addChild('Name', "Create User");
				$metaXML->addChild('Description', "This creates a new User Account.");
				$paramsXML = $metaXML->addChild('Parameters');
				$param1XML = $paramsXML->addChild('Parameter');
				$param1XML->addChild('Name', 'BigFix Console User Name');
				$param1XML->addChild('URL', 'user');
				$param1XML->addChild('Value', $currentBigFixUser);
				$param1XML->addChild('Description', 'Username for the BigFix Console (This is not the same username for the portal site)');
				$param2XML = $paramsXML->addChild('Parameter');
				$param2XML->addChild('Name', 'BigFix Console Login Password');
				$param2XML->addChild('URL', 'pass');
				$param2XML->addChild('Value', $currentBigFixPassword);
				$param2XML->addChild('Description', 'Login Password for the BigFix Console');
				$param3XML = $paramsXML->addChild('Parameter');
				$param3XML->addChild('Name', 'BigFix Console Server Name');
				$param3XML->addChild('URL', 'serv');
				$param3XML->addChild('Value', $currentBigFixServer);
				$param3XML->addChild('Description', 'Sever name for the BigFix Console sever being accessed');
				$resultXML = $queryXML->addChild('Result');
				
				$start = microtime(true);
				
				$actionsXML = $resultXML->addChild('Actions');
				
				$url = "http://localhost/CASecure_MSP_ver_1.0/proxies/BigFixUsersList.php?user=".$currentBigFixUser."&pass=".$currentBigFixPassword."&serv=".$currentBigFixServer;
	
				$bigFixUsersXML = simplexml_load_file($url);
				
				$bigFixUsersArray = array();
				$j = 0;
				foreach ($bigFixUsersXML->Query->Result->Tuple as $user) {
					$bigFixUserName = "";
					$serverName = "";
					$bigFixUserID = "";
					$i = 0;
					foreach ($user->Answer as $key => $value) {
						if ($i == 1) {
							$bigFixUserName = $value;	
						}
						else if ($i == 2) {
							$serverName = $value;
						}
						else if ($i == 0) {
							if ($value == "<none>") {
								$bigFixUserID = null;
							}
							else {
								$bigFixUserID = $value->__toString();
							}
						}
						$i++;
					}
					$bigFixUsersArray[$j]['bigfix_user_name'] = $bigFixUserName->__toString();
					$bigFixUsersArray[$j]['bigfix_server'] = $serverName->__toString();
					$bigFixUsersArray[$j]['bigfix_user_id'] = $bigFixUserID;
					$j++;
				}	
				//print_r($bigFixUsersArray);
				
				/*
				$logBigFixLoginsSQL =
					"INSERT INTO bigfix_logins ".
					"(bigfix_user_name, bigfix_server) ".
					"VALUES (:bigFixUserName, :serverName)";
				
				$logBigFixLoginsQuery = $db->prepare($logBigFixLoginsSQL);
				
				$logBigFixLoginsQuery->bindParam(':bigFixUserName', $bigFixUserName , PDO::PARAM_STR);
				$logBigFixLoginsQuery->bindParam(':serverName', $serverName, PDO::PARAM_STR);
				*/
				$logBigFixLoginsQuery = pdoMultiInsert('bigfix_logins', $bigFixUsersArray, $db);
				$logBigFixLoginsQuery->execute();
					
				$logBigFixLoginsXML = $actionsXML->addChild('Action');
				$logBigFixLoginsXML->addAttribute('type', 'Creation of BigFix Logins List');
				$logBigFixLoginsXML->addChild('Status', "Success");
				$logBigFixLoginsXML->addChild('Details', 'List of all BigFix Console Users has been logged in the database.');
				$bigFixLoginsInsertedRowsXML = $logBigFixLoginsXML->addChild('Inserted_Rows');
				$bigFixLoginsInsertedRowsXML->addAttribute('table', 'bigfix_logins');
				
				foreach($bigFixUsersArray as $bigFixUser) {
					$bigFixLoginRowXML = $bigFixLoginsInsertedRowsXML->addChild('Row');
					$bigFixLoginRowXML->addChild('bigfix_user_name', $bigFixUser['bigfix_user_name']);
					$bigFixLoginRowXML->addChild('bigfix_server', $bigFixUser['bigfix_server']);
					$bigFixLoginRowXML->addChild('bigfix_user_id', $bigFixUser['bigfix_user_id']);
				}
				
				$logBigFixLoginsXML->addChild('Count', sizeOf($bigFixUsersArray));
				//echo "<br/>Data for BigFix User '".$bigFixUserName."' was successfully logged in the Database.";//"'.<br />";
				
				$changeLogMessage = 'Populated the bigfix_logins table with data from the the BigFix Console.';
				
				$changeLogSQL = 
					"INSERT INTO database_change_log ".
					"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
					"VALUES (:updater, :timestamp, 'Initial BigFix Data Propigation', :message, 'bigfix_logins');";
				$changeLogQuery = $db->prepare($changeLogSQL);
				$changeLogQuery->bindParam(':updater', $updaterSQL, PDO::PARAM_STR);
				$changeLogQuery->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
				$changeLogQuery->bindParam(':message', $changeLogMessage, PDO::PARAM_STR);
				$changeLogQuery->execute();
				
				$end = microtime(true);
				
				$changeLogXML = $resultXML->addChild('Change_Log');
				$changeLogXML->addChild('Status', 'Transaction Logged');
				$changeDetailsXML = $changeLogXML->addChild('Details');
				$changeDetailsXML->addChild('timestamp', $timestamp);
				$changeDetailsXML->addChild('type_of_change', 'Initial BigFix Data Propigation');
				$changeDetailsXML->addChild('action_taken', $changeLogMessage);
				$changeDetailsXML->addChild('affected_tables', 'bigfix_logins');
				
				$changeLogXML->addChild('Count', 1);
				
				$evaluationXML = $queryXML->addChild('Evaluation');
				$evaluationXML->addChild('Time', round((($end - $start) * 1000), 3).'ms');
				$evaluationXML->addChild('Total_Number_of_Altered_Rows', sizeOf($bigFixUsersArray));
				
			$db->commit();
		}
		catch (\PDOException $e) {
			$errorXML = $queryXML->addChild('Error');
			if ($logBigFixLoginsQuery->errorCode() != 0) {
				$logBigFixLoginsXML = $actionsXML->addChild('Action');
				$logBigFixLoginsXML->addAttribute('type', 'Creation of BigFix Logins List');
				$logBigFixLoginsXML->addChild('Status', "Failure");
				if ($logBigFixLoginsQuery->errorCode() == 23505) {  // unique_violation
					$errorDescription = 'One or More of the BigFix Console User Names is already stored in the Database.';
				}
				else {
					$errorDescription = 'An Error occured when trying to import BigFix Console Data into Database';
				}
				$logBigFixLoginsXML->addChild('Details', $errorDescription);
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
			
			$db->rollBack();
			
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