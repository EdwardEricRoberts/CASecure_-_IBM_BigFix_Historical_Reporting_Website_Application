<?php
	$timestamp = date("Y-m-d H:i:sO");
	//echo "\nTime = ".$timestamp."\n";//"<br />";
	
	//$userName = "eer";
	//$password = "AllieCat5";
	//$server = "bigfix.internal.cassevern.com";
	
	$userName = implode(" ", array_slice($argv, 1, 1));
	$password = implode(" ", array_slice($argv, 2, 1));
	$server = implode(" ", array_slice($argv, 3, 1)); // Must be entered with periods "."s instead of "%25"s
	
	//$userName = $_GET['user'];
	//$password = $_GET['pass'];
	//$server = $_GET['serv'];
	
	$db_host = implode(" ", array_slice($argv, 4, 1));
	$db_name = implode(" ", array_slice($argv, 5, 1));
	$db_username = implode(" ", array_slice($argv, 6, 1));
	$db_password = implode(" ", array_slice($argv, 7, 1));
	
	//$db_host = "localhost";
	//$db_name = "postgres";
	//$db_username = "postgres";
	//$db_password = "abc.123";
	
	// Run the following command from the Command Prompt to run this file manually
	//php C:\Bitnami\wappstack-7.1.18-0\apache2\htdocs\CASecure_MSP_ver_1.0\database\MicrosoftPatchComplianceSummaryData.php eer AllieCat5 bigfix.internal.cassevern.com localhost postgres postgres abc.123
	
	$db = new PDO('pgsql:host='.$db_host.';dbname='.$db_name,$db_username,$db_password);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	
	$cgURL = "http://localhost/CASecure_MSP_ver_1.0/proxies/ComputerGroupsDropDownList.php?user=".$userName."&pass=".$password."&serv=".$server;
	
	$cgXML = simplexml_load_file($cgURL);
	
	foreach ($cgXML->Query->Result->Answer as $computerGroup) {
		
		$dataURL = "http://localhost/CASecure_MSP_ver_1.0/proxies/MicrosoftPatchComplianceReport.php?user=".$userName."&pass=".$password."&serv=".$server."&cg=".$computerGroup;
		
		$xml = simplexml_load_file($dataURL);
		
		$applicable = 0;
		$installed = 0;
		$outstanding = 0;
		
		foreach ($xml->Query->Result->Tuple as $row) {
			$applicable += $row->Answer[6];
			$installed += $row->Answer[7];
			$outstanding += $row->Answer[8];
		}
		
		if ($applicable == 0) {
			$compliance = "100%";
		}
		else {
			$compliance = round((($installed / $applicable) * 100), 0)."%";
		}
		
		//if ($db_name == "") {
		$sqlTableName = "microsoft_patch_compliance_summary_2";
		//}
		//else if ($db_name == "") {
			//$sqlTableName = "";
		//}
		
		$sql =
			"INSERT INTO ".$sqlTableName." ".
			"(timestamp, applicable, installed, outstanding, compliance, computer_group, logged_by) ".
			"VALUES (:timestamp, :applicable, :installed, :outstanding, :compliance, :computerGroup, :userName)";
		
		$query = $db->prepare($sql);
		
		$query->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
		$query->bindParam(':applicable', $applicable, PDO::PARAM_STR);
		$query->bindParam(':installed', $installed, PDO::PARAM_STR);
		$query->bindParam(':outstanding', $outstanding, PDO::PARAM_STR);
		$query->bindParam(':compliance', $compliance, PDO::PARAM_STR);
		$query->bindParam(':computerGroup', $computerGroup, PDO::PARAM_STR);
		$query->bindParam(':userName', $userName, PDO::PARAM_STR);
		
		try {
			$query->execute();
			echo "\nData was successfully sent to the Database, for Computer Group = '".$computerGroup."'.\n";//"'.<br />";
		}
		catch (PDOException $e) {
			$db->rollback();
			echo errorHandle($e);
		}
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