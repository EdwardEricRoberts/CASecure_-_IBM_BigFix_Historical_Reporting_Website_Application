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
				$metaXML->addChild('Name', "Fetch Computer Goups List");
				$metaXML->addChild('Description', "This query returns all Computer Groups accessable to the current user.");
				$paramsXML = $metaXML->addChild('Parameters');
				$param1XML = $paramsXML->addChild('Parameter');
				$param1XML->addChild('Name', 'Current User ID');
				$param1XML->addChild('URL', 'cid');
				$param1XML->addChild('Value', $currentUser);
				$param1XML->addChild('Description', 'The User ID of the current User.');
				$resultXML = $queryXML->addChild('Result');
				
				$fetchBigFixUserNameSQL = 
					"SELECT bigfix_user_name ".
					"FROM console_to_portal ".
					"WHERE user_id = :currentUser;";
				$fetchBigFixUserNameQuery = $db->prepare($fetchBigFixUserNameSQL);
				$fetchBigFixUserNameQuery->bindParam(":currentUser", $currentUser, PDO::PARAM_STR);
				$start = microtime(true);
				$fetchBigFixUserNameQuery->execute();
				$bigFixUserName = ($fetchBigFixUserNameQuery->fetch(PDO::FETCH_ASSOC))['bigfix_user_name'];
				//echo $bigFixUserName."<br/><br/>";
				
				$fetchAccessableSitesListSQL = 
					"SELECT site_name ".
					"FROM bigfix_site_access ".
					"WHERE bigfix_user_name = :bigFixUserName;";
				$fetchAccessableSitesListQuery = $db->prepare($fetchAccessableSitesListSQL);
				$fetchAccessableSitesListQuery->bindParam(":bigFixUserName", $bigFixUserName, PDO::PARAM_STR);
				$fetchAccessableSitesListQuery->execute();
				$accessableSitesArray = $fetchAccessableSitesListQuery->fetchAll(PDO::FETCH_ASSOC);
				$accessableSites = array();
				foreach($accessableSitesArray as $key => $siteName) {
					$accessableSites[] = $siteName['site_name'];
				}
				//print_r($accessableSites);
				//echo "<br/><br/>";
				
				if (!in_array('ActionSite', $accessableSites)) {
					$accessableSites[sizeOf($accessableSites)] = 'ActionSite';
				}
				//print_r($accessableSites);
				//echo "<br/><br/>";
				
				$fetchComputerGroupsSQL =
					"SELECT computer_group_name, computer_group_type, parent_site ".
					"FROM computer_groups ".
					//"WHERE computer_group_name = :search1 ".
					"ORDER BY parent_site, computer_group_name;";
				$fetchComputerGroupsQuery = $db->query($fetchComputerGroupsSQL);	
				$end = microtime(true);
				$fetchComputerGroupsArray = $fetchComputerGroupsQuery->fetchAll(PDO::FETCH_ASSOC);
				//print_r($searchComputerGroupsArray);
				//echo "<br/><br/>";
				
				$accessableComputerGroups = array();
				foreach($fetchComputerGroupsArray as $computerGroup) {
					if(in_array($computerGroup['parent_site'], $accessableSites)) {
						$accessableComputerGroups[] = $computerGroup;
					}
				}
				//print_r($accessableComputerGroups);
				//echo "<br/><br/>";
				
				$j = 0;
				while (isset($accessableComputerGroups[$j])) {//$searchSitesQuery->fetch(PDO::FETCH_ASSOC)) {
					$row = $accessableComputerGroups[$j];
					$tuple = $resultXML->addChild('computer_group'); //Tuple
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
			//$errorXML->addChild('Code', $fetchAllAlertsQuery->errorCode());
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