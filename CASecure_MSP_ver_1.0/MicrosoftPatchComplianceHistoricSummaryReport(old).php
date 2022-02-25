<html>
<head>
<title>CASecure >Microsoft Patch Compliance Historic Summary</title>

<script src="sorttable.js"></script>

<script src="BarGraph.js"></script>

<!-- <script type="text/javascript" src="/path/to/jquery.tablesorter.js"></script> -->

<link rel="stylesheet" type="text/css" href="TABLE_FORMAT_1.css">

<?php require 'includes/header.php'; ?>

<?php require 'includes/alert.php'; ?>

<?php require 'includes/navigation.php'; ?>

<style>

	
	/*
	table.sortable tbody {
		counter-reset: sortabletablescope;}
	table.sortable thead tr::before {
		content: "";
		display: table-cell; }
	table.sortable tbody tr::before {
		content: counter(sortabletablescope);
		counter-increment: sortabletablescope;
		display: table-cell; }
	*/
</style>

<span class="breadcrumbs"> <a href="Reporting.php">Reporting</a> > Microsoft Patch Compliance Historic Summary</span>
<br>
<div class="pagetitle">Microsoft Patch Compliance Historic Summary</div>

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
	var applicableCount = 0;
	var installedCount = 0;
	var outstandingCount = 0;
	var complianceTotal = "";
	
	// AJAX call for the Post Deployment Report
	var postDeploymentProxy = "database/MicrosoftPatchComplianceSummaryFetch.php?cg=" + computerGroup;
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
				
				
				var deploymentArray = [];
				var deploymentTable = document.getElementById('postDeploymentTable');
				var yAxis = 0, yArray = [], labelArray = [], timestamp, xAxis, xArray = [];
				var computerCount = deploymentTable.rows.length - 1;
				document.getElementById("computerCount").innerHTML = (computerCount == 1 ? "1 Record" : computerCount + " Records");
				
				var deploymentRows = deploymentTable.rows, 
					deploymentRowCount = deploymentRows.length,
					deploymentCells;
				
				for (var r = 1; r < deploymentRowCount; r++) {
					deploymentCells = deploymentRows[r].cells;
					yAxis = (parseFloat(deploymentCells[2].innerHTML)==0) ? 100 : Math.round(((parseFloat(deploymentCells[3].innerHTML))/(parseFloat(deploymentCells[2].innerHTML))) * 100);
					//yArray[r-1] = yAxis;
					
					timestamp = new Date(deploymentCells[1].innerHTML);
					xAxis = timestamp.getTime();
					xArray[r-1] = xAxis;
					
					deploymentArray[r-1] = {x: xAxis, y: yAxis};
					
				}
				//alert(xArray);
				//alert(deploymentArray);
				/*
				compliancePct = Math.round((deploymentArray[1]/deploymentArray[0])*100); 
				
				applicableCount = deploymentArray[0];
				installedCount = deploymentArray[1];
				outstandingCount = deploymentArray[2];
				complianceTotal = compliancePct + "%25";
				
				$("#postReportSummary").on("click", function() {
					var queryPost = new XMLHttpRequest();
					queryPost.open("POST", "database/MicrosoftPatchComplianceSummaryLog.php?appl=" + applicableCount + "&inst=" + installedCount + "&out=" + outstandingCount + "&comp=" + complianceTotal + "&cg=" + computerGroup, true);
					queryPost.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
					queryPost.onreadystatechange = function() {
						if (queryPost.readyState == 4 && queryPost.status == 200) {
							alert(queryPost.responseText);
						}
					}
					queryPost.send();
				});
				
				var myColor = ["#0000CC", "#006600", "#FF0000"];
				var myData = deploymentArray;
				var myCategories = ["Applicable Patches", "Installed Patches", "Outstanding Patches"];
				
				$('#chart').remove();
				$('#canvasDiv').append('<canvas id="chart" width="580" height="400" style = "border: 1px solid #333; display: none;"></canvas>');
				
				var chartColours = [];            // Chart colours (pulled from the HTML table)
				chartColours = myColor;
				
				var tableLegend;
				var cellid1 = "";
				var cellid2 = "";
				var cellid3 = "";
				var canvas;                       // The canvas element in the page
				
				var maxWidth = 0;
				
				canvas = document.getElementById('chart');
				canvas.style.display = "inline";
				tableLegend = document.getElementById('chartData');
				tableLegend.style.display = "table";
				
		 		var tempContext = canvas.getContext('2d');
				//tempContext.clearRect(0, 0, canvas.width, canvas.height);
				//tempContext.beginPath();
				
				tableLegend.innerHTML = '<tr><th id="header1">Category</th><th id="header2">Patches</th><th id="header3">Percent</th></tr>';
				
		 		for (var i = 0; i < myData.length; i++) {
		 			// Extract and store the cell colour
		 			//chartColours[i] = randomColor();	
		 			
		 			tableLegend.innerHTML += '<tr><td id="row' + i + 'cell0" style = "fontWeight: bolder"><span style="display:inline-block;width:13px;margin-bottom:3px;background-color: ' + chartColours[i] + ';"> &nbsp;</span><b><span> &nbsp;' + myCategories[i] + '</span></b></td><td id="row' + i + 'cell1" style = "fontWeight: bolder;"><b><span style="float:right;">' + myData[i] + '</span></b></td><td id="row' + i + 'cell2"><b><span style="float: right;">' + ((myData[i]/myData[0])*100).toFixed(2).toString() + '%</span></b></td></tr>';
		 			
		 			if (tempContext.measureText(myCategories[i]).width > maxWidth) { 
		 				maxWidth = tempContext.measureText(myCategories[i]).width;
		 			}
		 			
		 			cellid1 = "row" + i + "cell0";
		 			cellid2 = "row" + i + "cell1";
		 			cellid3 = "row" + i + "cell2";
		 			
		 			document.getElementById(cellid1).style.color = chartColours[i];
		 			document.getElementById(cellid2).style.color = chartColours[i];
		 			document.getElementById(cellid3).style.color = chartColours[i];
		 		}
				
				tableLegend.width = maxWidth + 280;
				
				eventWindowLoaded();
				
				$("#pLoad").remove();
				
				function eventWindowLoaded() {
					var c = document.getElementById('chart');//graph1
					if (c && c.getContext) {
						graph1 = new BarGraph(c);
						graph1.vals = myData; //[6300,200,5520,3760,9,320,819,1308,405,-2101,640,1999];
						graph1.cats = myCategories; //["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
						graph1.colors = myColor;
						graph1.title = "Patches per Categtory";
						graph1.bgcol1 = "#eef";
						graph1.bgcol2 = "#77f";
						graph1.effect = "twang";
						graph1.drawGraph();
					}
				}
				*/
				
				//
				//CanvasJS.addColorSet("deploymentShades",
				//	[//colorSet Array
				//		"#0000CC",
				//		"#006600",
				//		"#FF0000"              
				//	]
				//);
				/*
				var chart = new CanvasJS.Chart("postDeploymentChart", {
					//colorSet: "deploymentShades",
					title:{
						text: "Patch Compliance Summary",
						padding: 5
					},
					height: chartHeight,
					width: chartWidth,
					backgroundColor: "#ebedf2",
					axisX: {
						title: "Date",
						interval:2,
						intervalType:"day",
						valueFormatString: "MMM D, YYYY, hh:mm tt",
						labelAngle: -70
					}
					data: [              
						{
							// Change type to "line", "doughnut", "splineArea", etc.
							type: "line",
							indexLabelPlacement: "outside", 
							//showInLegend: true,
							dataPoints: deploymentArray
						}
					]
				});
				chart.render();
				//
				$("#pLoad").remove();
				*/
				/*
				function sortTable(n, tableName) {
					var table, rows, switching, i, x, y, shouldSwitch;
					table = document.getElementById(tableName);
					switching = true;
					while (switching) {
						switching = false;
						rows = table.getElementsByTagName("TR");
						for (i = 1; i < (rows.length - 1); i++) {
							shouldSwitch = false;
							x = rows[i].getElementsByTagName("TD")[n];
							y = rows[i + 1].getElementsByTagName("TD")[n];
							if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
								shouldSwitch= true;
								break;
							}
						}
						if (shouldSwitch) {
							rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
							switching = true;
						}
					}
				}
				*/
				var newTableObject = document.getElementById("postDeploymentTable");
				
				sorttable.makeSortable(newTableObject);
			/*
				$(document).ready(function() 
					{ 
						$("#postDeploymentTable").tablesorter(); 
					}
				);
			*/
			/*	
				var prevID = "";
				var currentID = "";
				var destTable = document.getElementById("postDeploymentTable");
				var downTable = document.getElementById("postDeploymentTableFinal");
				var origTable = document.getElementById("compliance"),
					origRows = origTable.rows, 
					origRowcount = origRows.length, r,
					origCells, origCellcount, c, origCell;
				var destRowNum = 0;
				for (r = 0; r < origRowcount; r++) {
					origCells = origRows[r].cells;
					currentID = origCells[1].innerHTML;
					origCellcount = origCells.length;
					if (currentID == prevID) {
						destTable.rows[destTable.rows.length-1].cells[6].innerHTML = parseInt(destTable.rows[destTable.rows.length-1].cells[6].innerHTML) + parseInt(origTable.rows[r].cells[6].innerHTML);
						destTable.rows[destTable.rows.length-1].cells[7].innerHTML = parseInt(destTable.rows[destTable.rows.length-1].cells[7].innerHTML) + parseInt(origTable.rows[r].cells[7].innerHTML);
						destTable.rows[destTable.rows.length-1].cells[8].innerHTML = parseInt(destTable.rows[destTable.rows.length-1].cells[8].innerHTML) + parseInt(origTable.rows[r].cells[8].innerHTML);
						destTable.rows[destTable.rows.length-1].cells[9].innerHTML =  complianceColor(Math.round((parseInt(destTable.rows[destTable.rows.length-1].cells[7].innerHTML) / parseInt(destTable.rows[destTable.rows.length-1].cells[6].innerHTML)) * 100)); 

					} else {
						var destRow = destTable.insertRow(destTable.rows.length);
						for (c = 0; c < origCellcount; c++) {
							origCell = origCells[c];
							var destCell = destRow.insertCell(c);
							switch (c){
								case 0: // Column with Fixlet ID and Computer ID, this is hidden
										destCell.innerHTML = origCell.innerHTML;
										destCell.className = "cellHidden";
										break;
								case 1: // Column with Computer ID, this is hidden
										destCell.innerHTML = origCell.innerHTML;
										destCell.className = "cellHidden";
										break;
								case 2: // Column Computer
										destCell.innerHTML = origCell.innerHTML;	
										alert(destCell.innerHTML);
										break;
								case 3: // Column Operating System
										destCell.innerHTML = origCell.innerHTML;				
										break;
								case 4: // Column IP Address
										destCell.innerHTML = origCell.innerHTML;				
										break;
								case 5: // Column Last Report Time
										destCell.innerHTML = origCell.innerHTML;				
										break;
								case 6: // 6 is Applicable Fixlets, it is right justified
								case 7: // 7 is Installed Fixlets
								case 8: // 8 is Outstanding Fixlets
								case 9: // 9 is Compliance percentage
										destCell.innerHTML = origCell.innerHTML;
										destCell.align = "right";
										destCell.className = "count" + c;
										break;
								default:
							}
						}
						var destCell = destRow.insertCell(origCellcount);
						destCell.align = "right";
						destCell.innerHTML = complianceColor(parseInt(destRow.cells[7].innerHTML)*100);

						destRowNum++;
					}
					prevID = origCells[1].innerHTML;
				}
				
				function complianceColor(val) {
					if (val >= 90) {
						return '<span style="color:#008800;">' + val + '%</span>';
					} else if (val >= 70 && val < 90) {
						return '<span style="color:#15428b;">' + val + '%</span>';
					} else if (val <= 20 && val >= 0) {
						return '<span style="color:#cc3333;">' + val + '%</span>';
					} else if (val == -99) {
						return '<span style="color:#15428b;">--</b></span>';
					} else {
						return '<span style="color:orange;">' + val + '%</span>';
					}
				}
		*/
					// Adding a little dynamic element to the report by
	// animating the compliance percentage number
				function animateValue(id, start, end, duration) {
					var obj = document.getElementById(id);
					var range = end - start;
					var minTimer = 50;
					var stepTime = Math.abs(Math.floor(duration / range));
					stepTime = Math.max(stepTime, minTimer);
					var startTime = new Date().getTime();
					var endTime = startTime + duration;
					var timer;
					
					function run() {
						var now = new Date().getTime();
						var remaining = Math.max((endTime - now) / duration, 0);
						var value = Math.round(end - (remaining * range));
						obj.innerHTML = value + "&#37;";
						if (value == end) {
							clearInterval(timer);
						}
					}
	
					timer = setInterval(run, stepTime);
					run();
				}
				animateValue("complianceValue", 0, compliancePct, 2000);
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
				rowHTML = "<li><a href='MicrosoftPatchComplianceReport.php?cg=" + xmlDoc.getElementsByTagName("Answer")[i].childNodes[0].nodeValue + "'>" + xmlDoc.getElementsByTagName("Answer")[i].childNodes[0].nodeValue + "</a></li>";
				$("#computerGroupList").append(rowHTML);
			}
		}
	}
	
