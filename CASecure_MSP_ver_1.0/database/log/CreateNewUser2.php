<?php
	$timestamp = date("Y-m-d H:i:sO");
	//echo "\nTime = ".$timestamp."\n";//"<br />";
	
	$userName = $_GET['name'];
	$password = $_GET['pass'];
	$welcomeName = $_GET['wel'];
	$updater = $_GET['up'];
	$admin = $_GET['admin'];
	$bigFixUser = $_GET['bigfix'];
	$primaryEmail = $_GET['email'];
	$defaultSite = $_GET['ds'];
	$defaultComputerGroup = $_GET['dcg'];
	
	$db_host = "localhost";
	$db_name = "CASecure1";
	$db_username = "postgres";
	$db_password = "abc.123";
	
	try {
		//$dsn = 'pgsql:host='.$db_host.';dbname='.$db_name
		$db = new PDO('pgsql:host='.$db_host.';dbname='.$db_name,$db_username,$db_password);
		//$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		//$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		//$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
		//$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
		//$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		
		echo "Database Connection, Successful.<br/>";
	}
	catch(PDOException $e) {
		echo "Failed to Connect to Database.  Please Email Site Administrator.<br/>";
		echo $e->getMessage();
		//echo errorHandle($e);
		//$db->rollBack();
	}
	
	$sqlTableName = "users";
	if ($updater == null && $welcomeName != null) {
		$sql =
			"INSERT INTO ".$sqlTableName." ".
			"(user_name, password, welcome_name, timestamp, is_admin) ".
			"VALUES (:userName, :password, :welcomeName, :timestamp, :admin)";
		
		$query = $db->prepare($sql);
		
		$query->bindParam(':userName', $userName, PDO::PARAM_STR);
		$query->bindParam(':password', $password, PDO::PARAM_STR);
		$query->bindParam(':welcomeName', $welcomeName, PDO::PARAM_STR);
		$query->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
		$query->bindParam(':admin', $admin, PDO::PARAM_STR);
	}
	else if ($welcomeName == null && $updater != null) {
		$sql =
			"INSERT INTO ".$sqlTableName." ".
			"(user_name, password, last_updated_by_id, timestamp, is_admin) ".
			"VALUES (:userName, :password, :updater, :timestamp, :admin)";
		
		$query = $db->prepare($sql);
	
		$query->bindParam(':userName', $userName, PDO::PARAM_STR);
		$query->bindParam(':password', $password, PDO::PARAM_STR);
		$query->bindParam(':updater', $updater, PDO::PARAM_STR);
		$query->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
		$query->bindParam(':admin', $admin, PDO::PARAM_STR);
	}
	else if ($updater == null && $welcomeName == null) {
		$sql =
			"INSERT INTO ".$sqlTableName." ".
			"(user_name, password, timestamp, is_admin) ".
			"VALUES (:userName, :password, :timestamp, :admin)";
		
		$query = $db->prepare($sql);
	
		$query->bindParam(':userName', $userName, PDO::PARAM_STR);
		$query->bindParam(':password', $password, PDO::PARAM_STR);
		$query->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
		$query->bindParam(':admin', $admin, PDO::PARAM_STR);
	}
	else {
		$sql =
			"INSERT INTO ".$sqlTableName." ".
			"(user_name, password, welcome_name, last_updated_by_id, timestamp, is_admin) ".
			"VALUES (:userName, :password, :welcomeName, :updater, :timestamp, :admin);";
		
		$query = $db->prepare($sql);
	
		$query->bindParam(':userName', $userName, PDO::PARAM_STR);
		$query->bindParam(':password', $password, PDO::PARAM_STR);
		$query->bindParam(':welcomeName', $welcomeName, PDO::PARAM_STR);
		$query->bindParam(':updater', $updater, PDO::PARAM_STR);
		$query->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
		$query->bindParam(':admin', $admin, PDO::PARAM_STR);
	}	
	
	$query2 = $db->query('SELECT');
	$query3 = $db->query('SELECT');
	$query4 = $db->query('SELECT');
	$query5 = $db->query('SELECT');
	$query6 = $db->query('SELECT');
	
	try {
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		$db->beginTransaction();
			
			$query->execute();
			
			$newUserId = $db->lastInsertId();
			
			if ($updater == null) {
				$consoleToPortalSQL = 
					"INSERT INTO console_to_portal ".
					"(user_id, bigfix_user_name, timestamp) ".
					"VALUES (:newUserId, :bigFixUser, :timestamp);";
				
				$query2 = $db->prepare($consoleToPortalSQL);
				
				$query2->bindParam(':newUserId', $newUserId, PDO::PARAM_STR);
				$query2->bindParam(':bigFixUser', $bigFixUser, PDO::PARAM_STR);
				$query2->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
				
				$changeLogSQL = 
					"INSERT INTO database_change_log ".
					"(timestamp, type_of_change, action_taken, affected_tables ) ".
					"VALUES (:timestamp, 'User Creation', :message, 'users, console_to_portal, email_info');";
				
				$query3 = $db->prepare($changeLogSQL);
				
				$message = 'Created new portal user "'.$userName.'" linked to BigFix user "'.$bigFixUser.'".';
				
				$query3->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
				$query3->bindParam(':message', $message, PDO::PARAM_STR);
			}
			else {
				$consoleToPortalSQL = 
					"INSERT INTO console_to_portal ".
					"(user_id, bigfix_user_name, last_updated_by_id, timestamp) ".
					"VALUES (:newUserId, :bigFixUser, :updater, :timestamp);";
				
				$query2 = $db->prepare($consoleToPortalSQL);
				
				$query2->bindParam(':newUserId', $newUserId, PDO::PARAM_STR);
				$query2->bindParam(':bigFixUser', $bigFixUser, PDO::PARAM_STR);
				$query2->bindParam(':updater', $updater, PDO::PARAM_STR);
				$query2->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
				
				$changeLogSQL = 
					"INSERT INTO database_change_log ".
					"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
					"VALUES (:updater, :timestamp, 'User Creation', :message, 'users, console_to_portal, email_info');";
				
				$query3 = $db->prepare($changeLogSQL);
				
				$message = 'Created new portal user "'.$userName.'" linked to BigFix user "'.$bigFixUser.'".';
				
				$query3->bindParam(':updater', $updater, PDO::PARAM_STR);
				$query3->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
				$query3->bindParam(':message', $message, PDO::PARAM_STR);
			}
			
			$primaryEmailSQL = 
				"INSERT INTO email_info ".
				"(user_id, email_address, priority) ".
				"VALUES (:newUserId, :primaryEmail, 1);";
				
			$query4 = $db->prepare($primaryEmailSQL);
			
			$query4->bindParam(':newUserId', $newUserId, PDO::PARAM_STR);
			$query4->bindParam(':primaryEmail', $primaryEmail, PDO::PARAM_STR);
			
			$bigFixLoginFetchSQL = 
				"SELECT (bigfix_user_name, bigfix_password, bigfix_server) ".
				"FROM bigfix_logins ".
				"WHERE bigfix_user_name IN (".
					"SELECT bigfix_user_name ".
					"FROM console_to_portal ".
					"WHERE user_id = :updater".
				");";
			
			$bigFixLoginFetchQuery = $db->prepare($bigFixLoginFetchSQL);
			$bigFixLoginFetchQuery->bindParam(':updater', $updater, PDO::PARAM_STR);
			$bigFixLoginFetchQuery->execute();
			$row = $bigFixLoginFetchQuery->fetch(PDO::FETCH_NUM);
			$bigFixLogin = explode(",", substr($row[0], 1, -1));
			$bigfixCurrentUser = $bigFixLogin[0];
			$bigFixCurrentPassword = $bigFixLogin[1];
			$bigFixCurrentServer = $bigFixLogin[2];
			//echo '<br/>'.$bigfixCurrentUser.', '.$bigFixCurrentPassword.', '.$bigFixCurrentServer.'<br/>';
			//print_r($row);
			
			$siteFetchSQL =
				"SELECT (site_name, site_type)".
				"FROM sites;";
			
			$siteFetchQuery = $db->query($siteFetchSQL);
			$site = $siteFetchQuery->fetchAll();//fetch(PDO::FETCH_ASSOC);
			print_r($site);
			echo "<br/>";
			
			$siteURL = "http://localhost/CASecure_MSP_ver_1.0/proxies/MasterSiteList.php?user=".$bigfixCurrentUser."&pass=".$bigFixCurrentPassword."&serv=".$bigFixCurrentServer;
			
			//echo "<br/>".$siteURL."<br/>";
			
			$siteXML = simplexml_load_file($siteURL);
							
			$userPermissionsTableName = "user_site_access";
			$userPermissionsData = array();
			$z = 0;
			
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
				
				$dataURL = "http://localhost/CASecure_MSP_ver_1.0/proxies/UserSiteAccessPermissionCheck.php?user=".$bigfixCurrentUser."&pass=".$bigFixCurrentPassword."&serv=".$bigFixCurrentServer."&type=".$type."&site=".$siteName."&search=".$bigFixUser;
				
				$xml = simplexml_load_file($dataURL);
						
				$permission = $xml->SitePermission->Permission;
				
				$userPermissionsData[$z] = 
					array(
						'user_id' => $newUserId, 
						'site_name' => $siteName->__toString(), 
						'user_privilege' => $permission->__toString()
					);
				$z++;
			}
			  
			$query5 = pdoMultiInsert($userPermissionsTableName, $userPermissionsData, $db);
			
			$userDefaultsSQL = 
				"INSERT INTO user_defaults ".
				"(user_id, default_site, default_computer_group) ".
				"VALUES (:newUserId, :defaultSite, :defaultComputerGroup)";
			
			$query6 = $db->prepare($userDefaultsSQL);
			
			$query6->bindParam(':newUserId', $newUserId, PDO::PARAM_STR);
			$query6->bindParam(':defaultSite', $defaultSite, PDO::PARAM_STR);
			$query6->bindParam(':defaultComputerGroup', $defaultComputerGroup, PDO::PARAM_STR);
			
			$query2->execute();
			
			$query4->execute();
			
			$query5->execute();
			
			$query6->execute();
			
			$query3->execute();
			
			//if ($query->errorCode() == 0 && $query2->errorCode() == 0 && $query3->errorCode() == 0 && $query4->errorCode() == 0) {
				echo "New User '".$userName."' was successfully created.<br />";
				echo 'Database User Id = '.$newUserId.'<br/>';
				echo "New User '".$userName."' linked to BigFix User '".$bigFixUser."'.<br/>";
				echo "Pimary Email '".$primaryEmail."' added.<br/>";
				echo "Site Privileges established.<br/>";
				echo "User Defaults Set.<br/>";
				echo "Transaction Logged";
			//}
			
		$db->commit();
	}
	catch (\PDOException $e) {
		//$sqlErrors = $query->errorInfo();
		//echo ($sqlErrors[2]);
		//echo $e->getCode();
		echo '<span style="color:#FF0000";>ERROR: ';
		if ($query->errorCode() == 23502) {
			echo 'Username and Password cannot be empty.';
		}
		else if ($query->errorCode() == 23503) {
			echo 'You are not able to create users.';
		}
		else if ($query->errorCode() == 23505) {
			echo 'Username "'.$userName.'" already exists. Enter a differnt value for Username.';
		}
		else if ($query2->errorCode() != 0) {
			echo 'Failure to set BigFix user.';
		}
		else if ($query3->errorCode() != 0) {
			echo 'Failure to log Transaction.';
		}
		else if ($query4->errorCode() == 23502) {
			echo 'Email Address cannot be empty.';
		}
		else if ($query4->errorCode() == 23505) {
			echo 'Email Address "'.$primaryEmail.'" is already being used.  Enter a different Email Address.';
		}
		else if ($query5->errorCode() != 0) {
			echo 'Site Privilages could not be Established.';
		}
		else {
			echo $e->getMessage();
		}
		echo '</span>';
		$db->rollBack();
		//throw $e;
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