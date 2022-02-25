<?php
	
	header('Content-type: application/xml');
	
	$timestamp = date("Y-m-d H:i:sO");
	//echo "\nTime = ".$timestamp."\n";//"<br />";
	
	$fileName = basename(__FILE__, '.php').'.php';
	$fileDirectory = getcwd();
	$requestURI = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http")."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	
	$currentUserID = $_GET['cid'];
	$userGroupID = $_GET['gid'];
	$userIDs = json_decode($_GET['ids']);
	
	if (sizeof($userIDs) == 0)
		$userIDs = array(0);
	
	$db_host = "localhost";
	$db_name = "CASecure1";
	$db_username = "postgres";
	$db_password = "abc.123";
	
	$xml= new SimpleXMLElement('<PDO/>'); 
	$connectionXML = $xml->addChild('Connection');
	try {
		$connectResultXML = $connectionXML->addChild('Result');
		
		//$dsn = 'pgsql:host='.$db_host.';dbname='.$db_name
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
				$metaXML->addChild('Name', "Update User Group");
				$metaXML->addChild('Description', "This query updates a User Group, and populates it with a list of User Members.");
				$paramsXML = $metaXML->addChild('Parameters');
				$param1XML = $paramsXML->addChild('Parameter');
				$param1XML->addChild('Name', 'Current User ID');
				$param1XML->addChild('URL', 'cid');
				$param1XML->addChild('Value', $currentUserID);
				$param1XML->addChild('Description', 'The User ID of the current User');
				$param2XML = $paramsXML->addChild('Parameter');
				$param2XML->addChild('Name', 'User Group ID');
				$param2XML->addChild('URL', 'gid');
				$param2XML->addChild('Value',$userGroupName);
				$param2XML->addChild('Description', 'The Name of the User Group being created.');
				$param3XML = $paramsXML->addChild('Parameter');
				$param3XML->addChild('Name', 'User Group User IDs');
				$param3XML->addChild('URL', 'ids');
				//$param3XML->addChild('Value',$_GET['ids']);
				$param3ValueXML = $param3XML->addChild('Value');
				foreach($userIDs as $userID){
					$param3ValueXML->addChild('user_id', $userID);
				}
				$param3XML->addChild('Description', 'Array of the User IDs for users that will be members of the User Group being created');
				$resultXML = $queryXML->addChild('Result');
				
				$start = microtime(true);
				
				$actionsXML = $resultXML->addChild('Actions');
				
				$storedUserIDsSQL = 
					"SELECT user_id ".
					"FROM user_group_users ".
					"WHERE user_group_id = :userGroupID; ";
				$storedUserIDsQuery = $db->prepare($storedUserIDsSQL);
				$storedUserIDsQuery->bindParam(":userGroupID", $userGroupID, PDO::PARAM_STR);
				$storedUserIDsQuery->execute();
				$storedUserIDsArray = $storedUserIDsQuery->fetchAll(PDO::FETCH_COLUMN);
				//print_r($storedUserIDsArray);
				//echo "<br/>";
				//print_r($userIDs);
				//echo "<br/>";
				
				$addedUserGroups = array_values(array_diff($userIDs, $storedUserIDsArray));
				$subtractedUserGroups = array_values(array_diff($storedUserIDsArray, $userIDs));
				//print_r($addedUserGroups);
				//echo "<br/>";
				//print_r($subtractedUserGroups);
				//echo "<br/>";
				
				$userGroupNameSQL = 
					"SELECT user_group_name ".
					"FROM user_groups ".
					"WHERE user_group_id = :userGroupID; ";
				$userGroupNameQuery = $db->prepare($userGroupNameSQL);
				$userGroupNameQuery->bindParam(":userGroupID", $userGroupID, PDO::PARAM_STR);
				$userGroupNameQuery->execute();
				$userGroupName = $userGroupNameQuery->fetch(PDO::FETCH_COLUMN);
				//print_r($userGroupName);
				//echo "<br/>";
				
				$fetchOriginalUserGroupInfoXML = $actionsXML->addChild('Action');
				$fetchOriginalUserGroupInfoXML->addAttribute('type', 'Fetch original User Group info');
				$fetchOriginalUserGroupInfoXML->addChild('Status', 'Success');
				$fetchOriginalUserGroupInfoXML->addChild('Details', '');
				$originalUserGroupInfoXML = $fetchOriginalUserGroupInfoXML->addChild('User_Group_Info');
				$originalUserGroupInfoXML->addChild('user_group_name', $userGroupName);
				$originalUsersXML = $originalUserGroupInfoXML->addChild('Users');
				foreach($storedUserIDsArray as $storedUserID) {
					$originalUsersXML->addChild('user_id', $storedUserID);
				}
				
				if($addedUserGroups == array() && $subtractedUserGroups == array()) { 
					$actionsXML->addChild('Action');
					
					//echo "No Changes Made.";
				}
				else { 
					if($addedUserGroups != array()) {
						$addedUserNamesSQL = "";
						foreach($addedUserGroups as $key => $groupID) {
							if($key != 0) {
								$addedUserNamesSQL .= " UNION ALL ";
							}
							$addedUserNamesSQL .= 
								"SELECT user_id, user_name ".
								"FROM users ".
								"WHERE user_id = ?";
						}
						$addedUserNamesSQL .= ";";
						//echo $addedUserNamesSQL."<br/>";
						$addedUserNamesQuery = $db->prepare($addedUserNamesSQL);
						$addedUserNamesQuery->execute($addedUserGroups);
						$addedUserNamesArray = $addedUserNamesQuery->fetchAll(PDO::FETCH_ASSOC);
						//print_r($addedUserNamesArray);
						//echo "<br/>";
						
						$insertUserInfo = array();
						foreach($addedUserNamesArray as $addedUser) {
							$insertUserInfo[] = array(
								"user_group_id" => $userGroupID, 
								"user_id" => $addedUser["user_id"], 
								"last_updated_by_id" => $currentUserID, 
								"timestamp" => $timestamp
							);
						}
						//print_r($insertUserInfo);
						
						$addUserGroupUsersQuery = pdoMultiInsert('user_group_users', $insertUserInfo, $db);
						$addUserGroupUsersQuery->execute();
						
						$message = 'Added User';
						$message .= ((sizeof($addedUserNamesArray) > 1) ? 's ' : ' ');
						foreach($addedUserNamesArray as $key => $addedUser) { 
							if($key != 0) {
								$message .= ', ';
							}
							$message .= $addedUser['user_name'].' ('.$addedUser['user_id'].')';
						}
						$message .= ' to User Group '.$userGroupName.' ('.$userGroupID.').';
						echo $message."<br/>";
						$changeLogSQL = 
						"INSERT INTO database_change_log ".
						"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
						"VALUES (:currentUserID, :timestamp, 'User Group Update', :message, 'user_group_users');";
						
						$changeLogQuery = $db->prepare($changeLogSQL);
						$changeLogQuery->bindParam(":currentUserID", $currentUserID , PDO::PARAM_STR);
						$changeLogQuery->bindParam(":timestamp", $timestamp, PDO::PARAM_STR);
						$changeLogQuery->bindParam(":message", $message, PDO::PARAM_STR);
						$changeLogQuery->execute();
						echo "Transaction Logged.<br/>";
					
					}
					if($subtractedUserGroups != array()) {
						$subtractedUserNamesSQL = "";
						foreach($subtractedUserGroups as $key => $groupID) {
							if ($key != 0) {
								$subtractedUserNamesSQL .= " UNION ALL ";
							}
							$subtractedUserNamesSQL .= 
								"SELECT user_id, user_name ".
								"FROM users ".
								"WHERE user_id = ?";
						}
						$subtractedUserNamesSQL .= ";";
						//echo $subtractedUserNamesSQL."<br/>";
						$subtractedUserNamesQuery = $db->prepare($subtractedUserNamesSQL);
						$subtractedUserNamesQuery->execute($subtractedUserGroups);
						$subtractedUserNamesArray = $subtractedUserNamesQuery->fetchAll(PDO::FETCH_ASSOC);
						//print_r($subtractedUserNamesArray);
						//echo "<br/>";
						
						$deleteUserInfo = array();
						foreach($subtractedUserNamesArray as $subtractedUser) {
							$deleteUserInfo[] = array(
								"user_group_id" => $userGroupID,
								"user_id" => $subtractedUser['user_id']
							);
						}
						//print_r($deleteUserInfo);
						//echo "<br/>";
						
						$subtractUserGroupUsersQuerry = pdoMultiDelete('user_group_users', $deleteUserInfo, $db);
						$subtractUserGroupUsersQuerry->execute();
						
						$message = 'Removed User';
						$message .= ((sizeof($subtractedUserNamesArray) > 1) ? 's ' : ' ');
						foreach($subtractedUserNamesArray as $key => $subtractedUser) {
							if ($key != 0) {
								$message .= ', ';
							}
							$message .= $subtractedUser['user_name'].' ('.$subtractedUser['user_id'].')';
						}
						$message .= ' from User Group '.$userGroupName.' ('.$userGroupID.').';
						echo $message.'<br/>';
						$changeLogSQL = 
						"INSERT INTO database_change_log ".
						"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
						"VALUES (:currentUserID, :timestamp, 'User Group Update', :message, 'user_group_users');";
						
						$changeLogQuery = $db->prepare($changeLogSQL);
						$changeLogQuery->bindParam(":currentUserID", $currentUserID , PDO::PARAM_STR);
						$changeLogQuery->bindParam(":timestamp", $timestamp, PDO::PARAM_STR);
						$changeLogQuery->bindParam(":message", $message, PDO::PARAM_STR);
						$changeLogQuery->execute();
						echo "Transaction Logged.<br/>";
					}
				}
			$db->commit();
		}
		catch (\PDOException $e) {
			$errorXML = $queryXML->addChild('Error');
			/*if ($bigFixLoginQuery->errorCode() != 0) {
				$fetchBigFixLoginXML = $actionsXML->addChild('Action');
				$fetchBigFixLoginXML->addAttribute('type', 'BigFix Console credentials for the Current User');
				$fetchBigFixLoginXML->addChild('Status', "Failure");
				$errorDescription = 'Unable to access BigFix Server credentials.';
				$fetchBigFixLoginXML->addChild('Details', $errorDescription);
			}
			else if ($cgFetchQuery->errorCode() != 0) {
				$databaseComputerGroupsXML = $actionsXML->addChild('Action');
				$databaseComputerGroupsXML->addAttribute('type', 'List of Computer Groups originally in the Database');
				$databaseComputerGroupsXML->addChild('Status', 'Failure');
				$errorDescription = 'Unable to retrieve list of Computer Groups from database.';
				$databaseComputerGroupsXML->addChild('Details', $errorDescription);
			}
			else if (isset($updateCGsQuery) && $updateCGsQuery->errorCode() != 0) {
				$insertComputerGroupsXML = $actionsXML->addChild('Action');
				$insertComputerGroupsXML->addAttribute('type', 'Rectify the Database');
				$insertComputerGroupsXML->addChild('Status', 'Failure');
				$errorDescription = 'Failed to Insert new Computer Groups into database.';
				$insertComputerGroupsXML->addChild('Description', $errorDescription);
			}
			else if (isset($deleteCGsQuery) && $deleteCGsQuery->errorCode() != 0) {
				$RemoveOutdatedComputerGroupsXML = $actionsXML->addChild('Action');
				$RemoveOutdatedComputerGroupsXML->addAttribute('type', 'Rectify the Database');
				$RemoveOutdatedComputerGroupsXML->addChild('Status', 'Failure');
				$errorDescription = 'Failed to Remove Outdated Computer Groups from database.';
				$RemoveOutdatedComputerGroupsXML->addChild('Description', $errorDescription);
			}
			else if (isset($changeLogQuery) && $changeLogQuery->errorCode() != 0) {
				$changeLogXML = $resultXML->addChild('Change_Log');
				$changeLogXML->addChild('Status', 'Failed to log Transaction');
				$errorDescription = 'Unable to Log Transaction due to Error, entire transaction has been Undone.';
				$changeLogXML->addChild('Details', $errorDescription);
			}
			*/
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
		catch (Exception $e) {
			$errorXML = $queryXML->addChild('Error');
			$errorCode = $e->getCode();
			$errorMessage = $e->getMessage();
			
			$errorXML->addChild('Error_Code', $errorCode);
			$errorXML->addChild('Details', $errorMessage);
			
			$db->rollback();
			
			try {
				$errorLogSQL = 
					"INSERT INTO error_log ".
					"(user_id, description, error_code, error_message, exception_type, timestamp, file_name, file_directory, request_uri) ".
					"VALUES (:userID, :description, :errorCode, :errorMessage, 'Unknown Error', :timestamp , :fileName, :fileDirectory, :requestURI);";
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
				$errorLogDetailsXML->addChild('exception_type', 'Unknown Error');
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
	catch(PDOException $e) {
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
		//print_r($rowsSQL);
		//echo "<br/>";
		$sql = "INSERT INTO ".$tableName." (".implode(", ", $columnNames).") VALUES ".implode(", ", $rowsSQL);
		
		$pdoStatement = $pdoObject->prepare($sql);
		
		foreach($toBind as $param => $val) {
			$pdoStatement->bindValue($param, $val); //bindParam
		}
		
		return $pdoStatement;//->execute();
	}
	
	function pdoMultiDelete($tableName, $data, $pdoObject) {
		$rowSQL = array();
		
		$toBind = array();
		
		$columnNames = array_keys($data[0]);
		
		$sql = "DELETE FROM ".$tableName." WHERE ";
		
		foreach($columnNames as $key => $columnName) {
			if($key != 0) {
				$sql .= " AND ";
			}
			$columnArray = array();
			$columnArray = array_column($data, $columnName);
			//print_r($columnArray);
			//echo "<br/>";
			$sql .= $columnName." IN (";
			foreach($columnArray as $arrayIndex => $columnValue) {
				if($arrayIndex != 0) {
					$sql .= ", ";
				}
				$param = ":".$columnName.$arrayIndex;
				$sql .= $param;
				$toBind[$param] = $columnValue;
			}
			$sql .= ")";
		}
		$sql .= ";";
		//echo $sql;
		//echo "<br/>";
		//print_r($toBind);
		//echo "<br/>";
		
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