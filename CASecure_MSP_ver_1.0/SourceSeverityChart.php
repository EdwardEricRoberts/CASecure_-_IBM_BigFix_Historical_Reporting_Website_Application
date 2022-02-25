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



<script src="https://canvasjs.com/assets/script/jquery-1.11.1.min.js"></script>
<script src="https://canvasjs.com/assets/script/jquery.canvasjs.min.js"></script>
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
<link rel="stylesheet" type="text/css" href="MSSP_CSS.css">
<link rel="stylesheet" type="text/css" href="MSSP_CSS_bp.css">
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

<!--
<script src="jquery-3.3.1.min.js"></script>
-->
<!--
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
-->
<!--
<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.3.1.min.js"></script>
<script type="text/javascript" src="js/jquery.ajax-cross-origin.min.js"></script>
-->


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
	//document.getElementById("reportingNav").className = "current-menu-item";
	
	var chartHeight = 350;
	var chartWidth = 275;
	
	var curUser = "<?php echo $_SESSION['bigfixuser']; ?>"; //"APIAdmin";
	var password = "<?php echo $_SESSION['bigfixpassword']; ?>"; //"AllieCat7";
	var server = "<?php echo $_SESSION['bigfixserver']; ?>"; //"bigfix.internal.cassevern.com";
	// HTTP encode periods so as to not ruin the URL for the AJAX call
	server = server.replace(/\./g, "%2E");
	
	var defSite = "<?php echo $_SESSION['defaultsite']; ?>";
	var defComputerGroup = "<?php echo $_SESSION['defaultcomputergroup']; ?>";
	
	// AJAX call for the Source Severity Report
	var sourceSeverityProxy = "proxies/SourceSeverityReport.php?user=" + curUser + "&pass=" + password + "&serv=" + server + "&cg=" + defComputerGroup; //All Machines";
	xmlTableParser(sourceSeverityProxy, "#sourceSeverityTable");
	
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
				
				var severityArray = [0,0,0,0]
				var severityTable = document.getElementById('sourceSeverityTable');
				
				var severityRows = severityTable.rows, 
					severityRowCount = severityRows.length, 
					severityCells;
				var currentSeverity;
				
			
				
				
				for (var r = 1; r < severityRowCount; r++) {
					severityCells = severityRows[r].cells;
					currentSeverity = severityCells[4].innerHTML;
					if (currentSeverity == "Critical")
						severityArray[0] += 1;
					else if (currentSeverity == "Important" || currentSeverity == "High")
						severityArray[1] += 1;
					else if (currentSeverity == "Moderate" || currentSeverity == "Medium")
						severityArray[2] += 1;
					else if (currentSeverity == "Low")
						severityArray[3] += 1;
				}
				//alert(severityArray);
				
				totalSeverity = severityArray[0] + severityArray[1] + severityArray[2] + severityArray[3];
				
				var severityChartData = 
					[
						{category: 'Critical',  value: severityArray[0],  percent: Math.round((severityArray[0]/totalSeverity)*100),  color: '#FF0000'},
						{category: 'Important', value: severityArray[1],  percent: Math.round((severityArray[1]/totalSeverity)*100),  color: '#FF8000'},
						{category: 'Moderate',  value: severityArray[2],  percent: Math.round((severityArray[2]/totalSeverity)*100),  color: '#FFD500'},
						{category: 'Low',       value: severityArray[3],  percent: Math.round((severityArray[3]/totalSeverity)*100),  color: '#66B2FF'}
					];
					
				d3PieChartDesktop(severityChartData, "sourceSeverity", "Deployed Content Severity", '#sourceSeverityChart', '#legendDiv', 275, 300, 20);
				
				/*
				CanvasJS.addColorSet("severityShades",
					[//colorSet Array
						"#FF0000", 
						"#FF8000",
						"#FFD500",
						"#66B2FF"
					]
				);
				var chart2 = new CanvasJS.Chart("sourceSeverityChart", {
					colorSet: "severityShades",
					title:{
						text: "Source Severity",
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
							{ label: "Critical",  y: severityArray[0], name: "Critical"  },
							{ label: "Important",   y: severityArray[1], name: "Important"  },
							{ label: "Moderate",  y: severityArray[2], name: "Moderate"  },
							{ label: "Low",  y: severityArray[3], name: "Low"  },
						]
					}
					]
				});
				chart2.render();
				*/
				$("#sLoad").remove();
			}
		}
	}
</script>
<!--<a href="SourceSeverityReport.php" class="chartlink" target="_parent">see full report</a>-->
<div id="sourceSeverityChart" style="width:275; height:300;">
	<h4 id="sLoad"><div align="center">Source Severity Chart<br><img src="includes/loading.gif"></div></h4>
</div>

<table id="sourceSeverityTable" style="display:none">
	<th>Patch Name and KB Article</th><th>Re-mediated Systems</th><th>Patch Release Date</th><th>Source Severity</th>
</table>

