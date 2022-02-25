<?php
	
	header('Content-type: application/xml');
	
	$timestamp = date("Y-m-d H:i:sO");
	
	$fileName = basename(__FILE__, '.php').'.php';
	$fileDirectory = getcwd();
	$requestURI = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http")."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	
	$currentUser = $_GET['cid'];
	$fetchUserGroup = $_GET['gid'];
	
	$db_host = "localhost";
	$db_name = "CASecure1";
	$db_username = "postgres";
	$db_password = "abc.123";
	
	$xml= new SimpleXMLElement('<PDO/>'); 
	$connectionXML = $xml->addChild('Connection');
	try {
		$connectResultXML = $connectionXML->addChild('Result');
		
		$db = new PDO('pgsql:host='.$db_host.';dbname='.$db_name,$db_username,$db_password);
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
				$metaXML->addChild('Name', "Fetch Users Not In Curent User Group");
				$metaXML->addChild('Description', "This query returns the Usernames of Users that are not members of the of the currently selected User Group.");
				$paramsXML = $metaXML->addChild('Parameters');
				$param1XML = $paramsXML->addChild('Parameter');
				$param1XML->addChild('Name', 'Current User ID');
				$param1XML->addChild('URL', 'cid');
				$param1XML->addChild('Value', $currentUser);
				$param1XML->addChild('Description', 'The User ID of the current User.');
				$param2XML = $paramsXML->addChild('Parameter');
				$param2XML->addChild('Name', 'User Group ID');
				$param2XML->addChild('URL', 'gid');
				$param2XML->addChild('Value', $fetchUserGroup);
				$param2XML->addChild('Description', 'The User Group ID for the currently selected User Group.');
				$resultXML = $queryXML->addChild('Result');
				
				$fetchUserGroupUsersSQL = 
					"SELECT user_id, user_name ".
					"FROM users ".
					"WHERE user_id NOT IN(".
						"SELECT u.user_id ".
						"FROM users u, user_group_users g ".
						"WHERE g.user_group_id = :fetchUserGroup AND u.user_id = g.user_id ".
					") ".
					"ORDER BY user_name;";
				$fetchUserGroupUsersQuery = $db->prepare($fetchUserGroupUsersSQL);
				$fetchUserGroupUsersQuery->bindParam(":fetchUserGroup", $fetchUserGroup, PDO::PARAM_STR);
				$start = microtime(true);
				$fetchUserGroupUsersQuery->execute();
				$end = microtime(true);
				$userGroupUsersArray = $fetchUserGroupUsersQuery->fetchAll(PDO::FETCH_ASSOC);
				//echo $fetchUserGroupUsersSQL."<br/>";
				//print_r($userGroupUsersArray);
				$j = 0;
				while (isset($userGroupUsersArray[$j])) {//$searchSitesQuery->fetch(PDO::FETCH_ASSOC)) {
					$row = $userGroupUsersArray[$j];
					$tuple = $resultXML->addChild('user'); //Tuple
					foreach ($row as $key => $value) {
						$tuple->addChild($key, $value); //'Answer'
					}
					$j++;
				}
				
				$evaluationXML = $queryXML->addChild('Evaluation');
				$evaluationXML->addChild('Time', round((($end - $start) * 1000), 3).'ms');
				$evaluationXML->addChild('Number_of_Results', $j);
				//$queryXML->addChild('SQL', $fetchUserGroupUsersSQL);
				
			$db->commit();
		}
		catch (\PDOException $e) {
			$errorXML = $queryXML->addChild('Error');
			$errorXML->addChild('Code', $fetchUserGroupUsersQuery->errorCode());
			$errorXML->addChild('Code', $e->getCode());//$fetchAlertsByUserGroupQuery->errorCode());
			$errorXML->addChild('Details', $e->getMessage());
			$db->rollback();
		}
	}
	catch(PDOException $e) {
		$connectResultXML->addChild('Status', 'Failure');
		$connectResultXML->addChild('Message', "Failed to Connect to Database.  Please Email Site Administrator.");
		$connectResultXML->addChild('Host', $db_host);
		$connectResultXML->addChild('Database', $db_name);
		
		$connectErrorXML = $connectionXML->addChild('Error');
		$connectErrorXML->addChild('Code', $e->getCode());
		$connectErrorXML->addChild('Details', $e->getMessage());
		
		$errorDescription = "Failed to Connect to Database.";
		
		$errorArray = array(
			"user_id" => $currentUser,
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
	catch(Exception $e) {
		$connectResultXML->addChild('Status', 'Failure');
		$connectResultXML->addChild('Message', "An Unexpected Error Occured.  Please try again. If Issue Persists, Please Email Site Administrator.");
		$connectResultXML->addChild('Host', $db_host);
		$connectResultXML->addChild('Database', $db_name);
		
		$connectErrorXML = $connectionXML->addChild('Error');
		$connectErrorXML->addChild('Error_Code', $e->getCode());
		//$connectErrorXML->addChild('Message', "Failed to Connect to Database.  Please Email Site Administrator.");
		$connectErrorXML->addChild('Details', $e->getMessage());
	}
	 
	echo $xml->asXML();
	
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