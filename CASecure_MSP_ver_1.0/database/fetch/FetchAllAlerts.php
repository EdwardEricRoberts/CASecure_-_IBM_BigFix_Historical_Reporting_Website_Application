<?php
	
	header('Content-type: application/xml');
	
	$currentUser = $_GET['cid'];
	
	$db_host = "localhost";
	$db_name = "CASecure2";
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
				$metaXML->addChild('Name', "Fetch All Alerts");
				$metaXML->addChild('Description', "This query returns all Alerts both active and inactive connected to the current user.");
				$paramsXML = $metaXML->addChild('Parameters');
				$param1XML = $paramsXML->addChild('Parameter');
				$param1XML->addChild('Name', 'Current User ID');
				$param1XML->addChild('URL', 'cid');
				$param1XML->addChild('Value', $currentUser);
				$param1XML->addChild('Description', 'The User ID of the current User.');
				$resultXML = $queryXML->addChild('Result');
				
				$fetchAllAlertsSQL =
					"SELECT a.alert_id, a.user_group_id, g.user_group_name, a.issued_by_id, u.user_name AS issued_by_user_name, a.title, a.message, a.timestamp, a.expiration, ".
						"CASE ".
							"WHEN a.active = true THEN 'active' ".
							"WHEN a.active = false THEN 'inactive' ".
						"END AS active ".
					"FROM alerts a, user_groups g, users u ".
					"WHERE g.user_group_id = a.user_group_id AND u.user_id = a.issued_by_id AND ".
						"a.user_group_id IN ( ".
							"SELECT user_group_id ".
							"FROM user_group_users ".
							"WHERE user_id = :currentUser ".
						") ".
					"ORDER BY a.timestamp DESC, active ASC;";
				$fetchAllAlertsQuery = $db->prepare($fetchAllAlertsSQL);	
				$fetchAllAlertsQuery->bindParam(':currentUser', $currentUser, PDO::PARAM_STR);
				$start = microtime(true);
				$fetchAllAlertsQuery->execute();
				$end = microtime(true);
				
				$j = 0;
				while ($row = $fetchAllAlertsQuery->fetch(PDO::FETCH_ASSOC)) {
					$tuple = $resultXML->addChild('alert'); //Tuple
					foreach ($row as $key => $value) {
						$tuple->addChild($key, $value); //'Answer'
					}
					$j++;
				}
				$evaluationXML = $queryXML->addChild('Evaluation');
				$evaluationXML->addChild('Time', round((($end - $start) * 1000), 3).'ms');
				$evaluationXML->addChild('Number_of_Results', $j);
				//$queryXML->addChild('SQL', $fetchAllAlertsSQL);
				
			$db->commit();
		}
		catch (\PDOException $e) {
			$errorXML = $queryXML->addChild('Error');
			$errorXML->addChild('Code', $fetchAllAlertsQuery->errorCode());
			$errorXML->addChild('Code', $e->getCode());
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