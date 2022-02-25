<?php
	$timestamp = date("Y-m-d H:i:sO");
	//echo "\nTime = ".$timestamp."\n";//"<br />";
	
	//$userName = implode(" ", array_slice($argv, 1, 1));
	//$password = implode(" ", array_slice($argv, 2, 1));
	//$server = implode(" ", array_slice($argv, 3, 1)); // Must be entered with periods "."s instead of "%2E"s
	
	$currentUserID = $_GET['uid'];
	
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
	try {
		$db = new PDO('pgsql:host='.$db_host.';dbname='.$db_name,$db_username,$db_password);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		
		echo "Database connection successful.<br/>";
	}
	catch (PDOException $e) {
		echo $e->getMessage();
		//echo errorHandle($e);
		$db->rollback();
	}
	
	
	try {
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		$db->beginTransaction();
			
			$bigFixLoginSQL = 
				"SELECT bigfix_user_name, bigfix_password, bigfix_server ".
				"FROM bigfix_logins ".
				"WHERE bigfix_user_name IN (".
					"SELECT bigfix_user_name ".
					"FROM console_to_portal ".
					"WHERE user_id = :currentUserID".
				");";
			
			$bigFixLoginQuery = $db->prepare($bigFixLoginSQL);
			$bigFixLoginQuery->bindParam(':currentUserID', $currentUserID, PDO::PARAM_STR);
			$bigFixLoginQuery->execute();
			$bigFixLogin = $bigFixLoginQuery->fetch(PDO::FETCH_ASSOC);
			
			$bigFixUser = $bigFixLogin['bigfix_user_name'];
			$bigFixPassword = $bigFixLogin['bigfix_password'];
			$bigFixServer = $bigFixLogin['bigfix_server'];
			
			$userSiteAccessSQL =
				"SELECT user_id, site_name, user_privilege ".
				"FROM user_site_access ";
				//"WHERE user_privilege <> 'None'";
			$userSiteAccessQuery = $db->query($userSiteAccessSQL);
			$userSiteAccessArray = $userSiteAccessQuery->fetchAll(PDO::FETCH_ASSOC);
			//print_r($userSiteAccessArray);
			
			$userSiteAccessWithBigFixAll = array();
			$userSiteAccessWithBigFix = array();
			
			foreach ($userSiteAccessArray as $userSiteAccessKey => $userSiteAccess) {
				$userBigFixInfoSQL = 
					"SELECT bigfix_user_name ".
					"FROM console_to_portal ".
					"WHERE user_id = :userID; ";
				$userBigFixInfoQuery = $db->prepare($userBigFixInfoSQL);
				$userBigFixInfoQuery->bindParam(':userID', $userSiteAccess["user_id"], PDO::PARAM_STR);
				$userBigFixInfoQuery->execute();
				$userBigFixInfoArray = $userBigFixInfoQuery->fetch(PDO::FETCH_ASSOC);
				$userBigFixUserName = $userBigFixInfoArray["bigfix_user_name"];
				
				$userSiteAccessWithBigFixAll[] = 
					array(
						"user_id" => $userSiteAccess["user_id"], 
						"bigfix_user_name" => $userBigFixUserName, 
						"site_name" => $userSiteAccess["site_name"]
						//"user_privilege" => $userSiteAccess["user_privilege"]
					);
				
				if($userSiteAccess["user_privilege"] != "None") {
					$userSiteAccessWithBigFix[] = 
						array(
							"user_id" => $userSiteAccess["user_id"], 
							"bigfix_user_name" => $userBigFixUserName, 
							"site_name" => $userSiteAccess["site_name"]
							//"user_privilege" => $userSiteAccess["user_privilege"]
						);
				}
				//echo $userBigFixUserName.", ".$userSiteAccess["user_id"].", ".$userSiteAccess["site_name"];
				//echo "<br/>";
			}
			//print_r($userSiteAccessWithBigFixAll);
			//print_r($userSiteAccessWithBigFix);
			
			$userSiteAccessWithBigFixAllImplode = array();
			foreach($userSiteAccessWithBigFixAll as $userSiteAccessAll) {
				$userSiteAccessWithBigFixAllImplode[] = implode(", ", $userSiteAccessAll);
			}
			//print_r($userSiteAccessWithBigFixAllImplode);
			
			$userSiteAccessWithBigFixImplode = array();
			foreach($userSiteAccessWithBigFix as $userSiteAccess) {
				$userSiteAccessWithBigFixImplode[] = implode(", ", $userSiteAccess);
			}
			//print_r($userSiteAccessWithBigFixImplode);
			
			$siteAccessWithBigFixAllImplode = array();
			foreach($userSiteAccessWithBigFixAllImplode as $userSiteAcessImplodeAll) {
				$siteAccessWithBigFixAllImplode[] = substr($userSiteAcessImplodeAll, strpos($userSiteAcessImplodeAll, ", ") + 2);
			}
			//print_r($siteAccessWithBigFixAllImplode);
			
			$siteAccessWithBigFixImplode = array();
			foreach($userSiteAccessWithBigFixImplode as $userSiteAcessImplode) {
				$siteAccessWithBigFixImplode[] = substr($userSiteAcessImplode, strpos($userSiteAcessImplode, ", ") + 2);
			}
			//print_r($siteAccessWithBigFixImplode);
			
			//print_r(array_unique($siteAccessWithBigFixAllImplode));
			//print_r(array_unique($siteAccessWithBigFixImplode));
			
			$bigfixUsers = array();
			foreach($userSiteAccessWithBigFixAll as $userSiteAccess) {
				$bigfixUsers[] = $userSiteAccess["bigfix_user_name"];
			}
			//print_r($bigfixUsers);
			
			$bigFixUsersUnique = array();
			foreach(array_unique($bigfixUsers) as $bigfix) {
				$bigFixUsersUnique[] = $bigfix;
			}
			//print_r($bigFixUsersUnique);
			
			$bigFixSiteAccessSQL = 
				"SELECT * ".
				"FROM bigfix_site_access ";
			$bigFixSiteAccessQuery = $db->query($bigFixSiteAccessSQL);
			$bigFixSiteAccessArray = $bigFixSiteAccessQuery->fetchAll(PDO::FETCH_ASSOC);
			//print_r($bigFixSiteAccessArray);
			
			$bigFixSiteAccessImplode = array();
			foreach($bigFixSiteAccessArray as $bigFixSiteAccess) {
				foreach($bigFixUsersUnique as $bigFix) {
					if($bigFixSiteAccess['bigfix_user_name'] === $bigFix) {
						$bigFixSiteAccessImplode[] = implode(", ", $bigFixSiteAccess);
					}
				}
			}
			//print_r($bigFixSiteAccessImplode);
			
			$missingSiteAccessWithBigFixImplode = array_diff($bigFixSiteAccessImplode, $siteAccessWithBigFixImplode);
			$outdatedSiteAccessWithBigFixImplode = array_diff($siteAccessWithBigFixImplode, $bigFixSiteAccessImplode);
			//print_r($missingSiteAccessWithBigFixImplode);
			//print_r($outdatedSiteAccessWithBigFixImplode);
			//echo "<br/>";
			//print_r($bigFixSiteAccessImplode);
			if ($missingSiteAccessWithBigFixImplode == array() && $outdatedSiteAccessWithBigFixImplode == array()) {
				echo "Database User Site Access List currently up to date.<br/>";
			}
			else {
				
				if($missingSiteAccessWithBigFixImplode != array()) {
					$missingBigFixUsers = array();
					$missingSites = array();
					foreach($missingSiteAccessWithBigFixImplode as $missingImplode) {
						$missingExplode = explode(", ", $missingImplode);
						$missingBigFixUsers[] = $missingExplode[0];
						$missingSites[] = $missingExplode[1];
					}
					//print_r(array_unique($missingBigFixUsers));
					//print_r($missingSites);
					$usersToUpdate = array();
					foreach(array_unique($missingBigFixUsers) as $missingBigFixUser) { 
						$userIDsSQL = 
							"SELECT user_id ".
							"FROM console_to_portal ".
							"WHERE bigfix_user_name = :bigFixUser";
						$userIDsQuery = $db->prepare($userIDsSQL);
						$userIDsQuery->bindParam(":bigFixUser", $missingBigFixUser, PDO::PARAM_STR);
						$userIDsQuery->execute();
						$userIDsArray = ($userIDsQuery->fetchALL(PDO::FETCH_COLUMN));
						//print_r($userIDsArray);
						//echo "<br/>";
						foreach($userIDsArray as $userID) {
							$usersToUpdate[] = $userID;
						}
					}
					print_r($usersToUpdate);
					
					$updatesArray = array();
					//
					foreach($usersToUpdate as $userID) { 
						foreach(array_unique($missingSites) as $missingSite) {
							
							$siteTypeSQL = 
								"SELECT site_type ".
								"FROM sites ".
								"WHERE site_name = :siteName; ";
							$siteTypeQuery = $db->prepare($siteTypeSQL);
							$siteTypeQuery->bindParam(":siteName", $missingSite, PDO::PARAM_STR);
							$siteTypeQuery->execute();
							$siteType = $siteTypeQuery->fetch(PDO::FETCH_ASSOC)["site_type"];
							//echo $siteType."<br/>";
							
							$searchSQL = 
								"SELECT bigfix_user_name ".
								"FROM console_to_portal ".
								"WHERE user_id = :userID; ";
							$searchQuery = $db->prepare($searchSQL);
							$searchQuery->bindParam(":userID", $userID, PDO::PARAM_STR);
							$searchQuery->execute();
							$search = $searchQuery->fetch(PDO::FETCH_ASSOC)["bigfix_user_name"];
							//echo $search."<br/>";
							
							$userSiteConfirmSQL = 
								"SELECT 1 ".
								"FROM user_site_access ".
								"WHERE user_id = :userID AND site_name = :missingSite; ";
							$userSiteConfirmQuery = $db->prepare($userSiteConfirmSQL);
							$userSiteConfirmQuery->bindParam(":userID", $userID, PDO::PARAM_STR);
							$userSiteConfirmQuery->bindParam(":missingSite", $missingSite, PDO::PARAM_STR);
							$userSiteConfirmQuery->execute();
							$userSiteConfirmArray = $userSiteConfirmQuery->fetchALL(PDO::FETCH_ASSOC);
							//print_r($userSiteConfirmArray);
							//echo "<br/>";
							
							$url = "http://localhost/CASecure_MSP_ver_1.0/proxies/UserSiteAccessPermissionCheck.php?user=".$bigFixUser."&pass=".$bigFixPassword."&serv=".$bigFixServer."&type=".$siteType."&site=".$missingSite."&search=".$search;
							//echo $url."<br/>";
							$xml = simplexml_load_file($url);
							$userPrivilege = $xml->SitePermission->Permission->__toString();
							//echo $userPrivilege."<br/>";
							//echo $userID.", ".$missingSite.", ".$userPrivilege."<br/>";
							
							if ($userSiteConfirmArray == array()) {		
								$insertMissingAccessSQL = 
									"INSERT INTO user_site_access ".
									"(user_id, site_name, user_privilege) ".
									"VALUES (:userID, :siteName, :userPrivilege)";
								$insertMissingAccessQuery = $db->prepare($insertMissingAccessSQL);
								$insertMissingAccessQuery->bindParam(":userID", $userID, PDO::PARAM_STR);
								$insertMissingAccessQuery->bindParam(":siteName", $missingSite, PDO::PARAM_STR);
								$insertMissingAccessQuery->bindParam(":userPrivilege", $userPrivilege, PDO::PARAM_STR);
								$insertMissingAccessQuery->execute();
								
							}
							else {
								$updateMissingAccessSQL = 
									"UPDATE user_site_access ".
									"set user_privilege = :userPrivilege ".
									"WHERE user_id = :userID AND site_name = :missingSite; ";
								$updateMissingAccessQuery = $db->prepare($updateMissingAccessSQL);
								$updateMissingAccessQuery->bindParam(":userPrivilege", $userPrivilege, PDO::PARAM_STR);
								$updateMissingAccessQuery->bindParam(":userID", $userID, PDO::PARAM_STR);
								$updateMissingAccessQuery->bindParam(":missingSite", $missingSite, PDO::PARAM_STR);
								$updateMissingAccessQuery->execute();
								
							}
							$updatesArray[] = array(
								"user_id" => $userID,
								"site_name" => $missingSite,
								"user_privilege" => $userPrivilege
							);
							echo 'Access for User "'.$userID.'" to Site "'.$missingSite.'" set to "'.$userPrivilege.'".<br/>';
						}
					}
					$message = 'Set User Site Access privledges ';
					foreach($updatesArray as $key => $update) {
						if($key != 0) {
							$message .= ", ";
						}
						$message .= "(".$update["user_id"].", ".$update["site_name"].", ".$update["user_privilege"].")";
					}
					//echo $message;
					$changeLogSQL = 
						"INSERT INTO database_change_log ".
						"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables) ".
						"VALUES (:currentUserID, :timestamp, 'BigFix Data Synchronization', :message, 'user_site_access'); ";
					$changeLogQuery = $db->prepare($changeLogSQL);
					$changeLogQuery->bindParam(":currentUserID", $currentUserID, PDO::PARAM_STR);
					$changeLogQuery->bindParam(":timestamp", $timestamp, PDO::PARAM_STR);
					$changeLogQuery->bindParam(":message", $message, PDO::PARAM_STR);
					$changeLogQuery->execute();
					echo "Transaction Logged. <br/>";
					//
				}
				
				if ($outdatedSiteAccessWithBigFixImplode != array()) {
					$outdatedBigFixUsers = array();
					$outdatedSites = array();
					foreach($outdatedSiteAccessWithBigFixImplode as $outdatedImplode) {
						$outdatedExplode = explode(", ", $outdatedImplode);
						$outdatedBigFixUsers[] = $outdatedExplode[0];
						$outdatedSites[] = $outdatedExplode[1];
					}
					//print_r(array_unique($outdatedBigFixUsers));
					//print_r($outdatedSites);
					$usersToUpdate = array();
					foreach(array_unique($outdatedBigFixUsers) as $outdatedBigFixUser) { 
						$userIDsSQL = 
							"SELECT user_id ".
							"FROM console_to_portal ".
							"WHERE bigfix_user_name = :bigFixUser";
						$userIDsQuery = $db->prepare($userIDsSQL);
						$userIDsQuery->bindParam(":bigFixUser", $outdatedBigFixUser, PDO::PARAM_STR);
						$userIDsQuery->execute();
						$userIDsArray = ($userIDsQuery->fetchALL(PDO::FETCH_COLUMN));
						//print_r($userIDsArray);
						//echo "<br/>";
						foreach($userIDsArray as $userID) {
							$usersToUpdate[] = $userID;
						}
					}
					//print_r($usersToUpdate);
					$updatesArray = array();
					foreach($usersToUpdate as $userID) { 
						foreach(array_unique($outdatedSites) as $outdatedSite) {
							$searchSQL = 
								"SELECT bigfix_user_name ".
								"FROM console_to_portal ".
								"WHERE user_id = :userID; ";
							$searchQuery = $db->prepare($searchSQL);
							$searchQuery->bindParam(":userID", $userID, PDO::PARAM_STR);
							$searchQuery->execute();
							$search = $searchQuery->fetch(PDO::FETCH_ASSOC)["bigfix_user_name"];
							//echo $userID.", ".$search.", ".$outdatedSite."<br/>";
							
							$updateSQL = 
								"UPDATE user_site_access ".
								"SET user_privilege = 'None' ".
								"WHERE user_id = :userID AND site_name = :outdatedSite;";
							$updateQuery = $db->prepare($updateSQL);
							$updateQuery->bindParam(":userID", $userID, PDO::PARAM_STR);
							$updateQuery->bindParam(":outdatedSite", $outdatedSite, PDO::PARAM_STR);
							$updateQuery->execute();
							$updatesArray[] = array(
								"user_id" => $userID,
								"site_name" => $outdatedSite
							);
							echo 'Access for User "'.$userID.'" to Site "'.$outdatedSite.'" set to "None".<br/>';
						}
					}
					//print_r($updatesArray);
					$message = 'Set User Site Access privledges to "None" for ';
					foreach($updatesArray as $key => $update) {
						if($key != 0) {
							$message .= ", ";
						}
						$message .= "(".$update["user_id"].", ".$update["site_name"].")";
					}
					//echo $message;
					$changeLogSQL = 
						"INSERT INTO database_change_log ".
						"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables) ".
						"VALUES (:currentUserID, :timestamp, 'BigFix Data Synchronization', :message, 'user_site_access'); ";
					$changeLogQuery = $db->prepare($changeLogSQL);
					$changeLogQuery->bindParam(":currentUserID", $currentUserID, PDO::PARAM_STR);
					$changeLogQuery->bindParam(":timestamp", $timestamp, PDO::PARAM_STR);
					$changeLogQuery->bindParam(":message", $message, PDO::PARAM_STR);
					$changeLogQuery->execute();
					echo "Transaction Logged. <br/>";
				}
			}
		/*	
			$outdatedBigFixSiteAccesses = array_diff($siteAccessWithBigFixImplode, $bigFixSiteAccessImplode);
			//print_r($outdatedBigFixSiteAccesses);
			
			//echo "<br/><br/>";
			$usersForUpdates = array();
			foreach($outdatedBigFixSiteAccesses as $outdatedBigFixSiteAccessKey => $outdatedBigFixSiteAccess) {
				$usersForUpdates[] = $userSiteAccessWithBigFix[$outdatedBigFixSiteAccessKey];
			}
			//print_r($usersForUpdates);
			//echo "<br/>";
			
			//$bigFixSiteAccessImplode;
			print_r(array_unique($siteAccessWithBigFixImplode));
	*/		
			//$bigFixSiteAccessRelevant = array()
			//foreach($bigFixSiteAccessArray as $bigFixSiteAccessKey => $bigFixSiteAccess) {
			//	$bigFixSiteAccessRelevant[] = 
			//}
			//print_r(array_diff($bigFixSiteAccessImplode, $siteAccessWithBigFixImplode));
		/*	
			$sitesSQL = 
				"SELECT site_name, site_type ".
				"FROM sites ";
			
			$sitesQuery = $db->query($sitesSQL);
			$sitesArray = $sitesQuery->fetchAll(PDO::FETCH_ASSOC);
			//print_r($sitesArray);
			
			$liveUserSiteAccesses = array();
			$i = 0;
			
			foreach ($sitesArray as $num => $site) {
				$siteName = $site['site_name'];
				$siteType = $site['site_type'];
	
				$url = "http://localhost/CASecure_MSP_ver_1.0/proxies/SiteAccessPermissionsList.php?user=".$bigFixUser."&pass=".$bigFixPassword."&serv=".$bigFixServer."&type=".$siteType."&site=".$siteName;
				//echo $url."<br/>";
				$xml = simplexml_load_file($url);
				
				foreach ($xml->SitePermission as $operator) {
					if ($operator->Operator != null && $operator->Operator != "") {
						$bigFixUserName = $operator->Operator;
						//echo $bigFixUserName.", ".$siteName.", ".$siteType."<br/>";
						$liveUserSiteAccesses[$i] = 
							array(
								"bigfix_user_name" => $bigFixUserName->__toString(),
								"site_name" => $siteName
							);
						$i++;
					}
				}
			}
			//print_r($liveUserSiteAccesses);
			
			$liveUserSiteAccessImplode = array();
			
			for($i = 0; $i < sizeOf($liveUserSiteAccesses); $i++) {
				$liveUserSiteAccessImplode[$i] = implode(", ", $liveUserSiteAccesses[$i]);
			}
			//print_r($liveUserSiteAccessImplode);
			
			$datbaseUserSiteAccesses = array();
			
			$userSiteAccessFetchSQL = 
				"SELECT * ".
				"FROM bigfix_site_access;";
			
			$userSiteAccessFetchQuery = $db->query($userSiteAccessFetchSQL);
			
			$datbaseUserSiteAccesses = $userSiteAccessFetchQuery->fetchAll(PDO::FETCH_ASSOC);
			//print_r($datbaseUserSiteAccesses);
			
			$datbaseUserSiteAccessImplode = array();
			
			for($i = 0; $i < sizeOf($datbaseUserSiteAccesses); $i++) {
				$datbaseUserSiteAccessImplode[$i] = implode(", ", $datbaseUserSiteAccesses[$i]);
			}
			//print_r($datbaseUserSiteAccessImplode);
			
			$missingUserSiteAccessImplode = array_diff($liveUserSiteAccessImplode, $datbaseUserSiteAccessImplode);
			$outdatedUserSiteAccessImplode = array_diff($datbaseUserSiteAccessImplode, $liveUserSiteAccessImplode);
			//print_r($missingUserSiteAccessImplode);
			//print_r($outdatedUserSiteAccessImplode);
			
			$missingUserSiteAccesses = array();
			$outdatedUserSiteAccesses = array();
			
			if ($missingUserSiteAccessImplode == array() && $outdatedUserSiteAccessImplode == array()) {
				echo "Database BigFix Site Access list is currently up to date.<br/>";
			}
			else {
				echo "Database BigFix Site Access list Updated.<br/>";
				if ($missingUserSiteAccessImplode != array()) {
					foreach($missingUserSiteAccessImplode as $liveIndex => $missingImploded) {
						$missingUserSiteAccesses[] = $liveUserSiteAccesses[$liveIndex];
					}
					//print_r($missingUserSiteAccesses);
					
					$updateUserSiteAccessQuery = pdoMultiInsert('bigfix_site_access', $missingUserSiteAccesses, $db);
					$updateUserSiteAccessQuery->execute();
					
					foreach($missingUserSiteAccesses as $missingUserSiteAccess) {
						echo 'BigFix User "'.$missingUserSiteAccess['bigfix_user_name'].'" assigned access to Site "'.$missingUserSiteAccess['site_name'].'".<br/>';
					}
					
					$changeLogSQL = 
						"INSERT INTO database_change_log ".
						"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
						"VALUES (:userID, :timestamp, 'BigFix Data Synchronization', :message, 'bigfix_site_access')";
					
					$message = 'Added new BigFix User Site Privilage(s) ';
					foreach($missingUserSiteAccesses as $key => $missingUserSiteAccess) {
						if($key != 0) {
							$message .= ', ';
						}
						$message .= '"'.$missingUserSiteAccess['bigfix_user_name'].'" ('.$missingUserSiteAccess['site_name'].')';
					}
					$message .= ' to the database.';
					
					$changeLogQuery = $db->prepare($changeLogSQL);
					
					$changeLogQuery->bindParam(':userID', $userID, PDO::PARAM_STR);
					$changeLogQuery->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
					$changeLogQuery->bindParam(':message', $message, PDO::PARAM_STR);
					
					$changeLogQuery->execute();
				}
				if ($outdatedUserSiteAccessImplode != array()) {
					foreach($outdatedUserSiteAccessImplode as $dbIndex => $outdatedImploded) {
						$outdatedUserSiteAccesses[] = $datbaseUserSiteAccesses[$dbIndex];
					}
					//print_r($outdatedUserSiteAccesses);
					
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
					
					foreach($outdatedUserSiteAccesses as $outdatedUserSiteAccess) {
						echo 'Access for BigFix User "'.$outdatedUserSiteAccess['bigfix_user_name'].'" to Site "'.$outdatedUserSiteAccess['site_name'].'" removed from the database, as it is no longer supported by BigFix.<br/>';
					}
					
					$changeLogSQL = 
						"INSERT INTO database_change_log ".
						"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
						"VALUES (:userID, :timestamp, 'BigFix Data Synchronization', :message, 'bigfix_site_access');";
					
					$message = 'Removed Outdated BigFix User Site Privilage(s) ';
					foreach($outdatedUserSiteAccesses as $key => $outdatedUserSiteAccess) {
						if($key != 0) {
							$message .= ', ';
						}
						$message .= '"'.$outdatedUserSiteAccess['bigfix_user_name'].'" ('.$outdatedUserSiteAccess['site_name'].')';
					}
					$message .= ' from the database.';
					
					$changeLogQuery = $db->prepare($changeLogSQL);
					
					$changeLogQuery->bindParam(':userID', $userID, PDO::PARAM_STR);
					$changeLogQuery->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
					$changeLogQuery->bindParam(':message', $message, PDO::PARAM_STR);
					
					$changeLogQuery->execute();
				}
			}
		*/	
		$db->commit();
	}
	catch (\PDOException $e) {
		echo '<span style="color:#FF0000;">ERROR: ';
		if ($bigFixLoginQuery->errorCode() != 0) {
			echo 'Unable to access BigFix Server credentials.';
		}
		//else if () {
		//	echo '';
		//}
		else {
			echo $e->getMessage();
		}
		echo '</span>';
		$db->rollback();
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