<?php
	// Defines output as an XML Document
	header('Content-type: application/xml');
	
	// Fetches HTTP variables from the PHP's Domain URL into PHP variables
	$userName = $_GET['user'];
	$password = $_GET['pass'];
	$server = $_GET['serv'];
	$computerGroup = $_GET['cg'];
	//$site = $_GET['site'];
	
	
	$computerGroup = str_replace('%20', ' ', $computerGroup);
	$computerGroup = str_replace('%2F', '/', $computerGroup);
	
//	$site = str_replace('%20', ' ', $site);
//	$site = str_replace('%2F', '/', $site);
	/*
	$cgURL = "http://localhost/CASecure_MSP_ver_1.0/proxies/ComputerGroupsBySiteName.php?user=".$userName."&pass=".$password."&serv=".$server;
	
	$cgXML = simplexml_load_file($cgURL);
	
	$computerGroups = array();
	$cgCount = 0;
	
	foreach ($cgXML->Query->Result->Tuple as $computerGroup) {
		
		$computerGroupName = "";
		$parentSite = "";
		$type = "";
		$i = 0;
		
		foreach ($computerGroup->Answer as $key => $value) {
			if ($i == 1) {
				$computerGroupName = $value;
			}
			$i++;
		}
		
		$computerGroups[$cgCount] = $computerGroupName->__toString();
		$cgCount++;
	}
	*/
	//print_r($computerGroups);
	//
	$siteURL = "http://localhost/CASecure_MSP_ver_1.0/proxies/MasterSiteList.php?user=".$userName."&pass=".$password."&serv=".$server;
	
	$siteXML = simplexml_load_file($siteURL);
	
	$sites = array();
	$siteCount = 0;
	
	foreach ($siteXML->Query->Result->Tuple as $site) {
		
		$siteName = "";
		$displayName = "";
		$type = "";
		$i = 0;
		
		foreach ($site->Answer as $key => $value) {
			if ($i == 1) {
				$siteName = $value;
			}
			else if ($i == 3) {
				$type = $value;
			}
			$i++;
		}
		if ($type != "operator") {
		//if ($siteName == "eer@internal.cassevern.com") {
			$sites[$siteCount] = $siteName->__toString();
			$siteCount++;
		}
	}
	//print_r($sites);
	//
	
	// Relevance Query as Concatenated String
	$relevance = '';
	//for ($i = 0; $i < 7; $i++) {
	//	if ($i != 0) {
	//		$relevance .= '; ';
	//	}
		
	foreach($sites as $siteNum => $site) {
		if ($siteNum != 0) {
			$relevance .= '; ';
		}
		$relevance .= 
			//'number of '.
			'( '.
				'"'.$site.'", '.
				'"'.$computerGroup.'", '.
				'size of item 0 of it, '.
				'item 2 of it, '.
				'(item 2 of it) - (item 1 of it), '.
				'item 1 of it, '.
				'( '.
					'if (item 2 of it = 0) '.
					'then ("100.0%") '.
					'else (relative significance place 3 of (100 - (item 1 of it as floating point / item 2 of it as floating point * 100)) as string & "%") '.
				')'.
			') '.
			'of '.
			'( '.
				'item 0 of it, '.
				'number of results (elements of item 0 of it, elements of item 1 of it) whose (relevant flag of it), '.
				'number of results (elements of item 0 of it, elements of item 1 of it) whose (remediated flag of it or relevant flag of it) '.
			') '.
			'of '.
			'( '.
				'(item 0 of it), '. // whose (id of it = unique values of ids of members of bes computer group whose(name of it = ""))
				'item 1 of it '.
			') '.
			'of '.
			'( '.
				'( '.
					'set of '.
					'items 0 of '.
					'( '.
						'subscribed computers of bes sites '.
						'whose (name of it = "'.$site.'"), '.
						'it'.
					') '.
					'whose (id of item 0 of it = id of item 1 of it) '.
					'of '.						'members of '.
					'bes computer groups '.
					'whose (name of it = "'.$computerGroup.'") '.
				'), '.
				'( '.
					'set of fixlets '.
					'whose '.
					'( '.
						'globally visible flag of it = TRUE and '.
						'( '.
							'exists source severity '.
							'whose '.
							'( '.
								'it is not "" and '.
								'it does not contain "N/A" and '.
								'it does not contain "Unspecified" '.
							') '.
							'of it '.
						') and '.
						'(source release date of it) > date "01 Jan 2000" and '.
						'exists results whose (remediated flag of it or relevant flag of it) of it and '.
						'exists actions of it '.
					') '.
					'of '.
					'bes sites '.
					'whose (name of it = "'.$site.'") '.
				') '.
			') ';
	}
	//}
	//echo $relevance;
//	
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
	// Closes cURL
	curl_close ($ch);
	// Outputs results to the page
	echo $returned; 
//
?>