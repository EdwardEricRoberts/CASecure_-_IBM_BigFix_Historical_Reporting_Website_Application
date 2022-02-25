<?php
	// Defines output as an XML Document
	header('Content-type: application/xml');
	
	// Fetches HTTP variables from the PHP's Domain URL into PHP variables
	$userName = $_GET['user'];
	$password = $_GET['pass'];
	$server = $_GET['serv'];
	
	// Relevance Query as Concatenated String
	$relevance = 
	/*
		'concatenations of '.
		'('.
			'tr of (td "sorttable_customkey=12" of (it as string) of (month_and_year of current date) & '.
			'td "align=right style=padding-right:70px " of ((it as string) of number of elements of item 2 of it) & '.
			'td "class=applicable align=right style=padding-right:70px" of (item 0 of it as string) & '.
			'td "class=installed align=right style=padding-right:70px" of ((item 0 of it - item 1 of it) as string) & '.
			'td "class=outstanding align=right style=padding-right:70px" of (item 1 of it as string) & '.
			'td "class=compliance align=right style=padding-right:60px id=item1" of (if (item 0 of it = 0) then ("-99") else ((((item 0 of it as floating point - item 1 of it) * 100 / item 0 of it) as integer as string))) ) of '.
				'(	number of results whose (exists first became relevant of it) of elements of it, '.
					'number of results whose (relevant flag of it) of elements of it, '.
					'it '.
				') of set of elements whose (month_and_year of source release date of it = month_and_year of current date) of it '.

		
		') of set of bes fixlets whose (exists source release date of it)';
	*/
		
		'('.
			'(it as string) of (month_and_year of current date), '.
			'((it as string) of number of elements of item 2 of it), '.
			'(item 0 of it as string), '.
			'((item 0 of it - item 1 of it) as string), '.
			'(item 1 of it as string), '.
			'(if (item 0 of it = 0) then ("-99") else ((((item 0 of it as floating point - item 1 of it) * 100 / item 0 of it) as integer as string))) '.
		') of '.
		'( '.
			'number of results whose (exists first became relevant of it) of elements of it, '.
			'number of results whose (relevant flag of it) of elements of it, '.
			'it '.
		') of '.
		'of set of elements whose (month_and_year of source release date of it = month_and_year of current date) of it '.
		'of set of bes fixlets whose (exists source release date of it)';
	
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
?>