<html>
<head>
<title>CASecure >Installed Software Search Report</title>

<link rel="stylesheet" type="text/css" href="TABLE_FORMAT_1.css">

<?php require 'includes/header.php'; ?>

<?php require 'includes/alert.php'; ?>

<?php require 'includes/navigation.php'; ?>

<span class="breadcrumbs"> <a href="Reporting.php">Reporting</a> > Installed Software Search Report</span>
<br>
<div class="pagetitle">Installed Software Search Report</div>

<script>
	document.getElementById("reportingNav").className = "current-menu-item";
	
	var chartHeight = 250;
	var chartWidth = 400;
	
	var curUser = "APIAdmin";
	var password = "AllieCat7";
	var server = "bigfix.internal.cassevern.com";
	// HTTP encode periods so as to not ruin the URL for the AJAX call
	server = server.replace(/\./g, "%2E");
	
	var computerGroup = "<?php echo $_GET['cg']; ?>";
	
	// AJAX call for the Windows Update Status Report
	var unspecifiedContentProxy = "proxies/SoftwareComputerSearchReport.php?user=" + curUser + "&pass=" + password + "&serv=" + server + "&cg=" + computerGroup;
	xmlTableParser(unspecifiedContentProxy, "#unspecifiedContentTable");
	
	// Function performs an AJAX call to a designated API proxy and collects the data into an HTML table
	function xmlTableParser(proxyURL, tableID) {
		var request = new XMLHttpRequest();
		request.open("GET", proxyURL, true);
		request.send();
		request.onreadystatechange = function() {
			if ((this.readyState === 4) && (this.status === 200)) {
				// Convert the XML document to a string
				xmlString = this.responseText.toString();
				// Eliminate the Meta-Query from the <Query> tag in the XML results as it can potentially cause issues when reading the XML code
				//var badQuery = xmlString.substring((parseInt(xmlString.search("Resource"))-1), (parseInt(xmlString.search('Result'))-5));
				//xmlString = xmlString.replace(badQuery, '');
				// Convert string of XML code back into an XML document
				var parser = new DOMParser();
				var xmlDoc = parser.parseFromString(xmlString,"text/xml");
				// Collect the number of rows (<Tuple> tags) and columns (<Answer> tags per <Tuple> tag) of data from the XML document
				var tupleCount = xmlDoc.getElementsByTagName("Tuple").length;
				var columnCount = xmlDoc.getElementsByTagName("Answer").length / tupleCount;
				// Build the HTML Table's contents from the data in the XML document
				var rowHTML = "";
				for (var i = 0; i < tupleCount; i++) {
					rowHTML = '<tr>';
					for (var j = 0; j < columnCount; j++) {
							rowHTML += '<td nowrap>' + xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue + '</td>';
					}
					rowHTML += '</tr>';
					$(tableID).append(rowHTML);
				}
				/*
				var applicationSoftwareCountArray = [0,0];
				var vulnerabilityTable = document.getElementById('unspecifiedContentTable');
				
				var vulnerabilityRows = vulnerabilityTable.rows, 
					vulnerabilityRowCount = vulnerabilityRows.length, 
					vulnerabilityCells;
				
				for (var r = 1; r < vulnerabilityRowCount; r++) {
					vulnerabilityCells = vulnerabilityRows[r].cells;
					applicationSoftwareCountArray[0] += parseInt(vulnerabilityCells[6].innerHTML);
					applicationSoftwareCountArray[1] += parseInt(vulnerabilityCells[7].innerHTML);
				}
				//alert(severeVulnerabilityArray);
				
				$("#postReportSummary").on("click", function() {
					var queryPost = new XMLHttpRequest();
					queryPost.open("POST", "database/WindowsUpdateStatusSummaryLog.php?dis=" +  applicationSoftwareCountArray[0] + "&en=" + applicationSoftwareCountArray[1] + "&cg=" + computerGroup, true);
					queryPost.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
					queryPost.onreadystatechange = function() {
						if (queryPost.readyState == 4 && queryPost.status == 200) {
							alert(queryPost.responseText);
						}
					}
					queryPost.send();
				});
				*/
				$("#wLoad").remove();
			}
		}
	}
	
	var compGroup = new XMLHttpRequest();
	compGroup.open("GET", "proxies/ComputerGroupsDropDownList.php?user=" + curUser + "&pass=" + password + "&serv=" + server, true);
	compGroup.send();
	compGroup.onreadystatechange = function() {
		if ((this.readyState === 4) && (this.status === 200)) {
			xmlCompGrpList = this.responseText.toString();
			var badQuery = xmlCompGrpList.substring((parseInt(xmlCompGrpList.search("Resource"))-1), (parseInt(xmlCompGrpList.search('Result'))-5));
			xmlCompGrpList = xmlCompGrpList.replace(badQuery, '');
			var parser = new DOMParser();
			var xmlDoc = parser.parseFromString(xmlCompGrpList,"text/xml");
			var rowCount = xmlDoc.getElementsByTagName("Answer").length;
			var rowHTML = "";
			for (var i = 0; i < rowCount; i++) {
				rowHTML = "<li><a href='UnspecifiedContentReport.php?cg=" + xmlDoc.getElementsByTagName("Answer")[i].childNodes[0].nodeValue + "'>" + xmlDoc.getElementsByTagName("Answer")[i].childNodes[0].nodeValue + "</a></li>";
				$("#computerGroupList").append(rowHTML);
			}
		}
	}
	
</script>

<br>
<hr />
<nav id="primary_nav_wrap">
	<ul>
		<li>Computer Group
			<ul id="computerGroupList">
			</ul>
		</li>
		<li>Current Computer Group: <span id="currentGroup"><?php echo $_GET['cg']; ?></span></li>
		<!--<li><button id="postReportSummary" onmouseover="" style="cursor:pointer;">Post Report Summary</button></li>-->
	</ul>
</nav>
<hr />

	<div id="wLoad">
		<h4>Loading...</h4>
		<br><img src="includes/loading.gif">
	</div>

<table id="unspecifiedContentTable">
	<th>Patch ID</th><th>Patch Name and KB Article</th><th>Source Release Date</th><th>Re-mediated Systems</th>
</table>

<?php require 'includes/footer.php'; ?>