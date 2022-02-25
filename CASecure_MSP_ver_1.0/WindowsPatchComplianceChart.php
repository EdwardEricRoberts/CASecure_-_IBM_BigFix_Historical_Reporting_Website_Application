<?php
require_once './includes/authenticate.php';
?>

<html>
<head>
	<title>CASecure >Compliance of Windows Patches</title>
	
	<link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
	<script src="http://d3js.org/d3.v5.min.js"></script>
	<script src="d3BarChartDesktop.js"></script>
	
	<link rel="stylesheet" type="text/css" href="TABLE_FORMAT_1.css">
	
	<script src="https://canvasjs.com/assets/script/jquery-1.11.1.min.js"></script>
	<script src="https://canvasjs.com/assets/script/jquery.canvasjs.min.js"></script>
	<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
	<link rel="stylesheet" type="text/css" href="MSSP_CSS.css">
	<link rel="stylesheet" type="text/css" href="MSSP_CSS_bp.css">
	<link rel="stylesheet" type="text/css" href="css/blueprint/screen.css" media="screen, projection">
	<link rel="stylesheet" type="text/css" href="css/blueprint/print.css" media="print">
	<link rel="stylesheet" type="text/css" href="css/blueprint/ie.css" media="screen, projection">
	
<script src="http://code.jquery.com/jquery-latest.min.js"></script>

<!-- Remember to include jQuery :) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0/jquery.min.js"></script>

<!-- jQuery Modal -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css" />

<style>
	.chartlink {
	z-index: 0;
	position: absolute;
	margin-left: 100px;
	}
</style>

</head>
<body onload="loadReports()">
<div class="container">

<div class="prepend-6 span-2 last" style="margin-top:-5px;">

<!-- Modal Box -->


<!-- Link to open the modal -->


