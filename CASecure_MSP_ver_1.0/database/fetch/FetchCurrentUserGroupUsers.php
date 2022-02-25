<?php
	
	header('Content-type: application/xml');
	
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
				$metaXML->addChild('Name', "Fetch Current User Group Users");
				$metaXML->addChild('Description', "This query returns the Usernames of Users that are members of the of the currently selected User Group.");
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
					"SELECT gu.user_id, u.user_name, ".
						"CASE WHEN gu.group_admin = true THEN 'true' ELSE 'false' END AS group_admin, ".
						"gu.user_group_id, g.user_group_name ".
					"FROM user_group_users gu, users u, user_groups g ".
					"WHERE gu.user_group_id = :fetchUserGroup AND u.user_id = gu.user_id AND g.user_group_id = gu.user_group_id;";
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