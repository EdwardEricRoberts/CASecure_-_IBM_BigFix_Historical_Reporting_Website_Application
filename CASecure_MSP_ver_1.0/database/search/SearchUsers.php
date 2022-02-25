<?php
	
	header('Content-type: application/xml');
	
	$xml= new SimpleXMLElement('<PDO/>'); 
	
	try {
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
					$metaXML->addChild('Name', "Search Users");
					$metaXML->addChild('Description', "This query searches for USer Groups that match a User defined Search term.");
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
					
					$searchUsersSQL =
						"SELECT user_id, user_name, welcome_name, timestamp, ".
							"CASE ".
								"WHEN is_admin = true THEN 'true' ".
								"WHEN is_admin = false THEN 'false' ".
							"END AS is_admin ".
						"FROM users ".
						"WHERE CAST(user_id AS char) LIKE :searchNum ".
						"UNION ".
						"SELECT user_id, user_name, welcome_name, timestamp, ".
							"CASE ".
								"WHEN is_admin = true THEN 'true' ".
								"WHEN is_admin = false THEN 'false' ".
							"END AS is_admin ".
						"FROM users ".
						"WHERE user_name LIKE :search1 ".
						"UNION ".
						"SELECT user_id, user_name, welcome_name, timestamp, ".
							"CASE ".
								"WHEN is_admin = true THEN 'true' ".
								"WHEN is_admin = false THEN 'false' ".
							"END AS is_admin ".
						"FROM users ".
						"WHERE welcome_name LIKE :search2 ".
						"UNION ".
						"SELECT user_id, user_name, welcome_name, timestamp, ".
							"CASE ".
								"WHEN is_admin = true THEN 'true' ".
								"WHEN is_admin = false THEN 'false' ".
							"END AS is_admin ".
						"FROM users ".
						"WHERE user_name LIKE :searchLower1 ".
						"UNION ".
						"SELECT user_id, user_name, welcome_name, timestamp, ".
							"CASE ".
								"WHEN is_admin = true THEN 'true' ".
								"WHEN is_admin = false THEN 'false' ".
							"END AS is_admin ".
						"FROM users ".
						"WHERE welcome_name LIKE :searchLower2 ".
						"UNION ".
						"SELECT user_id, user_name, welcome_name, timestamp, ".
							"CASE ".
								"WHEN is_admin = true THEN 'true' ".
								"WHEN is_admin = false THEN 'false' ".
							"END AS is_admin ".
						"FROM users ".
						"WHERE user_name LIKE :searchUpper1 ".
						"UNION ".
						"SELECT user_id, user_name, welcome_name, timestamp, ".
							"CASE ".
								"WHEN is_admin = true THEN 'true' ".
								"WHEN is_admin = false THEN 'false' ".
							"END AS is_admin ".
						"FROM users ".
						"WHERE welcome_name LIKE :searchUpper2 ".
						"ORDER BY user_name, welcome_name;";
					$searchUsersQuery = $db->prepare($searchUsersSQL);	
					$searchUsersQuery->bindParam(':searchNum', $search, PDO::PARAM_STR);
					$searchUsersQuery->bindParam(':search1', $search, PDO::PARAM_STR);
					$searchUsersQuery->bindParam(':search2', $search, PDO::PARAM_STR);
					$searchUsersQuery->bindParam(':searchLower1', $searchLower, PDO::PARAM_STR);
					$searchUsersQuery->bindParam(':searchLower2', $searchLower, PDO::PARAM_STR);
					$searchUsersQuery->bindParam(':searchUpper1', $searchUpper, PDO::PARAM_STR);
					$searchUsersQuery->bindParam(':searchUpper2', $searchUpper, PDO::PARAM_STR);
					$start = microtime(true);
					$searchUsersQuery->execute();
					$end = microtime(true);
					
					$j = 0;
					while ($row = $searchUsersQuery->fetch(PDO::FETCH_ASSOC)) {
						$tuple = $resultXML->addChild('user'); //Tuple
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
				$errorXML->addChild('Code', $searchUsersQuery->errorCode());
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
	
	}
	catch (Exception $e) {
		$inputErrorXML = $xml->addChild('Error');
		$inputErrorXML->addChild('Code', $e->getCode());
		$inputErrorXML->addChild('Details', $e->getMessage());
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