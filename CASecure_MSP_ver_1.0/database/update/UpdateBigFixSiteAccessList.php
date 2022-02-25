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
				$metaXML->addChild('Name', "Update BigFix Site Access List");
				$metaXML->addChild('Description', "This query checks to see if the list of BigFix User Site Accesses in the database is current with the BigFix Server.");
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
				
				$sitesSQL = 
					"SELECT site_name, site_type ".
					"FROM sites ";
				$sitesQuery = $db->query($sitesSQL);
				$sitesArray = $sitesQuery->fetchAll(PDO::FETCH_ASSOC);
				//print_r($sitesArray);
				
				$bigfixLoginsSQL = 
					"SELECT bigfix_user_name ".
					"FROM bigfix_logins ;";
				$bigfixLoginsQuery = $db->query($bigfixLoginsSQL);
				$bigfixLoginsArray = $bigfixLoginsQuery->fetchAll(PDO::FETCH_COLUMN);
				//print_r($bigfixLoginsArray);
				//echo "<br/>";
				
				$liveUserSiteAccesses = array();
				
				foreach ($sitesArray as $num => $site) {
					$siteName = $site['site_name'];
					$siteType = $site['site_type'];
		
					$liveBigFixUserSiteAccessesURL = "http://localhost/CASecure_MSP_ver_1.0/proxies/SiteAccessPermissionsList.php?user=".$bigFixUser."&pass=".$bigFixPassword."&serv=".$bigFixServer."&type=".$siteType."&site=".$siteName;
					//echo $url."<br/>";
					$liveBigFixUserSiteAccessesXML = simplexml_load_file($liveBigFixUserSiteAccessesURL);
					
					foreach ($liveBigFixUserSiteAccessesXML->SitePermission as $operator) {
						if ($operator->Operator != null && $operator->Operator != "") {
							$bigFixUserName = $operator->Operator->__toString();
							$userPrivilege = $operator->Permission->__toString();
							//echo $bigFixUserName.", ".$siteName.", ".$siteType."<br/>";
							$liveUserSiteAccesses[] = array(
								"bigfix_user_name" => $bigFixUserName, 
								"site_name" => $siteName, 
								"user_privilege" => $userPrivilege 
							);
						}
					}
					//print_r($liveUserSiteAccesses[$num]["bigfix_user_name"]);
					//echo "<br/>";
					//$bigfixLoginsForSite = array();
					//foreach($liveUserSiteAccesses as $liveBigFix) {
					//	$bigfixLoginsForSite[] = $liveBigFix["bigfix_user_name"];
					//}
					//print_r($bigfixLoginsForSite);
					//echo "<br/>";
					//foreach($bigfixLoginsArray as $bigfixLogins) {
					//	if(in_array($bigfixLogins, $bigfixLoginsForSite) == 0) {
					//		echo $siteName.", ".$bigfixLogins."<br/>";
					//	}
					//}
				}
				//print_r($liveUserSiteAccesses);
				//echo "<br/>";
				
				$liveBigFixUserSiteAccessesXML = $actionsXML->addChild('Action');
				$liveBigFixUserSiteAccessesXML->addAttribute('type', 'List of Live Site Privilages');
				$liveBigFixUserSiteAccessesXML->addChild('Status', 'Success');
				$liveBigFixUserSiteAccessesXML->addChild('Details', 'Fetched List of all BigFix Console User Site Pivilages currently Live on the BigFix API.');
				$liveBigFixUserSiteAccessesRowsXML = $liveBigFixUserSiteAccessesXML->addChild('Live_BigFix_User_Site_Accesses');
				foreach ($liveUserSiteAccesses as $liveUserSiteAccess) {
					$liveBigFixUserSiteAccessesRowXML = $liveBigFixUserSiteAccessesRowsXML->addChild('Row');
					$liveBigFixUserSiteAccessesRowXML->addChild('bigfix_user_name', $liveUserSiteAccess['bigfix_user_name']);
					$liveBigFixUserSiteAccessesRowXML->addChild('site_name', $liveUserSiteAccess['site_name']);
					$liveBigFixUserSiteAccessesRowXML->addChild('user_privilege', $liveUserSiteAccess['user_privilege']);
				}
				$liveBigFixUserSiteAccessesXML->addChild('Count', sizeOf($liveUserSiteAccesses));
				
				$liveUserSiteAccessImplode = array();
				$liveUserSiteImplode = array();
				
				for($i = 0; $i < sizeOf($liveUserSiteAccesses); $i++) {
					$liveUserSiteAccessImplode[$i] = implode(", ", $liveUserSiteAccesses[$i]);
					$liveUserSiteImplode[$i] = $liveUserSiteAccesses[$i]['bigfix_user_name'].", ".$liveUserSiteAccesses[$i]['site_name'];
				}
				//print_r($liveUserSiteAccessImplode);
				//print_r($liveUserSiteImplode);
				
				$datbaseUserSiteAccesses = array();
				
				$userSiteAccessFetchSQL = 
					"SELECT * ".
					"FROM bigfix_site_access ".
					"WHERE user_privilege <> 'None';";
				$userSiteAccessFetchQuery = $db->query($userSiteAccessFetchSQL);
				$datbaseUserSiteAccesses = $userSiteAccessFetchQuery->fetchAll(PDO::FETCH_ASSOC);
				//print_r($datbaseUserSiteAccesses);
				
				$databaseBigFixUserSiteAccessesXML = $actionsXML->addChild('Action');
				$databaseBigFixUserSiteAccessesXML->addAttribute('type', 'List of Site Privilages originally in the Database');
				$databaseBigFixUserSiteAccessesXML->addChild('Status', 'Success');
				$databaseBigFixUserSiteAccessesXML->addChild('Details', 'Fetched List of all BigFix Console User Site Pivilages from the database.');
				$databaseBigFixUserSiteAccessesRowsXML = $databaseBigFixUserSiteAccessesXML->addChild('Database_BigFix_User_Site_Accesses');
				foreach ($datbaseUserSiteAccesses as $datbaseUserSiteAccess) {
					$databaseBigFixUserSiteAccessesRowXML = $databaseBigFixUserSiteAccessesRowsXML->addChild('Row');
					$databaseBigFixUserSiteAccessesRowXML->addChild('bigfix_user_name', $datbaseUserSiteAccess['bigfix_user_name']);
					$databaseBigFixUserSiteAccessesRowXML->addChild('site_name', $datbaseUserSiteAccess['site_name']);
					$databaseBigFixUserSiteAccessesRowXML->addChild('user_privilege', $datbaseUserSiteAccess['user_privilege']);
				}
				$databaseBigFixUserSiteAccessesXML->addChild('Count', sizeOf($datbaseUserSiteAccesses));
				
				$databaseUserSiteAccessImplode = array();
				$databaseUserSiteImplode = array();
				
				for($i = 0; $i < sizeOf($datbaseUserSiteAccesses); $i++) {
					$databaseUserSiteAccessImplode[$i] = implode(", ", $datbaseUserSiteAccesses[$i]);
					$databaseUserSiteImplode[$i] = $datbaseUserSiteAccesses[$i]['bigfix_user_name'].", ".$datbaseUserSiteAccesses[$i]['site_name'];
				}
				//print_r($databaseUserSiteAccessImplode);
				//print_r($databaseUserSiteImplode);
				
				$missingUserSiteAccessImplode = array_diff($liveUserSiteAccessImplode, $databaseUserSiteAccessImplode);
				$outdatedUserSiteAccessImplode = array_diff($databaseUserSiteImplode, $liveUserSiteImplode);
				//print_r($missingUserSiteAccessImplode);
				//print_r($outdatedUserSiteAccessImplode);
				//print_r(array_diff_assoc($liveUserSiteAccessImplode, $databaseUserSiteAccessImplode));
				
				$compareBigFixUserSiteAccessXML = $actionsXML->addChild('Action');
				$compareBigFixUserSiteAccessXML->addAttribute('type', 'Compare lists of Live and Database BigFix User Site Accesses');
				$compareBigFixUserSiteAccessXML->addChild('Status', 'Success');
				$compareBigFixUserSiteAccessXML->addChild('Details', 'Compared the lists of Live and Database BigFix User Site Privilages to see which ones need to be added to or removed from the database.');
				
				$missingUserSiteAccesses = array();
				$outdatedUserSiteAccesses = array();
				
				if ($missingUserSiteAccessImplode == array() && $outdatedUserSiteAccessImplode == array()) {
					$compareBigFixUserSiteAccessXML->addChild('Missing_Database_User_Site_Accesses');
					$compareBigFixUserSiteAccessXML->addChild('Missing_Count', 0);
					$compareBigFixUserSiteAccessXML->addChild('Outdated_Database_User_Site_Accesses');
					$compareBigFixUserSiteAccessXML->addChild('Outdated_Count', 0);
					
					//echo "Database BigFix Site Access list is currently up to date.<br/>";
					$updateDatabaseXML = $actionsXML->addChild('Action');
					$updateDatabaseXML->addAttribute('type', 'Rectify the Database');
					$updateDatabaseXML->addChild('Status', 'N/A');
					$updateDatabaseXML->addChild('Description', 'Database is already currently Up to Date.  No Action Required.');
				}
				else {
					//echo "Database BigFix Site Access list Updated.<br/>";
					if ($missingUserSiteAccessImplode != array()) {
						foreach($missingUserSiteAccessImplode as $liveIndex => $missingImploded) {
							$missingUserSiteAccesses[] = $liveUserSiteAccesses[$liveIndex];
						}
						//print_r($missingUserSiteAccesses);
						$missingBigFixUserSiteAccessXML = $compareBigFixUserSiteAccessXML->addChild('Missing_Database_User_Site_Accesses');
						foreach ($missingUserSiteAccesses as $missingUserSiteAccess) {
							$missingBigFixUserSiteAccessRowXML = $missingBigFixUserSiteAccessXML->addChild('Row');
							$missingBigFixUserSiteAccessRowXML->addChild('bigfix_user_name', $missingUserSiteAccess['bigfix_user_name']);
							$missingBigFixUserSiteAccessRowXML->addChild('site_name', $missingUserSiteAccess['site_name']);
							$missingBigFixUserSiteAccessRowXML->addChild('user_privilege', $missingUserSiteAccess['user_privilege']);
						}
						$compareBigFixUserSiteAccessXML->addChild('Missing_Count', sizeOf($missingUserSiteAccessImplode));
						
						$insertArray = array();
						$updateArray = array();
						
						foreach($missingUserSiteAccesses as $key => $missingUserSiteAccess) { 
							$missingUser = $missingUserSiteAccess['bigfix_user_name'];
							$missingSite = $missingUserSiteAccess['site_name'];
							$missingPrivilage = $missingUserSiteAccess['user_privilege'];
							//echo $missingUser.", ".$missingSite.", ".$missingPrivilage."<br/>";
							
							$userSiteConfirmSQL = 
								"SELECT 1 ".
								"FROM bigfix_site_access ".
								"WHERE bigfix_user_name = :missingUser AND site_name = :missingSite; ";
							$userSiteConfirmQuery = $db->prepare($userSiteConfirmSQL);
							$userSiteConfirmQuery->bindParam(":missingUser", $missingUser, PDO::PARAM_STR);
							$userSiteConfirmQuery->bindParam(":missingSite", $missingSite, PDO::PARAM_STR);
							$userSiteConfirmQuery->execute();
							$userSiteConfirmArray = $userSiteConfirmQuery->fetchALL(PDO::FETCH_ASSOC);
							//print_r($userSiteConfirmArray);
							//echo "<br/>";
							
							if ($userSiteConfirmArray == array()) { 
								$insertMissingAccessSQL = 
									"INSERT INTO bigfix_site_access ".
									"(bigfix_user_name, site_name, user_privilege) ".
									"VALUES (:missingUser, :missingSite, :missingPrivilage);";
								$insertMissingAccessQuery = $db->prepare($insertMissingAccessSQL);
								$insertMissingAccessQuery->bindParam(":missingUser", $missingUser, PDO::PARAM_STR);
								$insertMissingAccessQuery->bindParam(":missingSite", $missingSite, PDO::PARAM_STR);
								$insertMissingAccessQuery->bindParam(":missingPrivilage", $missingPrivilage, PDO::PARAM_STR);
								$insertMissingAccessQuery->execute();
								
								$insertArray[] = array(
									"bigfix_user_name" => $missingUser, 
									"site_name" => $missingSite, 
									"user_privilege" => $missingPrivilage
								);
							}
							else { 
								$getOldAccessSQL = 
									"SELECT user_privilege ".
									"FROM bigfix_site_access ".
									"WHERE bigfix_user_name = :missingUser AND site_name = :missingSite; ";
								$getOldAccessQuery = $db->prepare($getOldAccessSQL);
								$getOldAccessQuery->bindParam(":missingUser", $missingUser, PDO::PARAM_STR);
								$getOldAccessQuery->bindParam(":missingSite", $missingSite, PDO::PARAM_STR);
								$getOldAccessQuery->execute();
								$getOldAccessFetch = ($getOldAccessQuery->fetch(PDO::FETCH_ASSOC));
								$oldPriviledge = $getOldAccessFetch['user_privilege'];
								//print_r($oldPriviledge);
								//echo "<br/>";
								
								$updateMissingAccessSQL = 
									"UPDATE bigfix_site_access ".
									"SET user_privilege = :missingPrivilage ".
									"WHERE bigfix_user_name = :missingUser AND site_name = :missingSite; ";
								$updateMissingAccessQuery = $db->prepare($updateMissingAccessSQL);
								$updateMissingAccessQuery->bindParam(":missingPrivilage", $missingPrivilage, PDO::PARAM_STR);
								$updateMissingAccessQuery->bindParam(":missingUser", $missingUser, PDO::PARAM_STR);
								$updateMissingAccessQuery->bindParam(":missingSite", $missingSite, PDO::PARAM_STR);
								$updateMissingAccessQuery->execute();
								
								$updateArray[] = array(
									"bigfix_user_name" => $missingUser, 
									"site_name" => $missingSite, 
									"user_privilege" => $missingPrivilage, 
									"old_privilege" => $oldPriviledge 
								);
							}
						}
						
						//print_r($insertArray);
						//echo "<br/>";
						//print_r($updateArray);
						//echo "<br/>";
						
						//foreach($insertArray as $missingUserSiteAccess) {
						//	echo 'BigFix User "'.$missingUserSiteAccess['bigfix_user_name'].'" assigned access to Site "'.$missingUserSiteAccess['site_name'].'" with priviledge "'.$missingUserSiteAccess['user_privilege'].'".<br/>';
						//}
						//foreach($updateArray as $missingUserSiteAccess) {
						//	echo 'Priviledges for BigFix User "'.$missingUserSiteAccess['bigfix_user_name'].'" access to Site "'.$missingUserSiteAccess['site_name'].'" changed from "'.$missingUserSiteAccess['old_privilege'].'" to "'.$missingUserSiteAccess['user_privilege'].'".<br/>';
						//}
						
						$insertUpdateBigFixSiteAccessesXML = $actionsXML->addChild('Action');
						$insertUpdateBigFixSiteAccessesXML->addAttribute('type', 'Rectify the Database');
						$insertUpdateBigFixSiteAccessesXML->addChild('Status', 'Success');
						$insertUpdateBigFixSiteAccessesXML->addChild('Description', 'Inserted and/or Updated the missing BigFix Site Accesses into the database.');
						
						if($insertArray != array()) {
							$insertUpdateBigFixSiteAccessesXML->addChild('Inserted_User_Accesses');
							
							$changeLogMessage = 'Added new BigFix User Site Privilage(s) ';
							foreach($insertArray as $key => $insertUser) {
								if($key != 0) {
									$changeLogMessage .= ', ';
								}
								$changeLogMessage .= '("'.$insertUser["bigfix_user_name"].'", "'.$insertUser["site_name"].'", "'.$insertUser["user_privilege"].'")';
							}
							$changeLogMessage .= ' to the database.';
							
							$changeLogSQL = 
							"INSERT INTO database_change_log ".
							"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
							"VALUES (:userID, :timestamp, 'BigFix Data Synchronization', :message, 'bigfix_site_access')";
							$changeLogQuery = $db->prepare($changeLogSQL);
							$changeLogQuery->bindParam(':userID', $userID, PDO::PARAM_STR);
							$changeLogQuery->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
							$changeLogQuery->bindParam(':message', $changeLogMessage, PDO::PARAM_STR);
							$changeLogQuery->execute();
							//echo "Transaction Logged.<br/>";
							
							$changeLogXML = $resultXML->addChild('Change_Log');
							$changeLogXML->addChild('Status', 'Transaction Logged');
							$changeDetailsXML = $changeLogXML->addChild('Details');
							$changeDetailsXML->addChild('timestamp', $timestamp);
							$changeDetailsXML->addChild('type_of_change', 'BigFix Data Synchronization');
							$changeDetailsXML->addChild('action_taken', $changeLogMessage);
							$changeDetailsXML->addChild('affected_tables', 'bigfix_site_access');
						}
						if($updateArray != array()) {
							$insertUpdateBigFixSiteAccessesXML->addChild('Updated_User_Accesses');
							
							$changeLogMessage = 'Changed BigFix User Site Privilage(s) for ';
							foreach($updateArray as $key => $updateUser) {
								if($key != 0) {
									$changeLogMessage .= ', ';
								}
								$changeLogMessage .= '("'.$updateUser["bigfix_user_name"].'", "'.$updateUser["site_name"].'", from "'.$updateUser["old_privilege"].'" to "'.$updateUser["user_privilege"].'")';
							}
							$changeLogMessage .= ' to the database.';
							
							$changeLogSQL = 
							"INSERT INTO database_change_log ".
							"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
							"VALUES (:userID, :timestamp, 'BigFix Data Synchronization', :message, 'bigfix_site_access')";
							$changeLogQuery = $db->prepare($changeLogSQL);
							$changeLogQuery->bindParam(':userID', $userID, PDO::PARAM_STR);
							$changeLogQuery->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
							$changeLogQuery->bindParam(':message', $changeLogMessage, PDO::PARAM_STR);
							$changeLogQuery->execute();
							//echo "Transaction Logged.<br/>";
							
							$changeLogXML = $resultXML->addChild('Change_Log');
							$changeLogXML->addChild('Status', 'Transaction Logged');
							$changeDetailsXML = $changeLogXML->addChild('Details');
							$changeDetailsXML->addChild('timestamp', $timestamp);
							$changeDetailsXML->addChild('type_of_change', 'BigFix Data Synchronization');
							$changeDetailsXML->addChild('action_taken', $changeLogMessage);
							$changeDetailsXML->addChild('affected_tables', 'bigfix_site_access');
						}
					}
					if ($outdatedUserSiteAccessImplode != array()) {
						foreach($outdatedUserSiteAccessImplode as $dbIndex => $outdatedImploded) {
							$outdatedUserSiteAccesses[] = $datbaseUserSiteAccesses[$dbIndex];
						}
						//print_r($outdatedUserSiteAccesses);
						$outdatedBigFixUserSiteAccessXML = $compareBigFixUserSiteAccessXML->addChild('Outdated_Database_User_Site_Accesses');
						foreach ($outdatedUserSiteAccesses as $outdatedUserSiteAccess) {
							$outdatedBigFixUserSiteAccessRowXML = $outdatedBigFixUserSiteAccessXML->addChild('Row');
							$outdatedBigFixUserSiteAccessRowXML->addChild('bigfix_user_name', $outdatedUserSiteAccess['bigfix_user_name']);
							$outdatedBigFixUserSiteAccessRowXML->addChild('site_name', $outdatedUserSiteAccess['site_name']);
							$outdatedBigFixUserSiteAccessRowXML->addChild('user_privilege', $outdatedUserSiteAccess['user_privilege']);
						}
						$compareBigFixUserSiteAccessXML->addChild('Outdated_Count', sizeOf($outdatedUserSiteAccessImplode));
						
						foreach($outdatedUserSiteAccesses as $key => $outdatedUserSiteAccess) {
							$deleteSQL = 
								"DELETE ".
								"FROM bigfix_site_access ".
								"WHERE bigfix_user_name = :outdatedUser AND site_name = :outdatedSite";
							$deleteQuery = $db->prepare($deleteSQL);
							$deleteQuery->bindParam(':outdatedUser', $outdatedUserSiteAccess['bigfix_user_name'], PDO::PARAM_STR);
							$deleteQuery->bindParam(':outdatedSite', $outdatedUserSiteAccess['site_name'], PDO::PARAM_STR);
							$deleteQuery->execute();
						}
						
						$RemoveOutdatedBigFixUsersXML = $actionsXML->addChild('Action');
						$RemoveOutdatedBigFixUsersXML->addAttribute('type', 'Rectify the Database');
						$RemoveOutdatedBigFixUsersXML->addChild('Status', 'Success');
						$RemoveOutdatedBigFixUsersXML->addChild('Description', 'Deleted the outdated BigFix User Site Accesses from the database.');
						//foreach($outdatedUserSiteAccesses as $outdatedUserSiteAccess) {
						//	echo 'Access for BigFix User "'.$outdatedUserSiteAccess['bigfix_user_name'].'" to Site "'.$outdatedUserSiteAccess['site_name'].'" removed from the database, as it is no longer supported by BigFix.<br/>';
						//}
						
						$changeLogMessage = 'Removed Outdated BigFix User Site Privilage(s) ';
						foreach($outdatedUserSiteAccesses as $key => $outdatedUserSiteAccess) {
							if($key != 0) {
								$changeLogMessage .= ', ';
							}
							$changeLogMessage .= '("'.$outdatedUserSiteAccess['bigfix_user_name'].'", "'.$outdatedUserSiteAccess['site_name'].'", "'.$outdatedUserSiteAccess['user_privilege'].'")';
						}
						$changeLogMessage .= ' from the database.';
						
						$changeLogSQL = 
							"INSERT INTO database_change_log ".
							"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
							"VALUES (:userID, :timestamp, 'BigFix Data Synchronization', :message, 'bigfix_site_access');";
						$changeLogQuery = $db->prepare($changeLogSQL);
						$changeLogQuery->bindParam(':userID', $userID, PDO::PARAM_STR);
						$changeLogQuery->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
						$changeLogQuery->bindParam(':message', $changeLogMessage, PDO::PARAM_STR);
						$changeLogQuery->execute();
						//echo "Transaction Logged.<br/>";
						
						$changeLogXML = $resultXML->addChild('Change_Log');
						$changeLogXML->addChild('Status', 'Transaction Logged');
						$changeDetailsXML = $changeLogXML->addChild('Details');
						$changeDetailsXML->addChild('timestamp', $timestamp);
						$changeDetailsXML->addChild('type_of_change', 'BigFix Data Synchronization');
						$changeDetailsXML->addChild('action_taken', $changeLogMessage);
						$changeDetailsXML->addChild('affected_tables', 'bigfix_site_access');
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
			else if ($sitesQuery->errorCode() != 0) {
				$liveBigFixUserSiteAccessesXML = $actionsXML->addChild('Action');
				$liveBigFixUserSiteAccessesXML->addAttribute('type', 'List of Live Site Privilages');
				$liveBigFixUserSiteAccessesXML->addChild('Status', 'Failure');
				$errorDescription = 'Failed to retrieve list of Sites from database.';
				$liveBigFixUserSiteAccessesXML->addChild('Details', $errorDescription);
			}
			else if ($bigfixLoginsQuery->errorCode() != 0) {
				$liveBigFixUserSiteAccessesXML = $actionsXML->addChild('Action');
				$liveBigFixUserSiteAccessesXML->addAttribute('type', 'List of Live Site Privilages');
				$liveBigFixUserSiteAccessesXML->addChild('Status', 'Failure');
				$errorDescription = 'Failed to retrieve list of BigFix Logins from database.';
				$liveBigFixUserSiteAccessesXML->addChild('Details', $errorDescription);
			}
			else if ($userSiteAccessFetchQuery->errorCode() != 0) {
				$databaseBigFixUserSiteAccessesXML = $actionsXML->addChild('Action');
				$databaseBigFixUserSiteAccessesXML->addAttribute('type', 'List of Site Privilages originally in the Database');
				$databaseBigFixUserSiteAccessesXML->addChild('Status', 'Failure');
				$errorDescription = 'Failed to retrieve list of User Site Accesses from database.';
				$databaseBigFixUserSiteAccessesXML->addChild('Details', $errorDescription);	
			}
			else if (isset($userSiteConfirmQuery) && $userSiteConfirmQuery->errorCode() != 0) {
				$insertUpdateBigFixSiteAccessesXML = $actionsXML->addChild('Action');
				$insertUpdateBigFixSiteAccessesXML->addAttribute('type', 'Rectify the Database');
				$insertUpdateBigFixSiteAccessesXML->addChild('Status', 'Failure');
				$errorDescription = '';
				$insertUpdateBigFixSiteAccessesXML->addChild('Description', $errorDescription);
			}
			else if (isset($insertMissingAccessQuery) && $insertMissingAccessQuery->errorCode() != 0) {
				$insertUpdateBigFixSiteAccessesXML = $actionsXML->addChild('Action');
				$insertUpdateBigFixSiteAccessesXML->addAttribute('type', 'Rectify the Database');
				$insertUpdateBigFixSiteAccessesXML->addChild('Status', 'Failure');
				$errorDescription = '';
				$insertUpdateBigFixSiteAccessesXML->addChild('Description', $errorDescription);
			}
			else if (isset($getOldAccessQuery) && $getOldAccessQuery->errorCode() != 0) {
				$insertUpdateBigFixSiteAccessesXML = $actionsXML->addChild('Action');
				$insertUpdateBigFixSiteAccessesXML->addAttribute('type', 'Rectify the Database');
				$insertUpdateBigFixSiteAccessesXML->addChild('Status', 'Failure');
				$errorDescription = '';
				$insertUpdateBigFixSiteAccessesXML->addChild('Description', $errorDescription);
			}
			else if (isset($updateMissingAccessQuery) && $updateMissingAccessQuery->errorCode() != 0) {
				$insertUpdateBigFixSiteAccessesXML = $actionsXML->addChild('Action');
				$insertUpdateBigFixSiteAccessesXML->addAttribute('type', 'Rectify the Database');
				$insertUpdateBigFixSiteAccessesXML->addChild('Status', 'Failure');
				$errorDescription = 'Failed to ';
				$insertUpdateBigFixSiteAccessesXML->addChild('Description', $errorDescription);
			}
			else if (isset($deleteQuery) && $deleteQuery->errorCode() != 0) {
				$RemoveOutdatedBigFixUsersXML = $actionsXML->addChild('Action');
				$RemoveOutdatedBigFixUsersXML->addAttribute('type', 'Rectify the Database');
				$RemoveOutdatedBigFixUsersXML->addChild('Status', 'Failure');
				$errorDescription = '';
				$RemoveOutdatedBigFixUsersXML->addChild('Description', $errorDescription);
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