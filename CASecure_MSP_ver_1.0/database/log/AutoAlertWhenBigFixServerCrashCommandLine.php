<?php
	$timestamp = date("Y-m-d H:i:sO");
	//echo "\nTime = ".$timestamp."\n";//"<br />";
	
	//$userName = implode(" ", array_slice($argv, 1, 1));
	//$password = implode(" ", array_slice($argv, 2, 1));
	//$server = implode(" ", array_slice($argv, 3, 1)); // Must be entered with periods "."s instead of "%2E"s
	
	$userID = $_GET['uid'];
	
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
					"WHERE user_id = :userID".
				");";
			
			$bigFixLoginQuery = $db->prepare($bigFixLoginSQL);
			$bigFixLoginQuery->bindParam(':userID', $userID, PDO::PARAM_STR);
			$bigFixLoginQuery->execute();
			$bigFixLogin = $bigFixLoginQuery->fetch(PDO::FETCH_ASSOC);
			
			$bigFixUser = $bigFixLogin['bigfix_user_name'];
			$bigFixPassword = $bigFixLogin['bigfix_password'];
			$bigFixServer = $bigFixLogin['bigfix_server'];
			
			
			$url = "http://localhost/CASecure_MSP_ver_1.0/proxies/MicrosoftPatchComplianceReport.php?user=".$bigFixUser."&pass=".$bigFixPassword."&serv=".$bigFixServer."&cg=All Machines";
			
			$xml = simplexml_load_file($url);
			
			$currentDateTime = new DateTime("now");
			$currentTime = strtotime($currentDateTime->format('D, M d, Y h:i:s A'));
			//echo($currentTime)."<br/>";
			$updateStatusGood = array();
			
			foreach($xml->Query->Result->Tuple as $key => $besComputer) {
				$lastReportTime = $besComputer->Answer[5]->__toString();
				$lastReportTimeString = str_replace("<br>", " ", $lastReportTime);
				//$lastReportTimeDateTime = DateTime::createFromFormat('D, M d, Y h:i:s A', $lastReportTimeString);
				$lastReportTimeTime = strtotime($lastReportTimeString);
				$updateHoursLag = abs($currentTime - $lastReportTimeTime)/(60*60);

				if($updateHoursLag >= 24) {
					$updateStatusGood[] = false;
				}
				else {
					$updateStatusGood[] = true;
				}
			}
			if (count(array_unique($updateStatusGood)) === 1) {
				if (array_unique($updateStatusGood)[0] == false) {
					echo "BigFix Server out of date.  Alert Created and sent to all User Groups.<br/>";
					
					$userGroupsListSQL = 
						"SELECT user_group_id ".
						"From user_groups";
					$userGroupsListQuery = $db->query($userGroupsListSQL);
					$userGroupsListArray = $userGroupsListQuery->fetchAll(PDO::FETCH_ASSOC);
					//print_r($userGroupsListArray);
					
					foreach($userGroupsListArray as $userGroup) { 
						$alertSQL = 
							"INSERT INTO alerts ".
							"(user_group_id, issued_by_id, title, message, timestamp, active) ".
							"VALUES (:userGroupID, :userID, 'BigFix Server Stagnation', 'The server has not refreshed in 24 hours, contact the administrator to reset the server.', :timestamp, true);";
						$alertQuery = $db->prepare($alertSQL);
						$alertQuery->bindParam(':userGroupID', $userGroup['user_group_id'], PDO::PARAM_STR);
						$alertQuery->bindParam(':userID', $userID, PDO::PARAM_STR);
						$alertQuery->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
						$alertQuery->execute();
						
					}
					
					$newAlertId = $db->lastInsertId();
					
					
					$changeLogSQL = 
						"INSERT INTO database_change_log ".
						"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
						"VALUES (:userID, :timestamp, 'Auto Alert', :message, 'alerts');";
					
					$changeLogQuery = $db->prepare($changeLogSQL);
					
					$message = 'Auto Alert "'.$title.'" (id = '.$newAlertId.') for All User Groups.';
					
					$changeLogQuery->bindParam(':userID', $userID, PDO::PARAM_STR);
					$changeLogQuery->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
					$changeLogQuery->bindParam(':message', $message, PDO::PARAM_STR);
					
					$changeLogQuery->execute();
					
				}
				else {
					echo "BigFix Server OK.<br/>";
				}
			}
			else {
				echo "BigFix Server OK, but some macines require updating.<br/>";
			}
			//print_r();
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