<?php
	
	header('Content-type: application/xml');
	
	//$computerGroup = $_GET['cg'];
	
	$db_host = "localhost";
	$db_name = "postgres";
	$db_username = "postgres";
	$db_password = "abc.123";
	
	$db = new PDO('pgsql:host='.$db_host.';dbname='.$db_name,$db_username,$db_password);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		
	//$sqlTableName = "microsoft_patch_compliance_summary_2";
		
	//$sql =
	//	"SELECT * ".
	//	"FROM ".$sqlTableName." ".
	//	"WHERE ".
	//	"computer_group = :computerGroup";
	
	$sqlTableName = "request_criticality";
	
	$sql =
		"SELECT * ".
		"FROM ".$sqlTableName." ".
		"ORDER BY criticality_id";
	
	$query = $db->prepare($sql);
	
	//$query->bindParam(':computerGroup', $computerGroup, PDO::PARAM_STR);
	
	$xml= new SimpleXMLElement('<Result/>'); 
	
	try {
		$query->execute();
		while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
			$tuple = $xml->addChild('Tuple');
			foreach ($row as $key => $value) {
				$tuple->addChild('Answer', $value);
			}
		}
		echo $xml->asXML();
		//echo "\nData was successfully sent to the Database, for Computer Group = '".$computerGroup."'.\n";//"'.<br />";
	}
	catch (PDOException $e) {
		$db->rollback();
		echo "<Error>".errorHandle($e)."</Error>";
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