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
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		
		$connectResultXML->addChild('Status', 'Connected');
		$connectResultXML->addChild('Message', "Database Connection Successful");
		$connectResultXML->addChild('Host', $db_host);
		$connectResultXML->addChild('Database', $db_name);
		
		try {
			$queryXML = $xml->addChild('Query');
			
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$db->beginTransaction();
				
				$metaXML = $queryXML->addChild('Meta');
				$metaXML->addChild('Name', "Update Site List");
				$metaXML->addChild('Description', "This query checks to see if the list of Sites in the database is current with the BigFix Server.");
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
				
				$siteURL = "http://localhost/CASecure_MSP_ver_1.0/proxies/MasterSiteList.php?user=".$bigFixUser."&pass=".$bigFixPassword."&serv=".$bigFixServer;
		
				$siteXML = simplexml_load_file($siteURL);
				
				$rowCount = 0;
				$liveSites = array();
				
				foreach ($siteXML->Query->Result->Tuple as $site) {
					
					$siteName = "";
					$displayName = "";
					$type = "";
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
						$i++;
					}
					
					$liveSites[$rowCount] = 
						array(
							"site_name" => $siteName->__toString(), 
							"site_display_name" => $displayName->__toString(), 
							"site_type" => $type->__toString()
						);
					
					$rowCount++;
				}
				//print_r($liveSites);
				
				$liveSitesXML = $actionsXML->addChild('Action');
				$liveSitesXML->addAttribute('type', 'List of Live Sites');
				$liveSitesXML->addChild('Status', 'Success');
				$liveSitesXML->addChild('Details', 'Fetched List of all Sites currently Live on the BigFix API.');
				$liveSitesRowsXML = $liveSitesXML->addChild('Live_Sites');
				foreach ($liveSites as $liveSite) {
					$liveSitesRowXML = $liveSitesRowsXML->addChild('Row');
					$liveSitesRowXML->addChild('site_name', $liveSite['site_name']);
					$liveSitesRowXML->addChild('site_display_name', $liveSite['site_display_name']);
					$liveSitesRowXML->addChild('site_type', $liveSite['site_type']);
				}
				$liveSitesXML->addChild('Count', sizeOf($liveSites));
				
				$liveSiteNames = array();
				for($i = 0; $i< sizeOf($liveSites); $i++) {
					$liveSiteNames[$i] = $liveSites[$i]["site_name"];
				}
				//print_r($liveSiteNames);
				
				//echo "<br/><br/>";
				$databaseSites = array();
				
				$siteFetchSQL = 
					"SELECT * ".
					"FROM sites;";
				$siteFetchQuery = $db->query($siteFetchSQL);
				$databaseSites = $siteFetchQuery->fetchAll(PDO::FETCH_ASSOC);
				//print_r($databaseSites);
				
				$databaseSitesXML = $actionsXML->addChild('Action');
				$databaseSitesXML->addAttribute('type', 'List of Sites originally in the Database');
				$databaseSitesXML->addChild('Status', 'Success');
				$databaseSitesXML->addChild('Details', 'Fetched List of all Sites from the database.');
				$databaseSitesRowsXML = $databaseSitesXML->addChild('Database_Sites');
				foreach ($databaseSites as $databaseSite) {
					$databaseSiteRowXML = $databaseSitesRowsXML->addChild('Row');
					$databaseSiteRowXML->addChild('site_name', $databaseSite['site_name']);
					$databaseSiteRowXML->addChild('site_display_name', $databaseSite['site_display_name']);
					$databaseSiteRowXML->addChild('site_type', $databaseSite['site_type']);
				}
				$databaseSitesXML->addChild('Count', sizeOf($databaseSites));
				
				$databaseSiteNames = array();
				for($i = 0; $i< sizeOf($databaseSites); $i++) {
					$databaseSiteNames[$i] = $databaseSites[$i]["site_name"];
				}
				
				$missingSiteNames = array_diff($liveSiteNames, $databaseSiteNames);
				$outdatedSiteNames = array_diff($databaseSiteNames, $liveSiteNames);
				//print_r($missingSiteNames);
				
				$compareSitesXML = $actionsXML->addChild('Action');
				$compareSitesXML->addAttribute('type', 'Compare lists of Live and Database Sites');
				$compareSitesXML->addChild('Status', 'Success');
				$compareSitesXML->addChild('Details', 'Compared the lists of Live and Database Sites to see which ones need to be added to or removed from the database.');
				
				$missingSites = array();
				$outdatedSites = array();
				
				if ($missingSiteNames == array() && $outdatedSiteNames == array()) {
					$compareSitesXML->addChild('Missing_Database_Sites');
					$compareSitesXML->addChild('Missing_Count', 0);
					$compareSitesXML->addChild('Outdated_Database_Sites');
					$compareSitesXML->addChild('Outdated_Count', 0);
					
					//echo "Database Site list is currently up to date.<br/>";
					$updateDatabaseXML = $actionsXML->addChild('Action');
					$updateDatabaseXML->addAttribute('type', 'Rectify the Database');
					$updateDatabaseXML->addChild('Status', 'N/A');
					$updateDatabaseXML->addChild('Description', 'Database is already currently Up to Date.  No Action Required.');
				}
				else {
					//echo "Database Site List Updated.<br/>";
					if ($missingSiteNames != array()) {
						$j = 0;
						foreach($missingSiteNames as $liveIndex => $missingSiteName) {
							$missingSites[$j] = $liveSites[$liveIndex];
							$j++;
						}
						//print_r($missingSites);
						//echo "<br/>";
						
						$missingSitesXML = $compareSitesXML->addChild('Missing_Database_Sites');
						foreach ($missingSites as $missingSite) {
							$missingSiteRowXML = $missingSitesXML->addChild('Row');
							$missingSiteRowXML->addChild('site_name', $missingSite['site_name']);
							$missingSiteRowXML->addChild('site_display_name', $missingSite['site_display_name']);
							$missingSiteRowXML->addChild('site_type', $missingSite['site_type']);
						}
						$compareSitesXML->addChild('Missing_Count', sizeOf($missingSiteNames));
						
						$updateSitesQuery = pdoMultiInsert("sites", $missingSites, $db);
						$updateSitesQuery->execute();
						
						//foreach($missingSites as $missingSite) {
						//	echo ucfirst($missingSite["site_type"]).' Site "'.$missingSite["site_name"].'" added to the database.<br/>';
						//}
						
						$insertSitesXML = $actionsXML->addChild('Action');
						$insertSitesXML->addAttribute('type', 'Rectify the Database');
						$insertSitesXML->addChild('Status', 'Success');
						$insertSitesXML->addChild('Description', 'Inserted the missing Sites into the database.');
						
						$changeLogMessage = 'Added new Site(s) ';
						foreach($missingSites as $key => $missingSite) {
							if($key != 0) {
								$changeLogMessage .= ', ';
							}
							$changeLogMessage .= '"'.$missingSite["site_name"].'" ('.$missingSite["site_type"].')';
						}
						$changeLogMessage .= ' to the database. ';
						
						$changeLogSQL = 
							"INSERT INTO database_change_log ".
							"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
							"VALUES (:userID, :timestamp, 'BigFix Data Synchronization', :message, 'sites');";
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
						$changeDetailsXML->addChild('affected_tables', 'sites');
						
					}
					if ($outdatedSiteNames != array()) {
						$j = 0;
						foreach($outdatedSiteNames as $dbIndex => $outdatedSiteName) {
							$outdatedSites[$j] = $databaseSites[$dbIndex];
							$j++;
						}
						//print_r($outdatedSites);
						//echo "<br/>";
						
						$outdatedSitesXML = $compareSitesXML->addChild('Outdated_Database_Sites');
						foreach ($outdatedSites as $outdatedSite) {
							$outdatedSiteRowXML = $outdatedSitesXML->addChild('Row');
							$outdatedSiteRowXML->addChild('site_name', $outdatedSite['site_name']);
							$outdatedSiteRowXML->addChild('site_display_name', $outdatedSite['site_display_name']);
							$outdatedSiteRowXML->addChild('site_type', $outdatedSite['site_type']);
						}
						$compareSitesXML->addChild('Outdated_Count', sizeOf($outdatedSiteNames));
						
						foreach($outdatedSites as $key => $outdatedSite) {
							$deleteSQL = 
								"DELETE ".
								"FROM sites ".
								"WHERE site_name = :outdatedSiteName";
							$deleteSitesQuery = $db->prepare($deleteSQL);
							$deleteSitesQuery->bindParam(':outdatedSiteName', $outdatedSite["site_name"], PDO::PARAM_STR);
							$deleteSitesQuery->execute();
						}
						
						$RemoveOutdatedSitesXML = $actionsXML->addChild('Action');
						$RemoveOutdatedSitesXML->addAttribute('type', 'Rectify the Database');
						$RemoveOutdatedSitesXML->addChild('Status', 'Success');
						$RemoveOutdatedSitesXML->addChild('Description', 'Removed the outdated Sites from the database.');
						
						//foreach($outdatedSites as $outdatedSite) {
						//	echo ucfirst($outdatedSite["site_type"]).' Site "'.$outdatedSite["site_name"].'" removed from the database, as it is no longer supported by BigFix.<br/>';
						//}
						
						
						$changeLogMessage = 'Removed Outdated Site(s) ';
						foreach ($outdatedSites as $key => $outdatedSite) {
							if($key != 0) {
								$changeLogMessage .= ', ';
							}
							$changeLogMessage .= '"'.$outdatedSite["site_name"].'" ('.$outdatedSite["site_type"].')';
						}
						$changeLogMessage .= ' from the database. ';
						
						$changeLogSQL = 
							"INSERT INTO database_change_log ".
							"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
							"VALUES (:userID, :timestamp, 'BigFix Data Synchronization', :message, 'sites');";
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
						$changeDetailsXML->addChild('affected_tables', 'sites');
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
			else if ($siteFetchQuery->errorCode() != 0) {
				$databaseSitesXML = $actionsXML->addChild('Action');
				$databaseSitesXML->addAttribute('type', 'List of Sites originally in the Database');
				$databaseSitesXML->addChild('Status', 'Failure');
				$errorDescription = 'Unable to retrieve list of Sites from database.';
				$databaseSitesXML->addChild('Details', $errorDescription);
			}
			else if (isset($updateSitesQuery) && $updateSitesQuery->errorCode() != 0) {
				$insertSitesXML = $actionsXML->addChild('Action');
				$insertSitesXML->addAttribute('type', 'Rectify the Database');
				$insertSitesXML->addChild('Status', 'Failure');
				$errorDescription = 'Failed to Insert new Sites into database.';
				$insertSitesXML->addChild('Description', $errorDescription);
			}
			else if (isset($deleteSitesQuery) && $deleteSitesQuery->errorCode() != 0) {
				$RemoveOutdatedSitesXML = $actionsXML->addChild('Action');
				$RemoveOutdatedSitesXML->addAttribute('type', 'Rectify the Database');
				$RemoveOutdatedSitesXML->addChild('Status', 'Failure');
				$errorDescription = 'Failed to Remove Outdated Sites from database.';
				$RemoveOutdatedSitesXML->addChild('Description', $errorDescription);
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