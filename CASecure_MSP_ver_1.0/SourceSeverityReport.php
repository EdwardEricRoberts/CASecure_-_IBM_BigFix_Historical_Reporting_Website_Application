<?php
require_once './includes/authenticate.php';
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
      <title>Source Severity Report</title>



	  <?php require 'includes/headerRevised_2.php'; ?>
      <?php require 'includes/navigationRevised.php'; ?>

<!-- start breadcrumbs -->                
	  <div class="span-24">
         <div style="background-color:#cacaca; width:100%; margin-top:2px; height:23px; line-height:23px;"> 
            <span style="text-align:center;">
               <img src="includes/breadcrumbs_start.jpg" style="height:23px;">
               <span style="z-index:0; position:absolute;"><a href="Reporting.php" class="breadcrumblink">Reporting</a> &nbsp;&raquo;&nbsp; Deployed Content Source Severity Report</span>
            </span>
         </div>
      </div>
<!-- end breadcrumbs -->
 <br>
      <div class="span-24 last">
	     <div class="pagetitle2" style="margin-bottom:20px; margin-left:10px; float:left">Deployed Content Source Severity Report<br>
		 </div>	  
	  </div>

<style>
			#container {
				width: 400px;
				margin: 0 auto;
			}
			#chart, #chartData {
 				border: 1px solid lightgrey;
 				background: #ebedf2 url("images/gradient.png") repeat-x 0 0;
			}
			#chart {
 				display: block;
 				margin: 0 0 10px 0;
 				float: left;
 				cursor: pointer;
			}
			#chartData {
 				width: 360px;
 				margin: 115px 0px 0 40px;
 				float: left;
 			//	border-collapse: collapse;
 			//	box-shadow: 0 0 1em rgba(0, 0, 0, 0.5);
 			//	-moz-box-shadow: 0 0 1em rgba(0, 0, 0, 0.5);
 			//	-webkit-box-shadow: 0 0 1em rgba(0, 0, 0, 0.5);
 			//	background-position: 0 -100px;
			}
/*			#chartData th, #chartData td {
 				padding: 0.5em;
 				border: 1px dotted #666;
 				text-align: left;
			}
			#chartData th {
 				border-bottom: 2px solid #333;
 				text-transform: uppercase;
			}
			#chartData td {
 				cursor: pointer;
			}
*/			#chartData td.highlight {
 				background: #e8e8e8;
			}
/*			#chartData tr:hover td {
 				background: #f0f0f0;
			}
*/	
	
</style>

<script>
	document.getElementById("reportingNav").className = "current-menu-item";
	
	var chartHeight = 250;
	var chartWidth = 400;
	
	var curUser = "<?php echo $_SESSION['bigfixuser']; ?>"; //"APIAdmin";
	var password = "<?php echo $_SESSION['bigfixpassword']; ?>"; //"AllieCat7";
	var server = "<?php echo $_SESSION['bigfixserver']; ?>"; //"bigfix.internal.cassevern.com";
	// HTTP encode periods so as to not ruin the URL for the AJAX call
	server = server.replace(/\./g, "%2E");
	
	var defSite = "<?php echo $_SESSION['defaultsite']; ?>";
	var defComputerGroup = "<?php echo $_SESSION['defaultcomputergroup']; ?>";
	//var computerGroup = "<?php echo $_GET['cg']; ?>";
	
	// AJAX call for the Source Severity Report
	var sourceSeverityProxy = "proxies/SourceSeverityReport.php?user=" + curUser + "&pass=" + password + "&serv=" + server + "&cg=" + defComputerGroup; //computerGroup;
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
				var cellInfo = "";
				for (var i = 0; i < tupleCount; i++) {
					rowHTML = '<tr>';
					for (var j = 0; j < columnCount; j++) {
						cellInfo = xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue;
						if (j==0) {
							rowHTML += '<td style="display:none;">' + cellInfo + '</td>';
						}
						else if (j==1) {
							rowHTML += '<td style="word-wrap:break-word;">' + cellInfo + '</td>';
						}
						else {
							if (j==3 && cellInfo == "Wed, Dec 31, 1969") {
								rowHTML += '<td><span style="visibility:hidden;">' + cellInfo + '</span></td>';
							}
							else {
								rowHTML += '<td nowrap>' + cellInfo + '</td>';
							}
						}
					}
					rowHTML += '</tr>';
					$(tableID).append(rowHTML);				     
			      }
				  $(document).ready( function () {
					  $.fn.dataTable.moment( 'ddd, MMM DD, YYYY' );
						$('#sourceSeverityTable').dataTable({
                           dom: 'Blfrtip',
						   
                           buttons: [
                               'copy', 'csv', 'excel', 'print'
                           ]
                        } );
                   } );
