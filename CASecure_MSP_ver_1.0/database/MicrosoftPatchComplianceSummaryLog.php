<?php
	//$id = 2;
	$timestamp = date("Y-m-d h:i:sO");
	$applicableCount = $_GET['appl'];
	$installedCount = $_GET['inst'];
	$outstandingCount = $_GET['out'];
	$compliance = $_GET['comp'];
	$computerGroup = $_GET['cg'];
	
	$db_host = "localhost";
	$db_name = "postgres";
	$db_username = "postgres";
	$db_password = "abc.123";
	
	$db = new PDO('pgsql:host='.$db_host.';dbname='.$db_name,$db_username,$db_password);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	
	//$lastId = $db->lastInsertId();
	//$id = $lastId + 1;
	
	$sql =
		"INSERT INTO microsoft_patch_compliance_summary ".
		"(timestamp, applicable, installed, outstanding, compliance, computer_group) ".
		"VALUES ('$timestamp', '$applicableCount', '$installedCount', '$outstandingCount', '$compliance', '$computerGroup')";
	
	$query = $db->prepare($sql);
	
	try {
		$query->execute();
		echo "Data was successfully sent to the Database!\ntimestamp = ".$timestamp."\napplicable patches = ".$applicableCount."\ninstalled patches = ".$installedCount."\noutstanding patches = ".$outstandingCount."\ncompliance = ".$compliance."\ncomputer group = ".$computerGroup;
	}
	catch (PDOException $e) {
		$db->rollback();
		echo errorHandle($e);
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
	//
?>