</script>
<br>
<hr />
<nav id="primary_nav_wrap">
	<ul>
		<li>Select Computer Group
			<ul id="computerGroupList">
			</ul>
		</li>
		<!--<li>Current Computer Group: <span id="currentGroup"><?php //echo $_GET['cg']; ?></span></li>-->
		<!--<li><button id="postReportSummary" onmouseover="" style="cursor:pointer;">Post Report Summary</button></li>-->
	</ul>
</nav>
<div style="align: right;">
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	Current Computer Group: 
	<span id="currentGroup">
		<b>
			<?php 
				if ($_GET['cg'] == "All Machines") 
					{echo "";} 
				else 
					{echo $_GET['cg'];} 
			?>
		</b>
	</span>
</div>
<hr />
<div id="container" style="min-width:1000px; max-width:1000px; margin-bottom: 30px; position: relative; height:300">
	<div id="pLoad">
		<br><img src="includes/loading.gif">
	</div>
	<div id="canvasDiv" style="width: 60%; position: absolute; top: 0px; left: 0px">
		<canvas id="chart" width="900" height="600" style = "display: none;"></canvas>
	</div>
	<div style="width: 40%; position: absolute; top: 0px; right: 0px">
		<div style = "position: relative">
			<div align="right" style="margin-top: 5px; width: 25%; position: absolute; top:0px; left: 90px">
				<div><p style="font-family: Arial, Helvetica, sans-serif; font-size: 40px;">Compliance</p><br></div>
				<div align="right" style="margin-top:-130px;"><p style="font-family: Arial, Helvetica, sans-serif; font-size: 80px;" id="complianceValue">0</p></div>
				<div align="right" style="margin-top:-70px;"><p style="font-family: Arial, Helvetica, sans-serif; font-size: 20px; white-space: nowrap; margin-top: 10px;" id="computerCount">Systems</p><br></div>
			</div>
		</div>
		<br>
		<table id="chartData" style = "display: none; position: absolute; top: 230px; left: 10px;">
			<tr>
				<th>Category</th><th>Patches</th><th>Percent</th>
			</tr>
		</table>
	</div>

<br>
<div>&nbsp;</div>
<!--
<div id="postDeploymentChart" style="width:60%; height: 600;">
	<canvas id="chart" width="900" height="600" style = "display: none;"></canvas>
	<h4 id="pLoad">Loading...</h4>
</div>
-->

<div style=" min-width:1000px; max-width:1000px; margin-top: 8px; position: absolute; top: 490" id="mainDiv">
	<table id="postDeploymentTable" class="sortable">
		<th>Log ID</th><th>Timestamp</th><th>Applicable Patches</th><th>Installed Patches</th><th>Outstanding Patches</th><th>Compliance</th><th>Computer Group</th><th>Logged By BigFix User</th>
	</table>
<!--	
	<table id="postDeploymentTableFinal">
		<th>Computer Name</th><th>Operating System</th><th>IP Address</th><th>Last Report Time</th><th>Applicable Patches</th><th>Installed Patches</th><th>Outstanding Patches</th><th>Compliance</th>
	</table>
-->
</div>
</div>
<?php require 'includes/footer.php'; ?>