<!-- end datatable -->				
				var severityArray = [0,0,0,0]
				var severityTable = document.getElementById('sourceSeverityTable');
				
				var computerCount = severityTable.rows.length - 1;
				document.getElementById("computerCount").innerHTML = (computerCount == 1 ? "1 Patch" : computerCount + " Patches");
				
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
				
				
				
				
				totalSeverity = severityArray[0] + severityArray[1] + severityArray[2] + severityArray[3];
				
				compliancePct = Math.round((severityArray[0]/totalSeverity)*100);
				
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
				
				var severityChartData = 
					[
						{category: 'Critical',  value: severityArray[0],  percent: Math.round((severityArray[0]/totalSeverity)*100),  color: '#FF0000'},
						{category: 'Important', value: severityArray[1],  percent: Math.round((severityArray[1]/totalSeverity)*100),  color: '#FF8000'},
						{category: 'Moderate',  value: severityArray[2],  percent: Math.round((severityArray[2]/totalSeverity)*100),  color: '#FFD500'},
						{category: 'Low',       value: severityArray[3],  percent: Math.round((severityArray[3]/totalSeverity)*100),  color: '#66B2FF'}
					];
				
				$("#wLoad").remove();
				
				var chartArea = document.getElementById("chartArea");
				
				d3PieChart(severityChartData, "sourceSeverity", 'Source Severity of Deployed Content', '#canvasDiv', '#legendDiv', chartArea.offsetWidth, chartArea.offsetHeight + 30, 45);
				
				
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
				rowHTML = "<li><a href='SourceSeverityReport.php?cg=" + xmlDoc.getElementsByTagName("Answer")[i].childNodes[0].nodeValue + "'>" + xmlDoc.getElementsByTagName("Answer")[i].childNodes[0].nodeValue + "</a></li>";
				$("#computerGroupList").append(rowHTML);
			}
		}
	}
	
</script>

<br>
<!-- start page content -->
      <div class="container">

	  
<!-- start chart top left of page  -->
        <div class="span-16">
            <div id="chartArea" style="height:400px; white-space:nowrap; overflow:hidden; width:600px; margin-bottom:20px; border:1px solid lightgrey;">
	           <div id="wLoad">
					<br><img src="includes/loading.gif">
				</div>
				<div id="canvasDiv">
				</div>
            </div>
         </div>
<!-- end chart top left of page -->	

        <div class="span-7 append-1 last">	  

<!-- button dropdown start -->
		   	<div class="btn-group append-bottom">
			   <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
			      Select Computer Group <span class="caret"></span>
			   </button>	          
			      <ul id="computerGroupList" class="dropdown-menu scrollable-menu" role="menu">	    
			      </ul>            
			</div>
<!-- button dropdown end -->


<!-- Current Computer Group dynamic label start -->
 		<div>
		 <div style="width:200px;">
	        Current Computer Group: <br>
	        <span id="currentGroup">
		       <b>
			      <?php echo $_GET['cg']; ?>
		       </b>
	        </span>
         </div>
		 </div>
<!-- Current computer group dynamic label end -->

<!-- Compliance section start -->
        <div style="top: 0px; right: 0px">
		<div>
			<div align="right" style="margin-top:5px; width:25%; top:0px; left:90px;">
			
				<span style="font-family: Arial, Helvetica, sans-serif; font-size:15pt;">Severe&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br></span>
				<span style="font-family: Arial, Helvetica, sans-serif; font-size:35pt;" id="complianceValue">0</span><br>
				<span style="font-family: Arial, Helvetica, sans-serif; font-size:15pt; white-space:nowrap;" id="computerCount">Patches</span><br><br>
			</div>
		</div>
	</div>
<!-- Compliance section end -->


<div>
<div id="legendDiv" style="width:280px;  height:160px; overflow:auto;" class="legend"></div>
</div>

</div>

<!-- start table at bottom of screen -->
         <div class="span-24 last">
		 <hr style="width:900px;"/>
            <div style=" margin-top:8px; width:920px;" id="mainDiv">
			   <table id="sourceSeverityTable" style="border:1px solid lightgrey; table-layout:fixed; ">
			      <thead>
				     <tr>
						<th style="display:none;">Patch ID</th>
					    <th width="320">Patch Name and KB Article</th>
						<th width="150">Re-mediated Systems</th>
						<th width="150">Patch Release Date</th>
						<th width="150">Source Severity</th>
					 </tr>
				  </thead>
				  <tbody id="resultsHere">
				  </tbody>
		       </table>
			</div>
		 </div>
		 

<!-- end table bottom of screen -->
<?php require 'includes/footer.php'; ?>
</div>