</div>
<script>
 $("#fade").modal({
  fadeDuration: 100,
  fadeDelay: 0.50
});
</script>
<script>
	var chartHeight = 300;
	var chartWidth = 420;
	
	var curUser = "<?php echo $_SESSION['bigfixuser']; ?>"; //"APIAdmin";
	var password = "<?php echo $_SESSION['bigfixpassword']; ?>"; //"AllieCat7";
	var server = "<?php echo $_SESSION['bigfixserver']; ?>"; //"bigfix.internal.cassevern.com";
	// HTTP encode periods so as to not ruin the URL for the AJAX call
	server = server.replace(/\./g, "%2E");
	
	var defSite = "<?php echo $_SESSION['defaultsite']; ?>";
	var defComputerGroup = "<?php echo $_SESSION['defaultcomputergroup']; ?>";
	
	// AJAX call for the Post Deployment Report
	var postDeploymentProxy = "proxies/MicrosoftPatchComplianceReport.php?user=" + curUser + "&pass=" + password + "&serv=" + server + "&cg=" + defComputerGroup; //All Machines";
	xmlTableParser(postDeploymentProxy, "#postDeploymentTable");
	
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
				var badQuery = xmlString.substring((parseInt(xmlString.search("Resource"))-1), (parseInt(xmlString.search('Result'))-5));
				xmlString = xmlString.replace(badQuery, '');
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
						if (j == 0 || j == 4) {}
						else {
							rowHTML += '<td nowrap>' + xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue + '</td>';
						}
					}
					rowHTML += '</tr>';
					$(tableID).append(rowHTML);
				}
				
				function secondTuesday(month, year) {
					var d = new Date(year, month, 1);
					d.setDate(d.getDate() + (9 - d.getDay()) % 7); //9
					d.setDate(d.getDate() + 7);
					return new Date(d.getTime());
				}
				
				function nextThursday(d) {
					d.setDate(d.getDate() + 2);
					return new Date(d.getTime());
				}
				
				function previousMonth(currentMonth) {
					if (currentMonth == 0) {
						return 11;
					}
					else {
						return currentMonth - 1;
					}
				}
				
				function checkYear(previousMonth, currentYear) {
					if (previousMonth == 11) {
						return currentYear - 1;
					}
					else {
						return currentYear;
					}
				}
				
				var thisMonth = new Date().getMonth();
				var thisYear = new Date().getFullYear();
				var lastMonth = previousMonth(thisMonth);
				var yearOfLastMonth = checkYear(lastMonth, thisYear);
				var currentDate = new Date();
				var patchTuesdayThisMonth = secondTuesday(thisMonth, thisYear); 
				var followingThursdayThisMonth = nextThursday(secondTuesday(thisMonth, thisYear));
				var followingThursdayLastMonth = nextThursday(secondTuesday(lastMonth, yearOfLastMonth));
				
				// Parse Post Deployment HTML table data into JavaScript Array to create charts with
				var deploymentArray = [0,0,0];
				var deploymentTable = document.getElementById('postDeploymentTable');
				
				var deploymentRows = deploymentTable.rows, 
					deploymentRowCount = deploymentRows.length,
					deploymentCells, lastPatchDay;
				
				for (var r = 1; r < deploymentRowCount; r++) {
				    deploymentCells = deploymentRows[r].cells;
					
					lastPatchDay = new Date((deploymentCells[3].innerHTML).split("<br>")[0] + " " + (deploymentCells[3].innerHTML).split("<br>")[1])
					
					if (currentDate >= patchTuesdayThisMonth && currentDate < followingThursdayThisMonth) {
						deploymentCells[7].innerHTML = "Unknown";
						for (var i = 0; i < deploymentCells.length; i++) {
							deploymentCells[i].style.color = "red";
							//deploymentCells[i].style.backgroundColor = "red";
						}
					}
					else if (currentDate < patchTuesdayThisMonth) {
						if (lastPatchDay < followingThursdayLastMonth) {
							deploymentCells[7].innerHTML = "Unknown";
							for (var i = 0; i < deploymentCells.length; i++) {
								deploymentCells[i].style.color = "red";
								//deploymentCells[i].style.backgroundColor = "red";
							}
						}
						else {
							deploymentArray[0] += isNaN(deploymentCells[4].innerHTML) ? 0 : parseInt(deploymentCells[4].innerHTML);
							deploymentArray[1] += isNaN(deploymentCells[5].innerHTML) ? 0 : parseInt(deploymentCells[5].innerHTML);
							deploymentArray[2] += isNaN(deploymentCells[6].innerHTML) ? 0 : parseInt(deploymentCells[6].innerHTML);
						}
					}
					else if (currentDate >= followingThursdayThisMonth) {
						if (lastPatchDay < followingThursdayThisMonth) {
							deploymentCells[7].innerHTML = "Unknown";
							for (var i = 0; i < deploymentCells.length; i++) {
								deploymentCells[i].style.color = "red";
								//deploymentCells[i].style.backgroundColor = "red";
							}
						}
						else {
							deploymentArray[0] += isNaN(deploymentCells[4].innerHTML) ? 0 : parseInt(deploymentCells[4].innerHTML);
							deploymentArray[1] += isNaN(deploymentCells[5].innerHTML) ? 0 : parseInt(deploymentCells[5].innerHTML);
							deploymentArray[2] += isNaN(deploymentCells[6].innerHTML) ? 0 : parseInt(deploymentCells[6].innerHTML);
						}
					}
				}
				/*
				for (var r = 1; r < deploymentRowCount; r++) {
					deploymentCells = deploymentRows[r].cells;
					deploymentArray[0] += isNaN(deploymentCells[6].innerHTML) ? 0 : parseInt(deploymentCells[6].innerHTML);
					deploymentArray[1] += isNaN(deploymentCells[7].innerHTML) ? 0 : parseInt(deploymentCells[7].innerHTML);
					deploymentArray[2] += isNaN(deploymentCells[8].innerHTML) ? 0 : parseInt(deploymentCells[8].innerHTML);;
				}
				*/
				applicableCount = deploymentArray[0];
				installedCount = deploymentArray[1];
				outstandingCount = deploymentArray[2];
				
				var complianceChartData = 
					[
						{category: 'Applicable Patches',  value: applicableCount,  percent: Math.round((applicableCount/applicableCount)*100, 5),  color: '#0000CC'},
						{category: 'Installed Patches',   value: installedCount,   percent: Math.round((installedCount/applicableCount)*100, 5),   color: '#006600'},
						{category: 'Outstanding Patches', value: outstandingCount, percent: Math.round((outstandingCount/applicableCount)*100, 5), color: '#FF0000'}
					];
				
				d3BarChartDesktop(complianceChartData, 'microsoftPatchCompliance', 'Microsoft Patch Compliance', '#postDeploymentChart', '#legendDiv', 420, 300, 45);
				
				/*
				CanvasJS.addColorSet("deploymentShades",
					[//colorSet Array
						"#0000CC",
						"#006600",
						"#FF0000"              
					]
				);
				var chart = new CanvasJS.Chart("postDeploymentChart", {
					colorSet: "deploymentShades",
					title:{
						text: "Compliance of Windows Patches",
						padding: 5
					},
					height: chartHeight,
					width: chartWidth,
					backgroundColor: "#ebedf2",
					data: [              
					{
						// Change type to "line", "doughnut", "splineArea", etc.
						type: "column",
						indexLabelPlacement: "outside", 
						//showInLegend: true,
						dataPoints: [
							{ label: "Applicable Patches",  y: deploymentArray[0], name: "Applicable"  },
							{ label: "Installed Patches",   y: deploymentArray[1], name: "Installed"  },
							{ label: "Outstanding Patches",  y: deploymentArray[2], name: "Outstanding"  },
						]
					}
					]
				});
				chart.render();
				*/
				$("#pLoad").remove();
				$("#postDeploymentChart").append('<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><a href="MicrosoftPatchComplianceReport.php">See Full Report</a>');
			}
		}
	}

</script>

<br>

<div class="span-24">		
	<div id="table-wrapper" style="display:none">
		<!-- <h3>Inventory</h3> -->
		<div id="table-scroll">
			<font size="1">
				<table id="inventoryTable"">
					<!-- <th nowrap style="align:left; ">Computer ID</th> -->
					<th nowrap style="align:left;">Computer Name</th>
					<th nowrap style="align:left;">Operating System</th>
					<th nowrap style="align:left;">IP Addresses</th>
					
					<th nowrap style="align:left;">Last Report Time</th>
				</table>
			</font>
		</div>
	</div>
	
	<div id="postDeploymentChart" style="width:400; height: 300; margin-top:-20px;">
		<h4 id="pLoad"><div align="center">Windows Patch Compliance Chart<br><img src="includes/loading.gif"></div></h4>
	<!--<a href="WindowsPatchComplianceReport.php" class="chartlink" target="_parent">see full report</a>-->
	</div>
</div>

<table id="postDeploymentTable" style="display:none;">
	<th>Computer Fixlet ID</th><th>Computer ID</th><th>Computer Name</th><th>Operating System</th><th>IP Address</th><th>Last Report Time</th><th>Applicable Patches</th><th>Installed Patches</th><th>Outstanding Patches</th>
</table>

<?php require 'includes/footer.php'; ?>