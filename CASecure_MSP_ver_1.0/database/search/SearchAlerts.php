<?php
	
	header('Content-type: application/xml');
	
	$currentUser = $_GET['cid'];
	$searchText = $_GET['search'];
	                                         
	$search = '%'.$searchText.'%';
	$searchLower = '%'.strtolower($searchText).'%';
	$searchUpper = '%'.strtoupper($searchText).'%';
	
	//echo $search."<br/>";
	
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
				$metaXML->addChild('Name', "Search Alerts");
				$metaXML->addChild('Description', "This query searches for Alerts accessable to the Current User that match a User defined Search term.");
				$paramsXML = $metaXML->addChild('Parameters');
				$param1XML = $paramsXML->addChild('Parameter');
				$param1XML->addChild('Name', 'Current User ID');
				$param1XML->addChild('URL', 'cid');
				$param1XML->addChild('Value', $currentUser);
				$param1XML->addChild('Description', 'The User ID of the current User');
				$param2XML = $paramsXML->addChild('Parameter');
				$param2XML->addChild('Name', 'Search Term');
				$param2XML->addChild('URL', 'search');
				$param2XML->addChild('Value',$searchText);
				$param2XML->addChild('Description', 'The text submitted by the User for the search');
				$resultXML = $queryXML->addChild('Result');
				
				$searchAlertsSQL =
					"SELECT a.alert_id, a.user_group_id, g.user_group_name, a.issued_by_id, u.user_name AS issued_by_user_name, title, a.message, a.timestamp, a.expiration, ".
						"CASE ".
							"WHEN a.active = true THEN 'active' ".
							"WHEN a.active = false THEN 'inactive' ".
						"END AS active ".
					"FROM alerts a, user_groups g, users u ".
					"WHERE CAST(alert_id AS char) LIKE :searchNum AND a.user_group_id = g.user_group_id AND a.issued_by_id = u.user_id ".
					"UNION ".
					"SELECT a.alert_id, a.user_group_id, g.user_group_name, a.issued_by_id, u.user_name AS issued_by_user_name, title, a.message, a.timestamp, a.expiration, ".
						"CASE ".
							"WHEN a.active = true THEN 'active' ".
							"WHEN a.active = false THEN 'inactive' ".
						"END AS active ".
					"FROM alerts a, user_groups g, users u ".
					"WHERE title LIKE :search1 AND a.user_group_id = g.user_group_id AND a.issued_by_id = u.user_id ".
					"UNION ".
					"SELECT a.alert_id, a.user_group_id, g.user_group_name, a.issued_by_id, u.user_name AS issued_by_user_name, title, a.message, a.timestamp, a.expiration, ".
						"CASE ".
							"WHEN a.active = true THEN 'active' ".
							"WHEN a.active = false THEN 'inactive' ".
						"END AS active ".
					"FROM alerts a, user_groups g, users u ".
					"WHERE message LIKE :search2 AND a.user_group_id = g.user_group_id AND a.issued_by_id = u.user_id ".
					"UNION ".
					"SELECT a.alert_id, a.user_group_id, g.user_group_name, a.issued_by_id, u.user_name AS issued_by_user_name, title, a.message, a.timestamp, a.expiration, ".
						"CASE ".
							"WHEN a.active = true THEN 'active' ".
							"WHEN a.active = false THEN 'inactive' ".
						"END AS active ".
					"FROM alerts a, user_groups g, users u ".
					"WHERE title LIKE :searchLower1 AND a.user_group_id = g.user_group_id AND a.issued_by_id = u.user_id ".
					"UNION ".
					"SELECT a.alert_id, a.user_group_id, g.user_group_name, a.issued_by_id, u.user_name AS issued_by_user_name, title, a.message, a.timestamp, a.expiration, ".
						"CASE ".
							"WHEN a.active = true THEN 'active' ".
							"WHEN a.active = false THEN 'inactive' ".
						"END AS active ".
					"FROM alerts a, user_groups g, users u ".
					"WHERE message LIKE :searchLower2 AND a.user_group_id = g.user_group_id AND a.issued_by_id = u.user_id ".
					"UNION ".
					"SELECT a.alert_id, a.user_group_id, g.user_group_name, a.issued_by_id, u.user_name AS issued_by_user_name, title, a.message, a.timestamp, a.expiration, ".
						"CASE ".
							"WHEN a.active = true THEN 'active' ".
							"WHEN a.active = false THEN 'inactive' ".
						"END AS active ".
					"FROM alerts a, user_groups g, users u ".
					"WHERE title LIKE :searchUpper1 AND a.user_group_id = g.user_group_id AND a.issued_by_id = u.user_id ".
					"UNION ".
					"SELECT a.alert_id, a.user_group_id, g.user_group_name, a.issued_by_id, u.user_name AS issued_by_user_name, title, a.message, a.timestamp, a.expiration, ".
						"CASE ".
							"WHEN a.active = true THEN 'active' ".
							"WHEN a.active = false THEN 'inactive' ".
						"END AS active ".
					"FROM alerts a, user_groups g, users u ".
					"WHERE message LIKE :searchUpper2 AND a.user_group_id = g.user_group_id AND a.issued_by_id = u.user_id ".
					"ORDER BY title, message;";
				$searchAlertsQuery = $db->prepare($searchAlertsSQL);	
				$searchAlertsQuery->bindParam(':searchNum', $search, PDO::PARAM_STR);
				$searchAlertsQuery->bindParam(':search1', $search, PDO::PARAM_STR);
				$searchAlertsQuery->bindParam(':search2', $search, PDO::PARAM_STR);
				$searchAlertsQuery->bindParam(':searchLower1', $searchLower, PDO::PARAM_STR);
				$searchAlertsQuery->bindParam(':searchLower2', $searchLower, PDO::PARAM_STR);
				$searchAlertsQuery->bindParam(':searchUpper1', $searchUpper, PDO::PARAM_STR);
				$searchAlertsQuery->bindParam(':searchUpper2', $searchUpper, PDO::PARAM_STR);
				$start = microtime(true);
				$searchAlertsQuery->execute();
				$end = microtime(true);
				
				$j = 0;
				while ($row = $searchAlertsQuery->fetch(PDO::FETCH_ASSOC)) {
					$tuple = $resultXML->addChild('alert'); //Tuple
					foreach ($row as $key => $value) {
						$tuple->addChild($key, $value); //'Answer'
					}
					$j++;
				}
				$evaluationXML = $queryXML->addChild('Evaluation');
				$evaluationXML->addChild('Time', round((($end - $start) * 1000), 3).'ms');
				$evaluationXML->addChild('Number_of_Results', $j);
				
			$db->commit();
		}
		catch (\PDOException $e) {
			$errorXML = $queryXML->addChild('Error');
			$errorXML->addChild('Code', $searchAlertsQuery->errorCode());
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