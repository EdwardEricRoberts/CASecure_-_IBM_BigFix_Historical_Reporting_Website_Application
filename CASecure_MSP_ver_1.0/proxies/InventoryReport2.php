<?php
	// Defines output as an XML Document
	header('Content-type: application/xml');
	
	$timestamp = date("Y-m-d H:i:sO");
	$fileName = basename(__FILE__, '.php').'.php';
	$fileDirectory = getcwd();
	$requestURI = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http")."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	
	// Fetches HTTP variables from the PHP's Domain URL into PHP variables
	$userName = $_GET['user'];
	$password = $_GET['pass'];
	$server = $_GET['serv'];
	$filter = $_GET['cg'];
	
	$filter = str_replace('%20', ' ', $filter);
	$filter = str_replace('%2F', '/', $filter);
	
	// Relevance Query as Concatenated String
	$relevance = 
		//'number of '.
		'('.
			'id of item 0 of it, '.
			'(name of item 0 of it | "<none>") & " <br>" & (device type of item 0 of it | "<none>"), '.
			//'concatenation " <br>" of values of results from (bes property "User Name") of it as string | "<none>", '.
			//'concatenation " <br>" of values of results (item 0 of it, elements of item 1 of it) as string | "<none>", '.
			'concatenation " <br>" of values of item 1 of it, '.
			'('.
				'if ((first 3 of it) as string = "Win") '.
				'then ((preceding text of first " " of it) as string & html " <br>" as string & (following text of first " " of it) as string) '.
				'else ((preceding text of last " " of it) as string & html " <br>" as string & (following text of last " " of it) as string)'.
			') of '.
			'operating system of item 0 of it | "<none>", '.
			'concatenation (" <br>") of (ip addresses of item 0 of it as string) | "<none>", '.
			'('.
				'(preceding text of end of first "Hz" of it) as string & '.
				'html " <br>" as string & '.
				'(following text of end of first "Hz " of it) as string '.
			') of '.
			'cpu of item 0 of it | "<none>", '.
			'('.
				'('.
					'((day_of_week of it) as three letters) & ", " & '.
					'((month of it) as three letters) & " " & ((day_of_month of it) as two digits) & ", " & ((year of it) as string) '.
				') of '.
				'date (local time zone) of it & '.
				'html " <br>" & '.
				'('.
					'if ((hour_of_day of it) <= 12) '.
					'then '.
					'('.
						'if ((hour_of_day of it != 0 )) '.
						'then ((two digit hour of it) & ":" & (two digit minute of it) & ":" & (two digit second of it) & " AM") '.
						'else ((12 as string) & ":" & (two digit minute of it) & ":" & (two digit second of it) & " AM") '.
					') '.
					'else '.
					'('.
						'('.
							'if (((hour_of_day of it) - 12) < 10) '.
							'then ((0 as string) & (((hour_of_day of it) - 12) as string)) '.
							'else (((hour_of_day of it) - 12) as string) '.
						') & ":" & (two digit minute of it) & ":" & (two digit second of it) & " PM"'.
					')'.
				') of '.
				'time (local time zone) of it '.
			') of last report time of item 0 of it'.
		') '.
		'of '.
		'( ';
	if ($filter == "All Machines") {
		$relevance .= 
			'computers of it, '.
			'it '.
		') ';
	}
	else {
		$relevance .= 
			'item 0 of it, '.
			'item 2 of it '.
		') '.
		'of '.
		'( '.
			'computers of it, '.
			'members of '.
			'bes computer groups '.
			'whose ( name of it = "'.$filter.'"), '.
			'it '.
		') '.
		'whose (id of item 0 of it = id of item 1 of it)';
	}
	$relevance .= 
		'of '.
		'( '.
			'results of bes properties whose (id of it = (2299708709, 29, 1)) '.
		')';
		
		
	$relevanceReg = $relevance;
	
	// HTTP Encoding to make the relevance query URL Friendly
	$relevance = str_replace('%', '%252525', $relevance);
	$relevance = str_replace(' ', '%20', $relevance);
	$relevance = str_replace('!', '%21', $relevance);
	$relevance = str_replace('"', '%22', $relevance);
	$relevance = str_replace('#', '%23', $relevance);
	$relevance = str_replace('$', '%24', $relevance);
	$relevance = str_replace('&', '%26', $relevance);		// ' = %27
	$relevance = str_replace('*', '%2A', $relevance);
	$relevance = str_replace('+', '%2B', $relevance);
	$relevance = str_replace(',', '%2C', $relevance);
	$relevance = str_replace('-', '%2D', $relevance);
	$relevance = str_replace('.', '%2E', $relevance);
	$relevance = str_replace('/', '%2F', $relevance);
	$relevance = str_replace(':', '%3A', $relevance);
	$relevance = str_replace(';', '%3B', $relevance);
	$relevance = str_replace('<', '%3C', $relevance);
	$relevance = str_replace('=', '%3D', $relevance);
	$relevance = str_replace('>', '%3E', $relevance);
	$relevance = str_replace('?', '%3F', $relevance);
	$relevance = str_replace('@', '%40', $relevance);
	$relevance = str_replace('\\', '%5C', $relevance);
	$relevance = str_replace('_', '%5F', $relevance);
	$relevance = str_replace('~', '%7E', $relevance);
	
	// BigFix REST API URL
	$url = "https://".$server."/api/query?relevance=".$relevance;
	
	// Proxy call to BigFix server using PHP cURL
	$ch = curl_init();
	// Submits the API's URL
	curl_setopt($ch, CURLOPT_URL,$url);
	// Defines authorization type as Basic Authentication
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	// Submits User Name and Password to the URL
	curl_setopt($ch, CURLOPT_USERPWD, "$userName:$password");
	// Allows output to be set to a variable
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
	// Turns off SSL Verification
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	// Sets cURL output to return variable
	$returned = curl_exec($ch);
	// Check for cURL Connection Errors
	if(curl_errno($ch)) {
		
		$errorDescription = "Could not connect to BigFix Server.";
		$errorMessage = curl_error($ch);
		$errorCode = curl_errno($ch);
		
		// Closes cURL
		curl_close ($ch);
		
		$xml= new SimpleXMLElement('<BESAPI/>'); 
		$queryXML = $xml->addChild('Query');
		$queryXML->addAttribute('Resource', $relevanceReg);
		$queryXML->addChild('Result');
		$queryXML->addChild('Error', $errorDescription);
		$queryXML->addChild('CURL_Error_Message', $errorMessage);
		$queryXML->addChild('CURL_Error_Number', $errorCode);
		
		// Log cURL Connection Error in Database
		try {
			$db_host = "localhost";
			$db_name = "CASecure1";
			$db_username = "postgres";
			$db_password = "abc.123";
			$db = new PDO('pgsql:host='.$db_host.';dbname='.$db_name,$db_username,$db_password);
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			try {
				$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				
				$errorLogSQL = 
					"INSERT INTO error_log ".
					"(description, error_code, error_message, exception_type, timestamp, file_name, file_directory, request_uri) ".
					"VALUES (:description, :errorCode, :errorMessage, 'BigFix Server cURL', :timestamp, :fileName, :fileDirectory, :requestURI);";
				$errorLogQuery = $db->prepare($errorLogSQL);
				$errorLogQuery->bindParam(':description', $errorDescription, PDO::PARAM_STR);
				$errorLogQuery->bindParam(':errorCode', $errorCode, PDO::PARAM_STR);
				$errorLogQuery->bindParam(':errorMessage', $errorMessage, PDO::PARAM_STR);
				$errorLogQuery->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
				$errorLogQuery->bindParam(':fileName', $fileName, PDO::PARAM_STR);
				$errorLogQuery->bindParam(':fileDirectory', $fileDirectory, PDO::PARAM_STR);
				$errorLogQuery->bindParam(':requestURI', $requestURI, PDO::PARAM_STR);
				$errorLogQuery->execute();
			}
			catch(\PDOException $e) {
				$queryXML->addChild('Log_Error', 'Failed to Log Error.');
				$db->rollback();
			}
		}
		catch(PDOException $e){
			$queryXML->addChild('Log_Error', 'Logged Error to File.');
			
			$errorArray = array(
					"user_id" => "",
					"description" => $errorDescription,
					"error_code" => $errorCode,
					"error_message" => $errorMessage,
					"exception_type" => "BigFix Server cURL",
					"timestamp" => $timestamp,
					"file_name" => $fileName,
					"file_directory" => $fileDirectory,
					"request_uri" => $requestURI
				);
				//print_r($errorArray);
				$connectionErrorsFile = fopen('C:\Bitnami\wappstack-7.3.9-0\apache2\htdocs\CASecure_MSP_ver_1.0\database\error\ConnectionErrors.csv', 'a');
				fputcsv($connectionErrorsFile, $errorArray);
				fclose($connectionErrorsFile);
		}
		
		echo $xml->asXML();
	}
	else {
		// Closes cURL
		curl_close ($ch);
		
		// Check for HTTP Status Errors
		if (substr($returned, 0, 4) === 'HTTP') {
			
			$errorDescription = "Could not connect to BigFix Server.";
			$errorMessage = $returned;
			$errorCode = substr($returned, 5, 3);
			
			$xml= new SimpleXMLElement('<BESAPI/>'); 
			$queryXML = $xml->addChild('Query');
			$queryXML->addAttribute('Resource', $relevanceReg);
			$queryXML->addChild('Result');
			$queryXML->addChild('Error', $errorDescription);
			$queryXML->addChild('HTTP_Error', $errorMessage);
			$queryXML->addChild('HTTP_Error_Number', $errorCode);
			
			// Log HTTP Status Error in Database
			try {
				$db_host = "localhost";
				$db_name = "CASecure1";
				$db_username = "postgres";
				$db_password = "abc.123";
				$db = new PDO('pgsql:host='.$db_host.';dbname='.$db_name,$db_username,$db_password);
				$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				
				try {
					$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					
					$errorLogSQL = 
						"INSERT INTO error_log ".
						"(description, error_code, error_message, exception_type, timestamp, file_name, file_directory, request_uri) ".
						"VALUES (:description, :errorCode, :errorMessage, 'BigFix Server HTTP', :timestamp, :fileName, :fileDirectory, :requestURI);";
					$errorLogQuery = $db->prepare($errorLogSQL);
					$errorLogQuery->bindParam(':description', $errorDescription, PDO::PARAM_STR);
					$errorLogQuery->bindParam(':errorCode', $errorCode, PDO::PARAM_STR);
					$errorLogQuery->bindParam(':errorMessage', $errorMessage, PDO::PARAM_STR);
					$errorLogQuery->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
		 			$errorLogQuery->bindParam(':fileName', $fileName, PDO::PARAM_STR);
					$errorLogQuery->bindParam(':fileDirectory', $fileDirectory, PDO::PARAM_STR);
					$errorLogQuery->bindParam(':requestURI', $requestURI, PDO::PARAM_STR);
					$errorLogQuery->execute();
				}
				catch(\PDOException $e) {
					$queryXML->addChild('Log_Error', 'Failed to Log Error.');
					$db->rollback();
				}
			}
			catch(PDOException $e){
				$queryXML->addChild('Log_Error', 'Logged Error to File.');
				
				$errorArray = array(
					"user_id" => "",
					"description" => $errorDescription,
					"error_code" => $errorCode,
					"error_message" => $errorMessage,
					"exception_type" => "BigFix Server HTTP",
					"timestamp" => $timestamp,
					"file_name" => $fileName,
					"file_directory" => $fileDirectory,
					"request_uri" => $requestURI
				);
				//print_r($errorArray);
				$connectionErrorsFile = fopen('C:\Bitnami\wappstack-7.3.9-0\apache2\htdocs\CASecure_MSP_ver_1.0\database\error\ConnectionErrors.csv', 'a');
				fputcsv($connectionErrorsFile, $errorArray);
				fclose($connectionErrorsFile);
			}
			
			echo $xml->asXML();
		}
		else {
			// Check if BigFix Relevance Error Exists and if so Log it in Database
			$dom = new DOMDocument(); 
			$dom->loadXML($returned);
			$errorNodes = $dom->getElementsByTagName('Error');
			if ($errorNodes->length != 0) {
				
				$errorDescription = "Issue with BigFix Relevance Code.";
				$errorMessage = $errorNodes[0]->nodeValue;
				$errorCode = "";
				
				// Log BigFix Relevance Error in Database
				try {
					$db_host = "localhost";
					$db_name = "CASecure1";
					$db_username = "postgres";
					$db_password = "abc.123";
					$db = new PDO('pgsql:host='.$db_host.';dbname='.$db_name,$db_username,$db_password);
					$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					
					try {
						$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
						
						$errorLogSQL = 
							"INSERT INTO error_log ".
							"(description, error_message, exception_type, timestamp, file_name, file_directory, request_uri) ".
							"VALUES (:description, :errorMessage, 'BigFix Relevance', :timestamp, :fileName, :fileDirectory, :requestURI);";
						$errorLogQuery = $db->prepare($errorLogSQL);
						$errorLogQuery->bindParam(':description', $errorDescription, PDO::PARAM_STR);
						$errorLogQuery->bindParam(':errorMessage', $errorMessage, PDO::PARAM_STR);
						$errorLogQuery->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
						$errorLogQuery->bindParam(':fileName', $fileName, PDO::PARAM_STR);
						$errorLogQuery->bindParam(':fileDirectory', $fileDirectory, PDO::PARAM_STR);
						$errorLogQuery->bindParam(':requestURI', $requestURI, PDO::PARAM_STR);
						$errorLogQuery->execute();
					}
					catch(\PDOException $e) {
						//$queryXML->addChild('Log_Error', 'Failed to Log Error.');
						$db->rollback();
					}
				}
				catch(PDOException $e){
					//$queryXML->addChild('Log_Error', 'Failed to Log Error.');
					
					$errorArray = array(
					"user_id" => "",
					"description" => $errorDescription,
					"error_code" => $errorCode,
					"error_message" => $errorMessage,
					"exception_type" => "BigFix Relevance",
					"timestamp" => $timestamp,
					"file_name" => $fileName,
					"file_directory" => $fileDirectory,
					"request_uri" => $requestURI
				);
				//print_r($errorArray);
				$connectionErrorsFile = fopen('C:\Bitnami\wappstack-7.3.9-0\apache2\htdocs\CASecure_MSP_ver_1.0\database\error\ConnectionErrors.csv', 'a');
				fputcsv($connectionErrorsFile, $errorArray);
				fclose($connectionErrorsFile);
				}
				echo $returned;
			}
			// No Errors of any Kind
			else {
				// Outputs results to the page
				echo $returned; 
			}
		}
	}
	
	
?>