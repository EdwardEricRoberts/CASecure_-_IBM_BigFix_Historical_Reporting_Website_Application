<?php
	$timestamp = date("Y-m-d H:i:sO");
	//echo "\nTime = ".$timestamp."\n";
	echo "Time = ".$timestamp."<br/><br/>";
	
	//$userName = "eer";
	//$password = "AllieCat5";
	//$server = "bigfix.internal.cassevern.com";
	
	//$userName = implode(" ", array_slice($argv, 1, 1));
	//$password = implode(" ", array_slice($argv, 2, 1));
	//$server = implode(" ", array_slice($argv, 3, 1)); // Must be entered with periods "."s instead of "%25"s
	
	$userName = $_GET['user'];
	$password = $_GET['pass'];
	$server = $_GET['serv'];
	
	//$db_host = implode(" ", array_slice($argv, 4, 1));
	//$db_name = implode(" ", array_slice($argv, 5, 1));
	//$db_username = implode(" ", array_slice($argv, 6, 1));
	//$db_password = implode(" ", array_slice($argv, 7, 1));
	
	$db_host = "localhost";
	$db_name = "CASecure2";
	$db_username = "postgres";
	$db_password = "abc.123";
	
	// Run the following command from the Command Prompt to run this file manually
	//php C:\Bitnami\wappstack-7.1.18-0\apache2\htdocs\CASecure_MSP_ver_1.0\database\log\LogHistoricPatchComplianceReportData2.php eer AllieCat5 bigfix.internal.cassevern.com localhost CASecure2 postgres abc.123
	try {
		$db = new PDO('pgsql:host='.$db_host.';dbname='.$db_name,$db_username,$db_password);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	
		try {
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$db->beginTransaction();
			
				$siteURL = "http://localhost/CASecure_MSP_ver_1.0/proxies/SitesHistoricalReportDropDownList.php?user=".$userName."&pass=".$password."&serv=".$server;
				$siteXML = simplexml_load_file($siteURL);
				
				foreach($siteXML->Query->Result->Tuple as $tuple) {
					$siteName = $tuple->Answer[0]->__toString();
					$siteDisplayName = $tuple->Answer[1]->__toString();
					
					$patchComplianceDataURL = "http://localhost/CASecure_MSP_ver_1.0/proxies/PatchComplianceReportBySite.php?user=".$userName."&pass=".$password."&serv=".$server."&site=".$siteName;
					$patchComplianceDataXML = simplexml_load_file($patchComplianceDataURL);
					
					if ($patchComplianceDataXML->Query->Result->Tuple->count() != 0) {
						//echo $patchComplianceDataXML->Query->Result->Tuple->count();
						//echo "<br/>";
						$reportDataArray = [];
						foreach ($patchComplianceDataXML->Query->Result->Tuple as $tuple) {
							$reportDataArray[] = 
								array(
									"site" => $siteName, 
									"timestamp" => $timestamp, 
									"computer_id" => $tuple->Answer[0]->__toString(), 
									"computer_name" => $tuple->Answer[1]->__toString(), 
									"device_type" => $tuple->Answer[2]->__toString(), 
									"users" => $tuple->Answer[3]->__toString(), 
									"operating_system" => $tuple->Answer[4]->__toString(), 
									"ip_addresses" => $tuple->Answer[5]->__toString(), 
									"last_report_time_string" => $tuple->Answer[6]->__toString(), 
									"last_report_time_timestamp" => $tuple->Answer[7]->__toString(), 
									"applicable_patches" => $tuple->Answer[8]->__toString(), 
									"installed_patches" => $tuple->Answer[9]->__toString(), 
									"outstanding_patches" => $tuple->Answer[10]->__toString(), 
									"compliance" => $tuple->Answer[11]->__toString() 
								);
						}
						//print_r($reportDataArray);
						//echo "<br><br>";
						
						$reportQuery = pdoMultiInsert("computer_historic_patch_compliance_report", $reportDataArray, $db);
						$reportQuery->execute();
						
						echo 'Patch Compliance Report Data Logged for Site "'.$siteDisplayName.'".<br/><br/>';
					}	
				}
				
				$cgURL = "http://localhost/CASecure_MSP_ver_1.0/proxies/ComputerGroupsHistoricalReportDropDownList.php?user=".$userName."&pass=".$password."&serv=".$server;
				$cgXML = simplexml_load_file($cgURL);
				
				foreach ($cgXML->Query->Result->Answer as $answer) {
					$computerGroup = $answer->__toString();
					
					$cgComputersURL = "http://localhost/CASecure_MSP_ver_1.0/proxies/ComputersListByComputerGroup.php?user=".$userName."&pass=".$password."&serv=".$server."&cg=".$computerGroup;
					$cgComputersXML = simplexml_load_file($cgComputersURL);
					
					if ($cgComputersXML->Query->Result->Answer->count() != 0) {
						$historicComputerGroupComputersArray = [];
						foreach ($cgComputersXML->Query->Result->Answer as $answer) {
							$computerId = $answer->__toString();
							$historicComputerGroupComputersArray[] = 
								array(
									"timestamp" => $timestamp, 
									"computer_group" => $computerGroup, 
									"computer_id" => $computerId 
								);
						}
						//print_r($historicComputerGroupComputersArray);
						//print_r(array_keys($historicComputerGroupComputersArray[0]));
						//echo "<br><br>";
						
						$historicComputerGroupsQuery = pdoMultiInsert("historic_computer_groups", $historicComputerGroupComputersArray, $db);
						$historicComputerGroupsQuery->execute();
						
						echo 'Computer Data Logged for Computer Group "'.$computerGroup.'".<br/><br/>';
					}
				}
				
				
			$db->commit();
		}
		catch(\PDOException $e) {
			$db->rollback();
			echo $e->getMessage();
			//echo errorHandle($e);
		}
	}
	catch(PDOException $e) {
		
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