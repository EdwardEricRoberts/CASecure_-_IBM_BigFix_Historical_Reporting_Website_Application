<?php
	
	//header('Content-type: application/xml');
	
	$timestamp = date("Y-m-d H:i:sO");
	//echo "\nTime = ".$timestamp."\n";//"<br />";
	
	//$userName = implode(" ", array_slice($argv, 1, 1));
	//$password = implode(" ", array_slice($argv, 2, 1));
	//$server = implode(" ", array_slice($argv, 3, 1)); // Must be entered with periods "."s instead of "%25"s
	
	//$userName = $_GET['user'];
	//$password = $_GET['pass'];
	//$server = $_GET['serv'];
	
	$currentUserID = $_GET['cid'];
	
	//$db_host = implode(" ", array_slice($argv, 4, 1));
	//$db_name = implode(" ", array_slice($argv, 5, 1));
	//$db_username = implode(" ", array_slice($argv, 6, 1));
	//$db_password = implode(" ", array_slice($argv, 7, 1));
	
	$db_host = "localhost";
	$db_name = "CASecure2";
	$db_username = "postgres";
	$db_password = "abc.123";
	
	// Run the following command from the Command Prompt to run this file manually
	//php C:\Bitnami\wappstack-7.1.18-0\apache2\htdocs\CASecure_MSP_ver_1.0\database\MicrosoftPatchComplianceSummaryData.php eer AllieCat5 bigfix.internal.cassevern.com localhost postgres postgres abc.123
	
	
	$xml= new SimpleXMLElement('<PDO/>'); 
	$connectionXML = $xml->addChild('Connection');
	try {
		$connectResultXML = $connectionXML->addChild('Result');
		
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
				$metaXML->addChild('Name', "Create User");
				$metaXML->addChild('Description', "This creates a new User Account.");
				$paramsXML = $metaXML->addChild('Parameters');
				$param1XML = $paramsXML->addChild('Parameter');
				$param1XML->addChild('Name', 'Current User ID');
				$param1XML->addChild('URL', 'cid');
				$param1XML->addChild('Value', $currentUserID);
				$param1XML->addChild('Description', 'The User ID of the current User');
				$resultXML = $queryXML->addChild('Result');
				
				$start = microtime(true);
				
				$actionsXML = $resultXML->addChild('Actions');
				
				$cgSQL = 
					"SELECT computer_group_name ".
					"FROM computer_groups;";
				$cgQuery = $db->query($cgSQL);
				$cgArray = $cgQuery->fetchAll(PDO::FETCH_COLUMN, 0);
				print_r($cgArray);
				echo "<br/><br/>";
				
				$siteSQL = 
					"SELECT site_name ".
					"FROM sites ".
					"WHERE (site_type = 'external' OR site_type = 'master');";
				$siteQuery = $db->query($siteSQL);
				$siteArray = $siteQuery->fetchAll(PDO::FETCH_COLUMN, 0);
				print_r($siteArray);
				echo "<br/><br/>";
				
				$bigFixLoginFetchSQL = 
					"SELECT (bigfix_user_name, bigfix_password, bigfix_server) ".
					"FROM bigfix_logins ".
					"WHERE bigfix_user_name IN (".
						"SELECT bigfix_user_name ".
						"FROM console_to_portal ".
						"WHERE user_id = :currentUserID".
					");";
				$bigFixLoginFetchQuery = $db->prepare($bigFixLoginFetchSQL);
				$bigFixLoginFetchQuery->bindParam(':currentUserID', $currentUserID, PDO::PARAM_STR);
				$bigFixLoginFetchQuery->execute();
				$row = $bigFixLoginFetchQuery->fetch(PDO::FETCH_NUM);
				//print_r($row);
				
				$bigFixLogin = explode(",", substr($row[0], 1, -1));
				$bigfixCurrentUser = $bigFixLogin[0];
				$bigFixCurrentPassword = $bigFixLogin[1];
				$bigFixCurrentServer = $bigFixLogin[2];
				
				$logData = array();
				$siteDivisionsArray = array_chunk($siteArray, 10);
				
				print_r($siteDivisionsArray);
				//echo "<br/>";
			/*	
			//
				foreach($siteDivisionsArray as $siteGroupNum => $siteGroup) {
					
					$siteGroupString = "[";
					foreach($siteGroup as $siteNum => $siteName) {
						if ($siteNum != 0) {
							$siteGroupString .= ",";
						}
						$siteGroupString .= $siteName;
						
					}
					$siteGroupString .= "]";
					//echo $siteGroupString."<br/>";
				//	
					foreach($cgArray as $cgNum => $cgName) {
						$dataURL = "http://localhost/CASecure_MSP_ver_1.0/proxies/PatchComplianceReportSummary.php?user=".$bigfixCurrentUser."&pass=".$bigFixCurrentPassword."&serv=".$bigFixCurrentServer."&cg=".$cgName."&sg=".$siteGroupString;
						
						$xml = simplexml_load_file($dataURL);
						
						foreach($xml->Query->Result->Tuple as $siteResult) {
							//foreach($siteResult->Answer as )
							//print_r($siteResult);
							//echo $siteResult->Answer[0].", ".$siteResult->Answer[1].", ".$siteResult->Answer[2].", 
							$logData[] = 
								array(
									"site" => ($siteResult->Answer[0]->__toString()), 
									"computer_group" => ($siteResult->Answer[1]->__toString()), 
									"timestamp" => (date("Y-m-d H:i:sO")), 
									"system_count" => ($siteResult->Answer[2]->__toString()), 
									"applicable_count" => ($siteResult->Answer[3]->__toString()), 
									"installed_count" => ($siteResult->Answer[4]->__toString()), 
									"outstanding_count" => ($siteResult->Answer[5]->__toString()), 
									"compliance" => ($siteResult->Answer[6]->__toString()) 
								);
						}
						echo "Data collected for Site '".$siteName."' and Computer Group '".$cgName."'.<br/>";
					}
					//
				}
			*/
				//print_r($logData);
			/*	
				$dataLogQuery = pdoMultiInsert("historic_patch_compliance_report", $logData, $db);
				$dataLogQuery->execute();
				
				$changeLogSQL = 
					"INSERT INTO database_change_log ".
					"(change_maker_id, timestamp, type_of_change, action_taken, affected_tables ) ".
					"VALUES (:currentUserID, :timestamp, 'Historic Report Data Log', :message, 'historic_patch_compliance_report');";
				
				$query3 = $db->prepare($changeLogSQL);
				
				$message = 'Daily data log for Historic Patch Compliance Report for all Sites and Computer Groups';
				
				$query3->bindParam(':currentUserID', $currentUserID, PDO::PARAM_STR);
				$query3->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
				$query3->bindParam(':message', $message, PDO::PARAM_STR);
				
				$query3->execute();
			
				echo "Data Successfully logged for Timestamp '".$timestamp."'.<br/>";
				echo "Transaction Logged";
			*/
			$db->commit();
		}
		catch(\PDOException $e) {
			/*if ($query->errorCode() == 23502) {
				echo 'Username and Password cannot be empty.';
			}
			else if ($query->errorCode() == 23503) {
				echo 'You are not able to create users.';
			}
			else if ($query->errorCode() == 23505) {
				echo 'Username "'.$userName.'" already exists. Enter a differnt value for Username.';
			}*/
			$e->getMessage();
		}
	}
	catch (PDOException $e) {
		$e->getMessage();
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