<?php
require_once './includes/authenticate.php';
?>

<html>
<head>
	<title>CASecure >Source Severity Report</title>
	
	<link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
	<script src="http://d3js.org/d3.v5.min.js"></script>
	<!--<script src="d3PieChartDesktop.js"></script>-->
	
	<script src="<?php echo 'd3PieChartDesktop.js?v='.filemtime('d3PieChartDesktop.js'); ?>"></script>
	
	<link rel="stylesheet" type="text/css" href="TABLE_FORMAT_1.css">
	
	<script src="https://canvasjs.com/assets/script/jquery-1.11.1.min.js"></script>
	<script src="https://canvasjs.com/assets/script/jquery.canvasjs.min.js"></script>
	<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
	<link rel="stylesheet" type="text/css" href="MSSP_CSS.css">
	
	<!--<link rel="stylesheet" type="text/css" href="MSSP_CSS_bp.css">-->
	<link rel="stylesheet" type="text/css" href="css/blueprint/screen.css" media="screen, projection">
	<link rel="stylesheet" type="text/css" href="css/blueprint/print.css" media="print">
	<link rel="stylesheet" type="text/css" href="css/blueprint/ie.css" media="screen, projection">
	
	<!-- <link href="includes/jquery.modal/jquery.modal.css" type="text/css" rel="stylesheet" /> -->
	<script src="http://code.jquery.com/jquery-latest.min.js"></script>
	<!-- <script src="includes/jquery.modal/jquery.modal.min.js"></script>  -->
	
	<!-- Remember to include jQuery :) -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0/jquery.min.js"></script>

	<!-- jQuery Modal -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css" />
</head>

<body onload="loadReports()">
	<div class="container">
	
	<div class="prepend-6 span-2 last" style="margin-top:-5px;">
		
		<!-- Modal Box -->
		
		
		<!-- Link to open the modal -->
		
		
	</div>
	
	<script>
		var chartHeight = 300;
		var chartWidth = 275;
		
		var curUser = "<?php echo $_SESSION['bigfixuser']; ?>"; //"APIAdmin";
		var password = "<?php echo $_SESSION['bigfixpassword']; ?>"; //"AllieCat7";
		var server = "<?php echo $_SESSION['bigfixserver']; ?>"; //"bigfix.internal.cassevern.com";
		// HTTP encode periods so as to not ruin the URL for the AJAX call
		server = server.replace(/\./g, "%2E");
		
		var defSite = "<?php echo $_SESSION['defaultsite']; ?>";
		var defComputerGroup = "<?php echo $_SESSION['defaultcomputergroup']; ?>";
		
		// AJAX call for the Windows Update Status Report
		var windowsUpdateStatusProxy = "proxies/WindowsUpdateStatusReport.php?user=" + curUser + "&pass=" + password + "&serv=" + server + "&cg=" + defComputerGroup;//"All%20Machines";
		xmlTableParser(windowsUpdateStatusProxy, "#windowsUpdateStatusTable");
		
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
							rowHTML += '<td nowrap>' + xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue + '</td>';
						}
						rowHTML += '</tr>';
						$(tableID).append(rowHTML);
					}
					
					var windowsUpdatesArray = [0,0];
					var windowsUpdatesTable = document.getElementById('windowsUpdateStatusTable');
					
					var windowsUpdatesRows = windowsUpdatesTable.rows, 
						windowsUpdatesRowCount = windowsUpdatesRows.length, 
						windowsUpdatesCells;
					var currentUpdateState;
					
					for (var r = 1; r < windowsUpdatesRowCount; r++) {
						windowsUpdatesCells = windowsUpdatesRows[r].cells;
						currentUpdateState = windowsUpdatesCells[5].innerHTML;
						if (currentUpdateState == "Enabled")
							windowsUpdatesArray[1] += 1;
						else
							windowsUpdatesArray[0] += 1;
					}
					
					var computerCount = windowsUpdatesTable.rows.length - 1;
					
					var complianceChartData = 
						[
							{category: 'Auto Updates Disabled', value: windowsUpdatesArray[0], percent: Math.round((windowsUpdatesArray[0]/computerCount)*100, 5), color: '#006600'},
							{category: 'Auto Updates Enabled',  value: windowsUpdatesArray[1], percent: Math.round((windowsUpdatesArray[1]/computerCount)*100, 5), color: '#FF0000'}
						];
					
					d3PieChartDesktop(complianceChartData, 'windowsUpdateStatus', 'Windows Update Status', '#windowsStatusUpdateChart', '#legendDiv', 275, 300, 20);
					
					/*
					CanvasJS.addColorSet("statusShades",
						[//colorSet Array
							"#006600", 
							"#FF0000"
						]
					);
					var chart3 = new CanvasJS.Chart("windowsStatusUpdateChart", {
						colorSet: "statusShades",
						title:{
							text: "Status of Windows Updates",
							//padding: 5
						},
						legend: {
							horizontalAlign: "center",
							verticalAlign: "bottom",
						},
						height: chartHeight,
						width: chartWidth,
						backgroundColor: "#ebedf2",
						data: [              
						{
							// Change type to "line", "doughnut", "splineArea", etc. "column"
							type: "pie",
							startAngle: 270,
							indexLabelPlacement: "outside", 
							showInLegend: true,
							dataPoints: [
								{ label: "Disabled",  y: windowsUpdatesArray[0], name: "Disabled"  },
								{ label: "Enabled",   y: windowsUpdatesArray[1], name: "Enabled"  },
							]
						}
						]
					});
					chart3.render();
					*/
					$("#wLoad").remove();
				}
			}
		}
	</script>
	
	<div id="windowsStatusUpdateChart" style="width:400; height: 300;">
		<h4 id="wLoad"><div align="center">Windows Status Update Chart<br><img src="includes/loading.gif"></div></h4>
	</div>
	
	<table id="windowsUpdateStatusTable" style="display:none">
		<th>Computer Fixlet ID</th><th>Computer ID</th><th>Computer Name</th><th>Operating System</th><th>IP Address</th><th>Automatic Update State</th><th>Automatic Update Method</th><th>No Reboot with Logged on User</th>
	</table>

<?php require 'includes/footer.php'; ?>