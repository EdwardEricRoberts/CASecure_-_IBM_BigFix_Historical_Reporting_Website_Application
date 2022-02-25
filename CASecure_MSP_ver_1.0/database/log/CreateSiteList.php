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
				$metaXML->addChild('Name', "Create Site List");
				$metaXML->addChild('Description', "This generates the list of Sites from the BigFix console and stores them.  it is intended for one time use upon initial start up of of the application.");
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
			
				$siteURL = "http://localhost/CASecure_MSP_ver_1.0/proxies/MasterSiteList.php?user=".$currentBigFixUser."&pass=".$currentBigFixPassword."&serv=".$currentBigFixServer;
				
				$siteXML = simplexml_load_file($siteURL);
				
				$sitesArray = array();
				$j = 0;
				foreach ($siteXML->Query->Result->Tuple as $site) {
					
					$siteName = "";
					$displayName = "";
					$type = "";
					$bigfixSiteID = "";
					$i = 0;
					
					foreach ($site->Answer as $key => $value) {
						if ($i == 1) {
							$siteName = $value;
						}
						else if ($i == 2) {
							$displayName = $value;
						}
						else if ($i == 3) {
							$type = $value;
						}
						else if ($i == 0) {
							if ($value == "<none>") {
								$bigfixSiteID = null;
							}
							else {
								$bigfixSiteID = $value->__toString();
							}
						}
						$i++;
					}
					$sitesArray[$j]['site_name'] = $siteName->__toString();
					$sitesArray[$j]['site_display_name'] = $displayName->__toString();
					$sitesArray[$j]['site_type'] = $type->__toString();
					$sitesArray[$j]['bigfix_site_id'] = $bigfixSiteID;
					$j++;
				}
				//print_r($sitesArray);
				
				/*
					$sql =
						"INSERT INTO sites ".
						"(site_name, site_display_name, site_type) ".
						"VALUES (:siteName, :displayName, :type)";
					
					$query = $db->prepare($sql);
					
					$query->bindParam(':siteName', $siteName , PDO::PARAM_STR);
					$query->bindParam(':displayName', $displayName, PDO::PARAM_STR);
					$query->bindParam(':type', $type, PDO::PARAM_STR);
					*/
				$logSitesQuery = pdoMultiInsert('sites', $sitesArray, $db);
				$logSitesQuery->execute();
				
				$logSitesXML = $actionsXML->addChild('Action');
				$logSitesXML->addAttribute('type', 'Creation of Sites List');
				$logSitesXML->addChild('Status', "Success");
				$logSitesXML->addChild('Details', 'List of all BigFix Console Sites has been logged in the database.');
				$sitesInsertedRowsXML = $logSitesXML->addChild('Inserted_Rows');
				$sitesInsertedRowsXML->addAttribute('table', 'sites');
				
				foreach($sitesArray as $site) {
					$siteRowXML = $sitesInsertedRowsXML->addChild('Row');
					$siteRowXML->addChild('site_name', $site['site_name']);
					$siteRowXML->addChild('site_display_name', $site['site_display_name']);
					$siteRowXML->addChild('site_type', $site['site_type']);
					$siteRowXML->addChild('bigfix_site_id', $site['bigfix_site_id']);
				}
				
				$logSitesXML->addChild('Count', sizeOf($sitesArray));
				
				$changeLogMessage = 'Populated the sites table with data from the the BigFix Console.';
				
				$changeLogSQL = 
					"INSERT INTO database_change_log ".
					"(timestamp, type_of_change, action_taken, affected_tables ) ".
					"VALUES (:timestamp, 'Initial BigFix Data Propigation', :message, 'sites');";
				$changeLogQuery = $db->prepare($changeLogSQL);
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
				$changeDetailsXML->addChild('affected_tables', 'sites');
				
				$changeLogXML->addChild('Count', 1);
				
				$evaluationXML = $queryXML->addChild('Evaluation');
				$evaluationXML->addChild('Time', round((($end - $start) * 1000), 3).'ms');
				$evaluationXML->addChild('Total_Number_of_Altered_Rows', sizeOf($sitesArray));
					
			$db->commit();
		}
		catch (\PDOException $e) {
			$errorXML = $queryXML->addChild('Error');
			if ($logSitesQuery->errorCode() != 0) {
				$logSitesXML = $actionsXML->addChild('Action');
				$logSitesXML->addAttribute('type', 'Creation of Sites List');
				$logSitesXML->addChild('Status', "Failure");
				if ($logSitesQuery->errorCode() == 23505) {  // unique_violation
					$errorDescription = 'One or More of the Site Names is already stored in the Database.';
				}
				else {
					$errorDescription = 'An Error occured when trying to import BigFix Console Data into Database';
				}
				$logSitesXML->addChild('Details', $errorDescription);
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
					"(description, error_code, error_message, exception_type, timestamp) ".
					"VALUES (:description, :errorCode, :errorMessage, 'PDO Query', :timestamp);";
				$errorLogQuery = $db->prepare($errorLogSQL);
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