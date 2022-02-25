<?php
	
	header('Content-type: application/xml');
	
	$timestamp = date("Y-m-d H:i:sO");
	//echo "\nTime = ".$timestamp."\n";//"<br />";
	
	//$userName = "eer";
	//$password = "AllieCat5";
	//$server = "bigfix.internal.cassevern.com";
	
	$currentBigFixUser = $_GET['user'];
	$currentBigFixPassword = $_GET['pass'];
	$currentBigFixServer = $_GET['serv'];
	
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
				$metaXML->addChild('Name', "Create BigFix Site Access List");
				$metaXML->addChild('Description', "This generates the list of Accessabilities for BigFix Console Users to Sites collected from the BigFix console and stores them.  it is intended for one time use upon initial start up of of the application.");
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
				
				$fetchSitesSQL = 
					"SELECT site_name, site_type ".
					"FROM sites ";
				$fetchSitesQuery = $db->query($fetchSitesSQL);
				$sitesArray = $fetchSitesQuery->fetchAll(PDO::FETCH_ASSOC);
				//print_r($sitesArray);
				
			/*	
				$siteURL = "http://localhost/CASecure_MSP_ver_1.0/proxies/MasterSiteList.php?user=".$currentBigFixUser."&pass=".$currentBigFixPassword."&serv=".$currentBigFixServer;
				
				$siteXML = simplexml_load_file($siteURL);
				
				foreach ($siteXML->Query->Result->Tuple as $site) {
					
					$siteName = "";
					$type = "";
					$i = 0;
					
					foreach ($site->Answer as $key => $value) {
						if ($i == 1) {
							$siteName = $value;
						}
						else if ($i == 3) {
							$type = $value;
						}
						$i++;
					}
			*/		
					//echo "<br/>Site Name = '".$siteName."'.<br/>";
			//	
				$userSiteAccessesArray = array();
				foreach($sitesArray as $siteIndex => $site) {
					$siteName = $site['site_name'];
					$siteType = $site['site_type'];
					
					$siteAccessURL = "http://localhost/CASecure_MSP_ver_1.0/proxies/SiteAccessPermissionsList.php?user=".$currentBigFixUser."&pass=".$currentBigFixPassword."&serv=".$currentBigFixServer."&type=".$siteType."&site=".$siteName;
					
					$siteAccessXML = simplexml_load_file($siteAccessURL);
					
					foreach ($siteAccessXML->SitePermission as $operator) {
						
						if ($operator->Operator != null && $operator->Operator != "") {
							$bigFixUserName = $operator->Operator->__toString();;
							$userPrivilege = $operator->Permission->__toString();
							//echo $bigFixUserName.", ".$siteName.", ".$siteType."<br/>";
							$userSiteAccessesArray[] = array(
								"bigfix_user_name" => $bigFixUserName, 
								"site_name" => $siteName, 
								"user_privilege" => $userPrivilege 
							);
						}
					}	
					/*
					$sql =
						"INSERT INTO bigfix_site_access ".
						"(bigfix_user_name, site_name) ".
						"VALUES (:bigFixUserName, :siteName)";
				
					$query = $db->prepare($sql);
					
					$query->bindParam(':bigFixUserName', $bigFixUserName, PDO::PARAM_STR);
					$query->bindParam(':siteName', $siteName, PDO::PARAM_STR);
					$query->execute();
					//echo "<br/>Data was successfully sent to the Database, for BigFix User = '".$bigFixUserName."' and Site Name = '".$siteName."'.<br/>";//"'.<br />";
					*/
				}
				//print_r($userSiteAccessesArray);
			//	
				$logBigFixSiteAccessesQuery = pdoMultiInsert('bigfix_site_access', $userSiteAccessesArray, $db);
				$logBigFixSiteAccessesQuery->execute();
				
				$logBigFixSiteAccessesXML = $actionsXML->addChild('Action');
				$logBigFixSiteAccessesXML->addAttribute('type', 'Creation of BigFix Console User Site Access List');
				$logBigFixSiteAccessesXML->addChild('Status', "Success");
				$logBigFixSiteAccessesXML->addChild('Details', 'List of all connections between BigFix Console Users and Sites has been logged in the database.');
				$accessesInsertedRows = $logBigFixSiteAccessesXML->addChild('Inserted_Rows');
				$accessesInsertedRows->addAttribute('table', 'bigfix_site_access');
				
				foreach ($userSiteAccessesArray as $userSiteAccess) {
					$accessRowXML = $accessesInsertedRows->addChild('Row');
					$accessRowXML->addChild('bigfix_user_name', $userSiteAccess['bigfix_user_name']);
					$accessRowXML->addChild('site_name', $userSiteAccess['site_name']);
					$accessRowXML->addChild('user_privilege', $userSiteAccess['user_privilege']);
				}
				
				$logBigFixSiteAccessesXML->addChild('Count', sizeOf($userSiteAccessesArray));
				
				$changeLogMessage = 'Populated the bigfix_site_access table with data from the the BigFix Console.';
				
				$changeLogSQL = 
					"INSERT INTO database_change_log ".
					"(timestamp, type_of_change, action_taken, affected_tables ) ".
					"VALUES (:timestamp, 'Initial BigFix Data Propigation', :message, 'bigfix_site_access');";
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
				$changeDetailsXML->addChild('affected_tables', 'bigfix_site_access');
				
				$changeLogXML->addChild('Count', 1);
				
				$evaluationXML = $queryXML->addChild('Evaluation');
				$evaluationXML->addChild('Time', round((($end - $start) * 1000), 3).'ms');
				$evaluationXML->addChild('Total_Number_of_Altered_Rows', sizeOf($userSiteAccessesArray));
			//
			$db->commit();
		}
		catch (\PDOException $e) {
			$errorXML = $queryXML->addChild('Error');
			
			if ($logBigFixSiteAccessesQuery->errorCode() != 0) {
				$logBigFixSiteAccessesXML = $actionsXML->addChild('Action');
				$logBigFixSiteAccessesXML->addAttribute('type', 'Creation of BigFix Console User Site Access List');
				$logBigFixSiteAccessesXML->addChild('Status', "Failure");
				if ($logBigFixSiteAccessesQuery->errorCode() == 23505) {  // unique_violation
					$errorDescription = 'One or More of the Accesses is already stored in the Database.';
				}
				else {
					$errorDescription = 'An Error occured when trying to import BigFix Console Data into Database';
				}
				$logBigFixSiteAccessesXML->addChild('Details', $errorDescription);
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
		catch (Exception $e) {
			$errorXML = $queryXML->addChild('Error');
			
			$logBigFixSiteAccessesXML = $actionsXML->addChild('Action');
			$logBigFixSiteAccessesXML->addAttribute('type', 'Creation of BigFix Console User Site Access List');
			$logBigFixSiteAccessesXML->addChild('Status', "Failure");
			$errorDescription = "A PHP error occured";
			$logBigFixSiteAccessesXML->addChild('Details', $errorDescription);
			
			$errorCode = $e->getCode();
			$errorMessage = $e->getMessage();
			
			$errorXML->addChild('Error_Code', $errorCode);
			$errorXML->addChild('Details', $errorMessage);
			
			$db->rollback();
			
			try {
				$errorLogSQL = 
					"INSERT INTO error_log ".
					"(description, error_code, error_message, exception_type, timestamp) ".
					"VALUES (:description, :errorCode, :errorMessage, 'PHP Code', :timestamp);";
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
				$errorLogDetailsXML->addChild('exception_type', 'PHP Code');
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