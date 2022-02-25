<?php
	
	header('Content-type: application/xml');
	
	$timestamp = date("Y-m-d H:i:sO");
	//echo "\nTime = ".$timestamp."\n";//"<br />";
	
	$fileName = basename(__FILE__, '.php').'.php';
	$fileDirectory = getcwd();
	$requestURI = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http")."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	
	$userId = $_GET['uid'];
	$password = $_GET['pass'];
	$welcomeName = $_GET['wel'];
	$updater = $_GET['cid'];
	$admin = $_GET['admin'];
	$bigFixUser = $_GET['bigfix'];
	$primaryEmail = $_GET['email'];
	$defaultSite = $_GET['ds'];
	$defaultComputerGroup = $_GET['dcg'];
	
	$db_host = "localhost";
	$db_name = "CASecure1";
	$db_username = "postgres";
	$db_password = "abc.123";
	
	//echo $admin."<br/>";
	
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
				$metaXML->addChild('Name', "Update User");
				$metaXML->addChild('Description', "This creates a new User Account.");
				$paramsXML = $metaXML->addChild('Parameters');
				$param1XML = $paramsXML->addChild('Parameter');
				$param1XML->addChild('Name', 'Current User ID');
				$param1XML->addChild('URL', 'cid');
				$param1XML->addChild('Value', $updater);
				$param1XML->addChild('Description', 'The User ID of the current User');
				$param2XML = $paramsXML->addChild('Parameter');
				$param2XML->addChild('Name', 'Password');
				$param2XML->addChild('URL', 'pass');
				$param2XML->addChild('Value', $password);
				$param2XML->addChild('Description', 'Password for the User account.');
				$param3XML = $paramsXML->addChild('Parameter');
				$param3XML->addChild('Name', 'User First Name');
				$param3XML->addChild('URL', 'wel');
				$param3XML->addChild('Value', $welcomeName);
				$param3XML->addChild('Description', 'Typically the First Name of the User.  The User will be greeted with this name.');
				$param4XML = $paramsXML->addChild('Parameter');
				$param4XML->addChild('Name', 'User Administrative Status');
				$param4XML->addChild('URL', 'admin');
				$param4XML->addChild('Value', $admin);
				$param4XML->addChild('Description', 'Boolean expressing whether the the User will be grated Administrative Priviledges.');
				$param5XML = $paramsXML->addChild('Parameter');
				$param5XML->addChild('Name', 'BigFix User Name');
				$param5XML->addChild('URL', 'bigfix');
				$param5XML->addChild('Value', $bigFixUser);
				$param5XML->addChild('Description', 'The name of the BigFix Console user to be attached to the User account being created.');
				$param6XML = $paramsXML->addChild('Parameter');
				$param6XML->addChild('Name', 'Primary Email Address');
				$param6XML->addChild('URL', 'email');
				$param6XML->addChild('Value', $primaryEmail);
				$param6XML->addChild('Description', 'Primary Email Address of the User account being created.');
				$param7XML = $paramsXML->addChild('Parameter');
				$param7XML->addChild('Name', 'Default Site');
				$param7XML->addChild('URL', 'ds');
				$param7XML->addChild('Value', $defaultSite);
				$param7XML->addChild('Description', 'The Default Site that will be used to access report data when the User first logs into their account.');
				$param8XML = $paramsXML->addChild('Parameter');
				$param8XML->addChild('Name', 'Default Computer Group');
				$param8XML->addChild('URL', 'dcg');
				$param8XML->addChild('Value', $defaultComputerGroup);
				$param8XML->addChild('Description', 'The Default Computer Group that will be used to access report data when the User first logs into their account.');
				$resultXML = $queryXML->addChild('Result');
				
				$start = microtime(true);
				
				$actionsXML = $resultXML->addChild('Actions');
				
				$fetchOriginalUserSQL = 
					"SELECT user_name, password, welcome_name, is_admin ".
					"FROM users ".
					"WHERE user_id = :userId;";
				$fetchOriginalUserQuery = $db->prepare($fetchOriginalUserSQL);
				$fetchOriginalUserQuery->bindParam(":userId", $userId, PDO::PARAM_STR);
				$fetchOriginalUserQuery->execute();
				$originalUserInfo = $fetchOriginalUserQuery->fetch();
				$userName = $originalUserInfo['user_name'];
				$originalPassword = $originalUserInfo['password'];
				$originalWelcomeName = $originalUserInfo['welcome_name'];
				$originalAdminStatus = $originalUserInfo['is_admin'];
				//echo $currentPassword."<br/>";	
				//echo $currentWelcomeName."<br/>";
				//echo $currentAdminStatus."<br/>";
				//echo (($originalAdminStatus == TRUE)?"true":"false")."<br/>";
				
				$fetchOriginalBigFixUserNameSQL = 
					"SELECT bigfix_user_name ".
					"FROM console_to_portal ".
					"WHERE user_id = :userId;";
				$fetchOriginalBigFixUserNameQuery = $db->prepare($fetchOriginalBigFixUserNameSQL);
				$fetchOriginalBigFixUserNameQuery->bindParam(":userId", $userId, PDO::PARAM_STR);
				$fetchOriginalBigFixUserNameQuery->execute();
				$originalBigFixUserName = ($fetchOriginalBigFixUserNameQuery->fetch())['bigfix_user_name'];
				//echo $currentBigFixUserName."<br/>";
				
				$fetchOriginalPrimaryEmailSQL = 
					"SELECT email_address ".
					"FROM email_info ".
					"WHERE user_id = :userId AND priority = 1;";
				$fetchOriginalPrimaryEmailQuery = $db->prepare($fetchOriginalPrimaryEmailSQL);
				$fetchOriginalPrimaryEmailQuery->bindParam(":userId", $userId, PDO::PARAM_STR);
				$fetchOriginalPrimaryEmailQuery->execute();
				$originalPrimaryEmail = ($fetchOriginalPrimaryEmailQuery->fetch())['email_address'];
				//echo $currentPrimaryEmail."<br/>";
				
				$fetchOriginalDefaultsSQL = 
					"SELECT default_site, default_computer_group ".
					"FROM user_defaults ".
					"WHERE user_id = :userId;";
				$fetchOriginalDefaultsQuery = $db->prepare($fetchOriginalDefaultsSQL);
				$fetchOriginalDefaultsQuery->bindParam(":userId", $userId, PDO::PARAM_STR);
				$fetchOriginalDefaultsQuery->execute();
				$originalDefaults = $fetchOriginalDefaultsQuery->fetch();
				$originalDefaultSite = $originalDefaults['default_site'];
				$originalDefaultComputerGroup = $originalDefaults['default_computer_group'];
				//echo $currentDefaultSite."<br/>".$currentDefaultComputerGroup."<br/>";
				
				$fetchOriginalUserInfoXML = $actionsXML->addChild('Action');
				$fetchOriginalUserInfoXML->addAttribute('type', 'Fetch Original User Info');
				$fetchOriginalUserInfoXML->addChild('Status', "Success");
				$fetchOriginalUserInfoXML->addChild('Details', 'Fetched original data for User "'.$userName.'"');
				$originalUserInfoXML = $fetchOriginalUserInfoXML->addChild('Original_User_Info');
				$originalUserInfoXML->addAttribute('user_id', $userId);
				$originalUserInfoXML->addChild('user_name', $userName);
				$originalUserInfoXML->addChild('password', $originalPassword);
				$originalUserInfoXML->addChild('welcome_name', $originalWelcomeName);
				$originalUserInfoXML->addChild('is_admin', (($originalAdminStatus == TRUE)?"true":"false"));
				$originalUserInfoXML->addChild('bigfix_user_name', $originalBigFixUserName);
				$originalUserInfoXML->addChild('email_address', $originalPrimaryEmail);
				$originalUserInfoXML->addChild('default_site', $originalDefaultSite);
				$originalUserInfoXML->addChild('default_computer_group', $originalDefaultComputerGroup);
				
				$updateArray = array();
				$tablesArray = array();
				$messageArray = array();
				$updatePasswordQuery = $db->query('SELECT');
				$updateWelcomeNameQuery = $db->query('SELECT');
				$updateAdminStatusQuery = $db->query('SELECT');
				$updateBigFixUserNameQuery = $db->query('SELECT');
				$updatePrimaryEmailQuery = $db->query('SELECT');
				$updateDefaultSiteQuery = $db->query('SELECT');
				$updateDefaultComputerGroupQuery = $db->query('SELECT');
				
				if ($password != $originalPassword && $password != null) {
					$updatePasswordSQL = 
						"UPDATE users ".
						"SET password = :password ".
						"WHERE user_id = :userId;";
					$updatePasswordQuery = $db->prepare($updatePasswordSQL);
					$updatePasswordQuery->bindParam(":password", $password, PDO::PARAM_STR);
					$updatePasswordQuery->bindParam(":userId", $userId, PDO::PARAM_STR);
					$updatePasswordQuery->execute();
					//echo 'Password for user "'.$userName.'" Updated from "'.$originalPassword.'" to "'.$password.'".<br/>';
					
					$updateArray['password'] = $password;
					$messageArray['password'] = 'from "'.$originalPassword.'" to "'.$password.'"';
					array_push($tablesArray,'users');
				}
				
				if ($welcomeName != $originalWelcomeName && $welcomeName != null) {
					$updateWelcomeNameSQL = 
						"UPDATE users ".
						"SET welcome_name = :welcomeName ".
						"WHERE user_id = :userId;";
					$updateWelcomeNameQuery = $db->prepare($updateWelcomeNameSQL);
					$updateWelcomeNameQuery->bindParam(":welcomeName". $welcomeName, PDO::PARAM_STR);
					$updateWelcomeNameQuery->bindParam(":userId". $userId, PDO::PARAM_STR);
					$updateWelcomeNameQuery->execute();
					//echo 'Name on file for user "'.$userName.'" changed from "'.$originalWelcomeName.'" to "'.$welcomeName.'".<br/>';
					
					$updateArray['welcome_name'] = $welcomeName;
					$messageArray['welcome_name'] = 'from "'.$originalWelcomeName.'" to "'.$welcomeName.'"';
					array_push($tablesArray,'users');
				}
				
				($admin == "true") ? ($isAdmin = true) : ($isAdmin = false);
				//echo $isAdmin."<br/>";
				
				if ($isAdmin != $originalAdminStatus && $admin != null) {
					$updateAdminStatusSQL = 
						"UPDATE users ".
						"SET is_admin = :admin ".
						"WHERE user_id = :userId;";
					$updateAdminStatusQuery = $db->prepare($updateAdminStatusSQL);
					$updateAdminStatusQuery->bindParam(":admin", $admin, PDO::PARAM_STR);
					$updateAdminStatusQuery->bindParam(":userId", $userId, PDO::PARAM_STR);
					$updateAdminStatusQuery->execute();
					if ($isAdmin == true) {
						//echo 'User "'.$userName.'" granted Administrative Privilages.<br/>';
						$updateArray['is_admin'] = "Granted";
					}
					else {
						//echo 'Administrative Privilages Revoked from user "'.$userName.'".<br/>';
						$updateArray['is_admin'] = "Revoked";
					}
					$messageArray['is_admin'] = 'from "'.$admin.'" to "'.'"';
					array_push($tablesArray,'users');
				}
				
				if ($bigFixUser != $originalBigFixUserName && $bigFixUser != null) {
					$updateBigFixUserNameSQL = 
						"UPDATE console_to_portal ".
						"SET bigfix_user_name = :bigFixUser ".
						"WHERE user_id = :userId;";
					$updateBigFixUserNameQuery = $db->prepare($updateBigFixUserNameSQL);
					$updateBigFixUserNameQuery->bindParam(":bigFixUser", $bigFixUser, PDO::PARAM_STR);
					$updateBigFixUserNameQuery->bindParam(":userId", $userId, PDO::PARAM_STR);
					$updateBigFixUserNameQuery->execute();
					//echo 'User "'.$userName.'" attached BigFix User changed from "'.$originalBigFixUserName.'" to "'.$bigFixUser.'".<br/>';
					$updateArray['bigfix_user_name'] = $bigFixUser;
					$messageArray['bigfix_user_name'] = 'from "'.$originalBigFixUserName.'" to "'.$bigFixUser.'"';
					array_push($tablesArray,'console_to_portal');
				}
				
				if ($primaryEmail != $originalPrimaryEmail && $primaryEmail != null) {
					$updatePrimaryEmailSQL = 
						"UPDATE email_info ".
						"SET email_address = :primaryEmail ".
						"WHERE user_id = :userId AND priority = 1;";
					$updatePrimaryEmailQuery = $db->prepare($updatePrimaryEmailSQL);
					$updatePrimaryEmailQuery->bindParam(":primaryEmail", $primaryEmail, PDO::PARAM_STR);
					$updatePrimaryEmailQuery->bindParam(":userId", $userId, PDO::PARAM_STR);
					$updatePrimaryEmailQuery->execute();
					//echo 'User "'.$userName.'" primary email changed from "'.$originalPrimaryEmail.'" to "'.$primaryEmail.'".<br/>';
					$updateArray['email_address'] = $primaryEmail;
					$messageArray['bigfix_user_name'] = 'from "'.$originalPrimaryEmail.'" to "'.$primaryEmail.'"';
					array_push($tablesArray,'email_info');
					
					/*
					$checkIfPrimaryEmailAlreadyExistSQL = 
						"SELECT email_address, priority ".
						"FROM email_info ".
						"WHERE user_id = :userId AND priority <> 1;";
					$checkIfPrimaryEmailAlreadyExistQuery = $db->prepare($checkIfPrimaryEmailAlreadyExistSQL);
					$checkIf/PrimaryEmailAlreadyExistQuery->bindParam(":userId", $userId, PDO::PARAM_STR);
					$checkIfPrimaryEmailAlreadyExistQuery->execute();
					$checkIfPrimaryEmailAlreadyExistArray = $checkIfPrimaryEmailAlreadyExistQuery->fetch(PDO::FETCH_ASSOC);
					
					if (!$checkIfPrimaryEmailAlreadyExistArray) {
						$updatePrimaryEmailSQL = 
							"UPDATE email_info ".
							"SET email_address = :primaryEmail ".
							"WHERE user_id = :userId AND priority = 1;";
						$updatePrimaryEmailQuery = $db->prepare($updatePrimaryEmailSQL);
						$updatePrimaryEmailQuery->bindParam(":primaryEmail", $primaryEmail, PDO::PARAM_STR);
						$updatePrimaryEmailQuery->bindParam(":userId", $userId, PDO::PARAM_STR);
						//$updatePrimaryEmailQuery->execute();
						echo '';
					}
					else { 
						$fetchOldPrioritySQL = 
							"SELECT priority ".
							"FROM email_info ".
							"WHERE email_address = :primaryEmail;";
						$fetchOldPriorityQuery = $db->prepare($fetchOldPrioritySQL);
						$fetchOldPriorityQuery->bindParam(":primaryEmail", $primaryEmail, PDO::PARAM_STR);
						$fetchOldPriorityQuery->execute();
						$fetchOldPriorityArray = $fetchOldPriorityQuery->fetch(PDO::FETCH_ASSOC);
						
					}
					*/
				}
				
				if ($defaultSite != $originalDefaultSite && $defaultSite != null) {
					$updateDefaultSiteSQL = 
						"UPDATE user_defaults ".
						"SET default_site = :defaultSite ".
						"WHERE user_id = :userId;";
					$updateDefaultSiteQuery = $db->prepare($updateDefaultSiteSQL);
					$updateDefaultSiteQuery->bindParam(":defaultSite", $defaultSite, PDO::PARAM_STR);
					$updateDefaultSiteQuery->bindParam(":userId", $userId, PDO::PARAM_STR);
					$updateDefaultSiteQuery->execute();
					//echo 'Default Site updated from "'.$originalDefaultSite.'" to "'.$defaultSite.'".<br/>';
					$updateArray['default_site'] = $defaultSite;
					$messageArray['default_site'] = 'from "'.$originalDefaultSite.'" to "'.$defaultSite.'"';
					array_push($tablesArray,'user_defaults');
				}
			
				if ($defaultComputerGroup != $originalDefaultComputerGroup && $defaultComputerGroup != null) {
					$updateDefaultComputerGroupSQL = 
						"UPDATE user_defaults ".
						"SET default_computer_group = :defaultComputerGroup ".
						"WHERE user_id = :userId;";
					$updateDefaultComputerGroupQuery = $db->prepare($updateDefaultComputerGroupSQL);
					$updateDefaultComputerGroupQuery->bindParam(":defaultComputerGroup", $defaultComputerGroup, PDO::PARAM_STR);
					$updateDefaultComputerGroupQuery->bindParam(":userId", $userId, PDO::PARAM_STR);
					$updateDefaultComputerGroupQuery->execute();
					//echo 'Default Computer Group updated from "'.$originalDefaultComputerGroup.'" to "'.$defaultComputerGroup.'".<br/>';
					$updateArray['default_computer_group'] = $defaultComputerGroup;
					$messageArray['default_computer_group'] = 'from "'.$originalDefaultComputerGroup.'" to "'.$defaultComputerGroup.'"';
					array_push($tablesArray,'user_defaults');
				}
				
				$uniqueTablesArray = array_unique($tablesArray);
				$uniqueTablesString = implode(', ', $uniqueTablesArray);
				
				$updateUserXML = $actionsXML->addChild('Action');
				$updateUserXML->addAttribute('type', 'Update User Info');
				if ($updateArray != array()) {
					$updateUserXML->addChild('Status', "Success");
					$updateUserXML->addChild('Details', 'Altered data for user "'.$userName.'" in database');
					$updatedUserInfoXML = $updateUserXML->addChild('Changed_User_Info');
					$updatedUserInfoXML->addAttribute('tables', $uniqueTablesString);
					foreach($updateArray as $columnName => $alteredColumn) {
						$updatedUserInfoXML->addChild($columnName, $alteredColumn);
					}
					$updateUserXML->addChild('Count', sizeOf($uniqueTablesArray));
				}
				else {
					$updateUserXML->addChild('Status', "N/A");
					$updateUserXML->addChild('Details', 'No Changes Made.');
				}
				
				if ($messageArray != array()) {
					$message = 'Changes made to user account "'.$userName.' ('.$userId.') = ';
					foreach($messageArray as $messageIndex => $messageRow) {
						$message .= $messageIndex.' changed '.$messageRow.', ';
					}
					$message = substr($message, 0 , -2);
					$message .= '.';
					
					$databaseChangeLogSQL = 
						"INSERT INTO database_change_log ".
						"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables) ".
						"VALUES (:updater, :timestamp, 'Change User Info', :message, 'users, console_to_portal, email_info, user_defaults');";
					$databaseChangeLogQuery = $db->prepare($databaseChangeLogSQL);
					$databaseChangeLogQuery->bindParam(":updater", $updater, PDO::PARAM_STR);
					$databaseChangeLogQuery->bindParam(":timestamp", $timestamp, PDO::PARAM_STR);
					$databaseChangeLogQuery->bindParam(":message", $message, PDO::PARAM_STR);
					$databaseChangeLogQuery->execute();
					//echo "Transaction Logged.<br/>";
					
					$changeLogXML = $resultXML->addChild('Change_Log');
					$changeLogXML->addChild('Status', 'Transaction Logged');
					$changeDetailsXML = $changeLogXML->addChild('Details');
					$changeDetailsXML->addChild('timestamp', $timestamp);
					$changeDetailsXML->addChild('type_of_change', 'Change User Info');
					$changeDetailsXML->addChild('action_taken', $message);
					$changeDetailsXML->addChild('affected_tables', 'users, console_to_portal, email_info, user_defaults');
					
					$changeLogXML->addChild('Count', 1);
				}
				
				$end = microtime(true);
				
				$evaluationXML = $queryXML->addChild('Evaluation');
				$evaluationXML->addChild('Time', round((($end - $start) * 1000), 3).'ms');
				$evaluationXML->addChild('Total_Number_of_Altered_Rows', sizeOf($uniqueTablesArray));
				
			$db->commit();
		}
		catch (\PDOException $e) {
			$errorXML = $queryXML->addChild('Error');
			if ($fetchOriginalUserQuery->errorCode() != 0) {
				$fetchOriginalUserInfoXML = $actionsXML->addChild('Action');
				$fetchOriginalUserInfoXML->addAttribute('type', 'Fetch Original User Info');
				$fetchOriginalUserInfoXML->addChild('Status', "Failure");
				$errorDescription = 'Error occured fetching original User information.';
				$fetchOriginalUserInfoXML->addChild('Details', $errorDescription);
				
			}
			else if ($fetchOriginalBigFixUserNameQuery->errorCode() != 0) {
				$fetchOriginalUserInfoXML = $actionsXML->addChild('Action');
				$fetchOriginalUserInfoXML->addAttribute('type', 'Fetch Original User Info');
				$fetchOriginalUserInfoXML->addChild('Status', "Failure");
				$errorDescription = 'Error occured fetching original BigFix Login information.';
				$fetchOriginalUserInfoXML->addChild('Details', $errorDescription);
			}
			else if ($fetchOriginalPrimaryEmailQuery->errorCode() != 0) {
				$fetchOriginalUserInfoXML = $actionsXML->addChild('Action');
				$fetchOriginalUserInfoXML->addAttribute('type', 'Fetch Original User Info');
				$fetchOriginalUserInfoXML->addChild('Status', "Failure");
				$errorDescription = 'Error occured fetching original Email Information';
				//echo 'Email Error.';
				$fetchOriginalUserInfoXML->addChild('Details', $errorDescription);
			}
			else if ($fetchOriginalDefaultsQuery->errorCode() != 0) {
				$fetchOriginalUserInfoXML = $actionsXML->addChild('Action');
				$fetchOriginalUserInfoXML->addAttribute('type', 'Fetch Original User Info');
				$fetchOriginalUserInfoXML->addChild('Status', "Failure");
				$errorDescription = 'Error occured fetching original User Defaults information.';
				$fetchOriginalUserInfoXML->addChild('Details', $errorDescription);
				//echo 'Failure to set BigFix user.';
			}
			else if ((isset($updatePasswordQuery) && $updatePasswordQuery->errorCode() != 0) || 
					 (isset($updateWelcomeNameQuery) && $updateWelcomeNameQuery->errorCode() != 0) || 
					 (isset($updateAdminStatusQuery) && $updateAdminStatusQuery->errorCode() != 0) || 
					 (isset($updateBigFixUserNameQuery) && $updateBigFixUserNameQuery->errorCode() != 0) || 
					 (isset($updatePrimaryEmailQuery) && $updatePrimaryEmailQuery->errorCode() != 0) || 
					 (isset($updateDefaultSiteQuery) && $updateDefaultSiteQuery->errorCode() != 0) || 
					 (isset($updateDefaultComputerGroupQuery) && $updateDefaultComputerGroupQuery->errorCode() != 0)
			) {
				$updateUserXML = $actionsXML->addChild('Action');
				$updateUserXML->addAttribute('type', 'Update User Info');
				$updateUserXML->addChild('Status', "Failure");
				if ($updatePasswordQuery->errorCode() != 0) {
					$errorDescription = 'Failure to update User Password.';
					//echo 'Failure to log Transaction.';
				}
				else if ($updateWelcomeNameQuery->errorCode() != 0) {
					$errorDescription = 'Failure to update User First Name.';
					//echo 'Email Address cannot be empty.';
				}
				else if (isset($updateAdminStatusQuery) && $updateAdminStatusQuery->errorCode() != 0) {
					$errorDescription = 'Failure to update User Admin Status.';
					//echo 'Email Address "'.$primaryEmail.'" is already being used.  Enter a different Email Address.';
				}
				else if ($updateBigFixUserNameQuery->errorCode() != 0) {
					$errorDescription = 'Failure to update BigFix Username.';
				}
				else if ($updatePrimaryEmailQuery->errorCode() != 0) {
					if ($updatePrimaryEmailQuery->errorCode() == 23505) { //Unique Key Violation
						$errorDescription = 'Email Address "'.$primaryEmail.'" is already being used.  Enter a different Email Address.';
					}
					else {
						$errorDescription = 'Failure to update Primary Email Address.';
					}
				}
				else if ($updateDefaultSiteQuery->errorCode() != 0) {
					$errorDescription = 'Failure to update Default Site.';
				}
				else if ($updateDefaultComputerGroupQuery->errorCode() != 0) {
					$errorDescription = 'Failure to update Daefault Computer Group';
				}
				$updateUserXML->addChild('Details', $errorDescription);
			}
			else if ($databaseChangeLogQuery->errorCode() != 0) {
				$changeLogXML = $resultXML->addChild('Change_Log');
				$changeLogXML->addChild('Status', 'Failed to log Transaction');
				$errorDescription = 'Unable to Log Transaction due to Error, entire transaction has been Undone.';
				$changeLogXML->addChild('Details', );
			}
			
			$errorCode = $e->getCode();
			$errorMessage = $e->getMessage();
			
			$errorXML->addChild('Error_Code', $errorCode);
			$errorXML->addChild('Details', $errorMessage);
			
			$db->rollBack();
			//throw $e;
			
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
			
			$db->rollBack();
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