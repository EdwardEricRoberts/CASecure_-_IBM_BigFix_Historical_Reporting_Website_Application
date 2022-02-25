<?php
	
	header('Content-type: application/xml');
	
	$timestamp = date("Y-m-d H:i:sO");
	
	$fileName = basename(__FILE__, '.php').'.php';
	$fileDirectory = getcwd();
	$requestURI = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http")."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	
	$currentUser = $_GET['cid'];
	$site = $_GET['site'];
	$computerGroup = $_GET['cg'];
	
	$db_host = "localhost";
	$db_name = "CASecure2";
	//$db_name = "CASecureTierpoint1";
	$db_username = "postgres";
	$db_password = "abc.123";
	
	$xml = new SimpleXMLElement('<PDO/>');
	$connectionXML = $xml->addChild('Connection');
	try{
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
				$metaXML->addChild('Name', "Fetch Historic Patch Compliance Report Data");
				$metaXML->addChild('Description', "This query returns the data for the Historic Patch Compliance Report.");
				$paramsXML = $metaXML->addChild('Parameters');
				$param1XML = $paramsXML->addChild('Parameter');
				$param1XML->addChild('Name', 'Current User ID');
				$param1XML->addChild('URL', 'cid');
				$param1XML->addChild('Value', $currentUser);
				$param1XML->addChild('Description', 'The User ID of the current User.');
				$param2XML = $paramsXML->addChild('Parameter');
				$param2XML->addChild('Name', 'Site');
				$param2XML->addChild('URL', 'site');
				$param2XML->addChild('Value', $site);
				$param2XML->addChild('Description', 'The User selected Site.');
				$param3XML = $paramsXML->addChild('Parameter');
				$param3XML->addChild('Name', 'Computer Group');
				$param3XML->addChild('URL', 'cg');
				$param3XML->addChild('Value', $computerGroup);
				$param3XML->addChild('Description', 'The User selected Computer Group.');
				$resultXML = $queryXML->addChild('Result');
				
				$start = microtime(true);
				
				$actionsXML = $resultXML->addChild('Actions');
				
				if ($db_name == "CASecure2") {
					$table = "computer_historic_patch_compliance_report";
				}
				else if ($db_name == "CASecureTierpoint1") {
					$table = "historic_patch_compliance_report";
				}
				
				$fetchReportDataSQL = 
					"SELECT ".
						"'".$site."' AS site, ".
						"'".$computerGroup."' AS computer_group, ".
						"ag.timestamp, ".
						"ag.applicable_count, ".
						"ag.installed_count, ".
						"ag.outstanding_count, ".
						"ag.compliance, ".
						"ag.system_count ".
					"FROM ".
					"(".
						"SELECT ".
							"pc.timestamp, ".
							"SUM(pc.applicable_patches) AS applicable_count, ".
							"SUM(pc.installed_patches) AS installed_count, ".
							"SUM(pc.outstanding_patches) AS outstanding_count, ".
							"CASE ".
								"WHEN (SUM(pc.applicable_patches) = 0) THEN ('100.00%') ".
								"ELSE (ROUND(((SUM(pc.installed_patches)/SUM(pc.applicable_patches))*100), 2) || '%') ".
							"END AS compliance, ".
							"COUNT(pc.computer_id) AS system_count ".
						"FROM ( ".
							"SELECT * ".
							"FROM ( ".
								"SELECT * ".
								"FROM ".$table." ".
								"WHERE (".
									"( ".
										"site = 'Enterprise Security' ".
										"AND ".
										"( ".
											"( ".
												"( ".
													"timestamp <= ( ".
														"DATE_TRUNC('month', timestamp) + ".
														"(".
															"(".
																"((9 - CAST(EXTRACT(dow FROM DATE_TRUNC('month', timestamp)) AS INT)) % 7) ".
																" + 7 ".
															") ".
														" || ' days')::INTERVAL ".
													") ".
												") ".
												"AND ".
												"( ".
													"last_report_time_timestamp > ( ".
														"(DATE_TRUNC('month', timestamp) - INTERVAL '1 month') + ".
														"(".
															"(".
																"((9 - CAST(EXTRACT(dow FROM (DATE_TRUNC('month', timestamp) - INTERVAL '1 month')) AS INT)) % 7) ".
																" + 9 ".
															") ".
														" || ' days')::INTERVAL ".
													") ".
												") ".
											") ".
											"OR ".
											"( ".
												"( ".
													"timestamp >= ( ".
														"DATE_TRUNC('month', timestamp) + ".
														"(".
															"(".
																"((9 - CAST(EXTRACT(dow FROM DATE_TRUNC('month', timestamp)) AS INT)) % 7) ".
																" + 9".
															") ".
														" || ' days')::INTERVAL ".
													")".
												") ".
												"AND ".
												"( ".
													"last_report_time_timestamp > ( ".
														"DATE_TRUNC('month', timestamp) + ".
														"(".
															"(".
																"((9 - CAST(EXTRACT(dow FROM DATE_TRUNC('month', timestamp)) AS INT)) % 7) ".
																" + 9 ".
															") ".
														" || ' days')::INTERVAL ".
													") ".
												") ".
											") ".
										") ".
									") ".
									"OR ".
									"( ".
										"site <> 'Enterprise Security' ".
									") ".
								") ".
							") AS  pc1 ".
							"WHERE pc1.site = :site ".
						") AS pc ".
							"JOIN LATERAL ( ".
								"SELECT * ".
								"FROM historic_computer_groups ".
								"WHERE computer_group = :computerGroup ".
							") AS cg ".
								"ON pc.computer_id = cg.computer_id ".
								"AND (".
									"pc.timestamp = cg.timestamp ".
									//"OR ".
									//"DATE_TRUNC('day', pc.timestamp) = DATE_TRUNC('day', cg.timestamp)".
								") ".
						"GROUP BY pc.timestamp ".
					") AS ag ";
				$fetchReportDataQuery = $db->prepare($fetchReportDataSQL);
				$fetchReportDataQuery->bindParam(":site", $site, PDO::PARAM_STR);
				$fetchReportDataQuery->bindParam(":computerGroup", $computerGroup, PDO::PARAM_STR);
				$fetchReportDataQuery->execute();
				$historicReportDataArray = $fetchReportDataQuery->fetchAll(PDO::FETCH_ASSOC);
				
				//print_r($historicReportDataArray);
				
				
				$fetchHistoricDataXML = $actionsXML->addChild('Action');
				$fetchHistoricDataXML->addAttribute('type', 'Fetch Historical Patch Compliance Data');
				$fetchHistoricDataXML->addChild('Status', "Success");
				$fetchHistoricDataXML->addChild('Details', 'Fetched Summerized Patch Compliance Report Data for Site "'.$site.'" and Computer Group "'.$computerGroup.'".');
				$historicDataXML = $fetchHistoricDataXML->addChild('historic_data');
				$historicDataXML->addAttribute('site', $site);
				$historicDataXML->addAttribute('computer_group', $computerGroup);
				
				
				foreach ($historicReportDataArray as $historicDataRow) {
					$dataTimestampXML = $historicDataXML->addChild('data_timestamp');
					$dataTimestampXML->addChild('timestamp', $historicDataRow['timestamp']);
					$dataTimestampXML->addChild('site', $historicDataRow['site']);
					$dataTimestampXML->addChild('computer_group', $historicDataRow['computer_group']);
					$dataTimestampXML->addChild('applicable_count', $historicDataRow['applicable_count']);
					$dataTimestampXML->addChild('installed_count', $historicDataRow['installed_count']);
					$dataTimestampXML->addChild('outstanding_count', $historicDataRow['outstanding_count']);
					$dataTimestampXML->addChild('compliance', $historicDataRow['compliance']);
					$dataTimestampXML->addChild('system_count', $historicDataRow['system_count']);
				}
				$fetchHistoricDataXML->addChild('Count', sizeOf($historicReportDataArray));
				
				$end = microtime(true);
				
				$evaluationXML = $queryXML->addChild('Evaluation');
				$evaluationXML->addChild('Time', round((($end - $start) * 1000), 3).'ms');
				$evaluationXML->addChild('Number_of_Results', sizeOf($historicReportDataArray));
				
			$db->commit();
		}
		catch (\PDOException $e) {
			$errorXML = $queryXML->addChild('Error');
			
			if ($fetchReportDataQuery->errorCode() != 0) {
				$fetchHistoricDataXML = $actionsXML->addChild('Action');
				$fetchHistoricDataXML->addAttribute('type', 'Fetch Historical Patch Compliance Data');
				$fetchHistoricDataXML->addChild('Status', "Failure");
				$errorDescription = '';
				$fetchHistoricDataXML->addChild('Details', $errorDescription);
			}
			echo $e->getMessage();
			$errorXML->addChild('Code', $e->getCode());
			$errorXML->addChild('Details', $e->getMessage());
			$db->rollback();
			//echo "<Error>".errorHandle($e)."</Error>";
			
			try {
				$errorLogSQL = 
					"INSERT INTO error_log ".
					"(user_id, description, error_code, error_message, exception_type, timestamp, file_name, file_directory, request_uri) ".
					"VALUES (:userID, :description, :errorCode, :errorMessage, 'PDO Query', :timestamp , :fileName, :fileDirectory, :requestURI);";
				$errorLogQuery = $db->prepare($errorLogSQL);
				$errorLogQuery->bindParam(':userID', $updaterSQL, PDO::PARAM_STR);
				$errorLogQuery->bindParam(':description', $errorDescription, PDO::PARAM_STR);
				$errorLogQuery->bindParam(':errorCode', $errorCode, PDO::PARAM_STR);
				$errorLogQuery->bindParam(':errorMessage', $errorMessage, PDO::PARAM_STR);
				$errorLogQuery->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
				$errorLogQuery->bindParam(':fileName', $fileName, PDO::PARAM_STR);
				$errorLogQuery->bindParam(':fileDirectory', $fileDirectory, PDO::PARAM_STR);
				$errorLogQuery->bindParam(':requestURI', $requestURI, PDO::PARAM_STR);
				$errorLogQuery->execute();
				
				$errorLogXML = $errorXML->addChild('Error_Log');
				$errorLogXML->addChild('Status', 'Error Logged');
				$errorLogDetailsXML = $errorLogXML->addChild('Details');
				$errorLogDetailsXML->addChild('description', $errorDescription);
				$errorLogDetailsXML->addChild('error_code', $e->getCode());
				$errorLogDetailsXML->addChild('error_message', $e->getMessage());
				$errorLogDetailsXML->addChild('exception_type', 'PDO Query');
				$errorLogDetailsXML->addChild('timestamp', $timestamp);
				$errorLogDetailsXML->addChild('file_name', htmlspecialchars($fileName));
				$errorLogDetailsXML->addChild('file_directory', htmlspecialchars($fileDirectory));
				$errorLogDetailsXML->addChild('request_uri', htmlspecialchars($requestURI));
			}
			catch(\PDOException $e2) {
				$errorLogXML = $errorXML->addChild('Error_Log');
				$errorLogXML->addChild('Status', 'Failed to Log Error');
				$errorLogXML->addChild('Error_Code', $e2->getCode());
				$errorLogXML->addChild('Details', $e2->getMessage());
			}
		}
	}
	catch (PDOException $e) {
		$connectResultXML->addChild('Status', 'Failure');
		$connectResultXML->addChild('Message', "Failed to Connect to Database.  Please Email Site Administrator.");
		$connectResultXML->addChild('Host', $db_host);
		$connectResultXML->addChild('Database', $db_name);
		
		$connectErrorXML = $connectionXML->addChild('Error');
		$connectErrorXML->addChild('Error_Code', $e->getCode());
		$connectErrorXML->addChild('Details', $e->getMessage());
		
		$errorDescription = "Failed to Connect to Database.";
		
		$errorArray = array(
			"user_id" => $updater,
			"description" => $errorDescription,
			"error_code" => $e->getCode(),
			"error_message" => $e->getMessage(),
			"exception_type" => "PDO Connection",
			"timestamp" => $timestamp,
			"file_name" => $fileName,
			"file_directory" => $fileDirectory,
			"request_uri" => $requestURI
		);
		//print_r($errorArray);
		$connectionErrorsFile = fopen('C:\Bitnami\wappstack-7.3.9-0\apache2\htdocs\CASecure_MSP_ver_1.0\database\error\ConnectionErrors.csv', 'a');
		fputcsv($connectionErrorsFile, $errorArray);
		fclose($connectionErrorsFile);
		
		$errorLogXML = $connectErrorXML->addChild('Error_Log');
		$errorLogXML->addChild('Status', 'Error Logged');
		$errorLogDetailsXML = $errorLogXML->addChild('Details');
		$errorLogDetailsXML->addChild('description', $errorDescription);
		$errorLogDetailsXML->addChild('error_code', $e->getCode());
		$errorLogDetailsXML->addChild('error_message', $e->getMessage());
		$errorLogDetailsXML->addChild('exception_type', 'PDO Connection');
		$errorLogDetailsXML->addChild('timestamp', $timestamp);
		$errorLogDetailsXML->addChild('file_name', htmlspecialchars($fileName));
		$errorLogDetailsXML->addChild('file_directory', htmlspecialchars($fileDirectory));
		$errorLogDetailsXML->addChild('request_uri', htmlspecialchars($requestURI));
	}
	catch (Exception $e) {
		$connectResultXML->addChild('Status', 'Failure');
		$connectResultXML->addChild('Message', "An Unexpected Error Occured.  Please try again. If Issue Persists, Please Email Site Administrator.");
		$connectResultXML->addChild('Host', $db_host);
		$connectResultXML->addChild('Database', $db_name);
		
		$connectErrorXML = $connectionXML->addChild('Error');
		$connectErrorXML->addChild('Error_Code', $e->getCode());
		$connectErrorXML->addChild('Details', $e->getMessage());
	}
	finally {
		echo $xml->asXML();
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