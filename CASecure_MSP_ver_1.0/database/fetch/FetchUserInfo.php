<?php
	
	header('Content-type: application/xml');
	
	$currentUser = $_GET['cid'];
	$fetchUser = $_GET['uid'];
	
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
				$metaXML->addChild('Name', "Fetch User Info");
				$metaXML->addChild('Description', "This query returns the account information for the currently selected User.");
				$paramsXML = $metaXML->addChild('Parameters');
				$param1XML = $paramsXML->addChild('Parameter');
				$param1XML->addChild('Name', 'Current User ID');
				$param1XML->addChild('URL', 'cid');
				$param1XML->addChild('Value', $currentUser);
				$param1XML->addChild('Description', 'The User ID of the current User.');
				$param1XML = $paramsXML->addChild('Parameter');
				$param1XML->addChild('Name', 'Fetch User ID');
				$param1XML->addChild('URL', 'uid');
				$param1XML->addChild('Value', $fetchUser);
				$param1XML->addChild('Description', 'The User ID of the currently selcted User.');
				$resultXML = $queryXML->addChild('Result');
				
				$fetchUserInfoSQL = 
					"SELECT u.user_name, u.password, u.welcome_name, ".
						"CASE WHEN u.is_admin = TRUE THEN 'true' ELSE 'false' END AS is_admin, ".
						"c.bigfix_user_name, d.default_site, d.default_computer_group ".
					"FROM users u, console_to_portal c, user_defaults d ".
					"WHERE u.user_id = :fetchUser AND c.user_id = u.user_id AND d.user_id = u.user_id;";
				$fetchUserInfoQuery = $db->prepare($fetchUserInfoSQL);
				$fetchUserInfoQuery->bindParam(":fetchUser", $fetchUser, PDO::PARAM_STR);
				$start = microtime(true);
				$fetchUserInfoQuery->execute();
				$end = microtime(true);
				$userInfoArray = $fetchUserInfoQuery->fetchAll(PDO::FETCH_ASSOC);
				
				//print_r($userInfoArray);
				$j = 0;
				while (isset($userInfoArray[$j])) {//$searchSitesQuery->fetch(PDO::FETCH_ASSOC)) {
					$row = $userInfoArray[$j];
					$tuple = $resultXML->addChild('user'); //Tuple
					foreach ($row as $key => $value) {
						$tuple->addChild($key, $value); //'Answer'
					}
					$j++;
				}
				$evaluationXML = $queryXML->addChild('Evaluation');
				$evaluationXML->addChild('Time', round((($end - $start) * 1000), 3).'ms');
				$evaluationXML->addChild('Number_of_Results', $j);
		 		//$queryXML->addChild('SQL', $fetchUserInfoSQL);
				
			$db->commit();
		}
		catch (\PDOException $e) {
			$errorXML = $queryXML->addChild('Error');
			$errorXML->addChild('Code', $fetchUserInfoQuery->errorCode());
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