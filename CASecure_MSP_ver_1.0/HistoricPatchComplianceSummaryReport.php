<?php
require_once './includes/authenticate.php';
require_once './includes/db_connect.php';
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
      <title>Microsoft Patch Compliance Historic Summary</title>


	  <?php require 'includes/headerRevised_2.php'; ?>
      <?php require 'includes/navigationRevised.php'; ?>

<!-- start breadcrumbs -->                
	  <div class="span-24">
         <div style="background-color:#cacaca; width:100%; margin-top:2px; height:23px; line-height:23px;"> 
            <span style="text-align:center;">
               <img src="includes/breadcrumbs_start.jpg" style="height:23px;">
               <span style="z-index:0; position:absolute;"><a href="Reporting.php" class="breadcrumblink">Reporting</a> &nbsp;&raquo;&nbsp; Patch Compliance Historic Summary</span>
            </span>
         </div>
      </div>
<!-- end breadcrumbs -->
 <br>
      <div class="span-24 last">
	     <div class="pagetitle2" style="margin-bottom:20px; margin-left:10px; float:left">Patch Compliance Historic Summary<br>
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
	
	var site = "<?php echo $_GET['site'] ?>";
	var computerGroup = "<?php echo $_GET['cg']; ?>";
	
	//var patchTuesdayOn = "<?php echo $_GET['pt']; ?>";
	if ("<?php echo $_GET['pt']; ?>" == "FALSE") {
		var patchTuesdayOn = false;
	}
	else if ("<?php echo $_GET['pt']; ?>" == "TRUE") {
		var patchTuesdayOn = true;
	}
	
	// AJAX call for the Source Severity Report
	//var sourceSeverityProxy = "database/MicrosoftPatchComplianceSummaryFetch.php?cg=" + computerGroup;
	//xmlTableParser(sourceSeverityProxy, "#sourceSeverityTable");
	
	function numberWithCommas(x) {
		return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}	
	//
	var currentUserID = "<?php echo $_SESSION['userid']; ?>"; //"501f60f9";
	
	if(!patchTuesdayOn) {
		var historicDataFetchURL = "http://localhost/Server/CASecure_MSP_ver_1.0/database/fetch/FetchHistoricPatchComplianceReportData2.php?cid=" + currentUserID + "&site=" + site + "&cg=" + computerGroup;
	}
	else {
		var historicDataFetchURL = "http://localhost/Server/CASecure_MSP_ver_1.0/database/fetch/FetchHistoricPatchTuesdayComplianceReportData.php?cid=" + currentUserID + "&site=" + site + "&cg=" + computerGroup;
	}
	
	var historicDataFetchRequest = new XMLHttpRequest();
	historicDataFetchRequest.open("GET", historicDataFetchURL, true);
	historicDataFetchRequest.send();
	historicDataFetchRequest.onreadystatechange = function() {
		if ((this.readyState === 4) && (this.status === 200)) {
			var historicDataFetchXML = this.responseXML;
			
			if (historicDataFetchXML.getElementsByTagName("Error").length == 0) {
				historicDataFetchCount = historicDataFetchXML.getElementsByTagName("data_timestamp").length;
				//alert(historicDataFetchCount);
				var historicDataFetchRowHTML = '';
				for (var i = 0; i < historicDataFetchCount; i++) {
					//var systemCount = (historicDataFetchXML.getElementsByTagName("system_count")[i].childNodes[0].nodeValue == null)?(0):(historicDataFetchXML.getElementsByTagName("system_count")[i].childNodes[0].nodeValue);
					historicDataFetchRowHTML = '<tr>';
					historicDataFetchRowHTML += '<td style="display:none"></td>';
					historicDataFetchRowHTML += '<td nowrap>' + historicDataFetchXML.getElementsByTagName("timestamp")[i].childNodes[0].nodeValue + '</td>';
					historicDataFetchRowHTML += '<td nowrap style="text-align:right;">' + historicDataFetchXML.getElementsByTagName("applicable_count")[i].childNodes[0].nodeValue + '</td>';
					historicDataFetchRowHTML += '<td nowrap style="text-align:right;">' + historicDataFetchXML.getElementsByTagName("installed_count")[i].childNodes[0].nodeValue + '</td>';
					historicDataFetchRowHTML += '<td nowrap style="text-align:right;">' + historicDataFetchXML.getElementsByTagName("outstanding_count")[i].childNodes[0].nodeValue + '</td>';
					historicDataFetchRowHTML += '<td nowrap style="text-align:right;">' + historicDataFetchXML.getElementsByTagName("compliance")[i].childNodes[0].nodeValue + '</td>';
					historicDataFetchRowHTML += '<td nowrap style="text-align:right;">' + historicDataFetchXML.getElementsByTagName("system_count")[i].childNodes[0].nodeValue + '</td>';
					historicDataFetchRowHTML += '<td style="display:none">' + historicDataFetchXML.getElementsByTagName("site")[i].childNodes[0].nodeValue + '</td>';
					historicDataFetchRowHTML += '<td style="display:none">' + historicDataFetchXML.getElementsByTagName("computer_group")[i].childNodes[0].nodeValue + '</td>';
					historicDataFetchRowHTML += '</tr>';
					//alert(historicDataFetchRowHTML);
					$('#sourceSeverityTable').append(historicDataFetchRowHTML);
				}
				
				  $(document).ready( function () {
					  $.fn.dataTable.moment( 'ddd, MMMM DD, YYYY h:mm:ss A' ); 
					  
					  
						$('#sourceSeverityTable').dataTable({
						dom: 'Blfrtip',
						
						button: [
							'copy', 'csv', 'excel', 'print'
							]
						});
				  });
<!-- end datatable -->	

				var dateTimeArray = [];
				var complianceArray = [];
				var applicableArray =[];
				var installedArray =[];
				var outstandingArray =[];
				var colorArray = [];
				var deploymentTable = document.getElementById("sourceSeverityTable");
				
				//var computerCount = deploymentTable.rows.length - 1;
				//document.getElementById("computerCount").innerHTML = (computerCount == 1 ? "1 System" : computerCount + " Systems");
				
				var deploymentRows = deploymentTable.rows, 
					deploymentRowCount = deploymentRows.length,
					deploymentCells;
				
				for (var r = 1; r < deploymentRowCount; r++) {
					deploymentCells = deploymentRows[r].cells;
					dateTimeArray[r-1] = new Date(deploymentCells[1].innerHTML);
					complianceArray[r-1] = (deploymentCells[2].innerHTML != '0')?((parseFloat(deploymentCells[3].innerHTML) / parseFloat(deploymentCells[2].innerHTML)) * 100).toFixed(2):0;
					applicableArray[r-1] = deploymentCells[2].innerHTML;
					installedArray[r-1] = deploymentCells[3].innerHTML;
					outstandingArray[r-1] = deploymentCells[4].innerHTML;
					colorArray[r-1] = complianceColor(complianceArray[r]);
				}
				//alert(dateTimeArray);
				
				var complianceChartData = [];
				//
				for (var i = 0; i < dateTimeArray.length; i++) {
					complianceChartData[i] = 
						{
							timestamp: dateTimeArray[i],  
							percent_1: complianceArray[i],  
							applicable_1: applicableArray[i], 
							installed_1: installedArray[i], 
							outstanding_1: outstandingArray[i], 
							color_1: colorArray[i]
						}
				}
				$('#wLoad').remove();
				//alert(complianceChartData[105].id)
				d3LineChart(complianceChartData, 'patchCompliance', 'Historic Patch Compliance', '#canvasDiv', '#legendDiv', chartArea.offsetWidth, chartArea.offsetHeight + 12, 50);
				//d3PieChart(severityChartData, "sourceSeverity", 'Source Severity of Deployed Content', '#canvasDiv', '#legendDiv', chartArea.offsetWidth, chartArea.offsetHeight + 12, 40);
				
				$("#sourceSeverityTable" + ' tr td:nth-child(2)').each(function() {
					var timestamp = new Date($(this).text());
					var dateFormat = d3.timeFormat('%a, %B %d, %Y');
					var timeFormat = d3.timeFormat('%_I:%M:%S %p');
					$(this).html(dateFormat(timestamp) + '<br/>' + timeFormat(timestamp));
				})
				
				$("#sourceSeverityTable" + ' tr td:nth-child(3)').each(function() {$(this).text(numberWithCommas($(this).text()))});
				$("#sourceSeverityTable" + ' tr td:nth-child(4)').each(function() {$(this).text(numberWithCommas($(this).text()))});
				$("#sourceSeverityTable" + ' tr td:nth-child(5)').each(function() {$(this).text(numberWithCommas($(this).text()))});
			}
		}
	}
	//
	/*
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
						if (j == 0 || j == 6 || j == 7)
							rowHTML += '<td nowrap style="display:none;">' + xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue + '</td>';
						else if (j == 1)
							rowHTML += '<td nowrap>' + xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue + '</td>';
						else 
							rowHTML += '<td nowrap style="text-align:right;">' + xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue + '</td>';
					}
					rowHTML += '</tr>';
					$(tableID).append(rowHTML);
				}
				  $(document).ready( function () {
					  $.fn.dataTable.moment( 'ddd, MMMM DD, YYYY h:mm:ss A' ); 
					  
					  
						$('#sourceSeverityTable').dataTable({
						dom: 'Blfrtip',
						
						button: [
							'copy', 'csv', 'excel', 'print'
							]
						});
				  });
				
<!-- end datatable -->				
				var dateTimeArray = [];
				var complianceArray = [];
				var applicableArray =[];
				var installedArray =[];
				var outstandingArray =[];
				var colorArray = [];
				var deploymentTable = document.getElementById("sourceSeverityTable");
				
				//var computerCount = deploymentTable.rows.length - 1;
				//document.getElementById("computerCount").innerHTML = (computerCount == 1 ? "1 System" : computerCount + " Systems");
				
				var deploymentRows = deploymentTable.rows, 
					deploymentRowCount = deploymentRows.length,
					deploymentCells;
				
				for (var r = 1; r < deploymentRowCount; r++) {
					deploymentCells = deploymentRows[r].cells;
					dateTimeArray[r-1] = new Date(deploymentCells[1].innerHTML);
					complianceArray[r-1] = (deploymentCells[2].innerHTML != '0')?((parseFloat(deploymentCells[3].innerHTML) / parseFloat(deploymentCells[2].innerHTML)) * 100).toFixed(2):0;
					applicableArray[r-1] = deploymentCells[2].innerHTML;
					installedArray[r-1] = deploymentCells[3].innerHTML;
					outstandingArray[r-1] = deploymentCells[4].innerHTML;
					colorArray[r-1] = complianceColor(complianceArray[r]);
				}
				//alert(dateTimeArray);
				
				var complianceChartData = [];
				//
				for (var i = 0; i < dateTimeArray.length; i++) {
					complianceChartData[i] = 
						{
							timestamp: dateTimeArray[i],  
							percent_1: complianceArray[i],  
							applicable_1: applicableArray[i], 
							installed_1: installedArray[i], 
							outstanding_1: outstandingArray[i], 
							color_1: colorArray[i]
						}
				}
				$('#wLoad').remove();
				//alert(complianceChartData[105].id)
				d3LineChart(complianceChartData, 'microsoftPatchCompliance', 'Historic Microsoft Patch Compliance', '#canvasDiv', '#legendDiv', chartArea.offsetWidth, chartArea.offsetHeight + 12, 50);
				//d3PieChart(severityChartData, "sourceSeverity", 'Source Severity of Deployed Content', '#canvasDiv', '#legendDiv', chartArea.offsetWidth, chartArea.offsetHeight + 12, 40);
				
				$(tableID + ' tr td:nth-child(2)').each(function() {
					var timestamp = new Date($(this).text());
					var dateFormat = d3.timeFormat('%a, %B %d, %Y');
					var timeFormat = d3.timeFormat('%_I:%M:%S %p');
					$(this).html(dateFormat(timestamp) + '<br/>' + timeFormat(timestamp));
				})
				
				$(tableID + ' tr td:nth-child(3)').each(function() {$(this).text(numberWithCommas($(this).text()))});
				$(tableID + ' tr td:nth-child(4)').each(function() {$(this).text(numberWithCommas($(this).text()))});
				$(tableID + ' tr td:nth-child(5)').each(function() {$(this).text(numberWithCommas($(this).text()))});
			}
		}
	}
	*/
	function complianceColor(val) {
		if (val >= 95) {
			return "00FF00";
		} else if (val >= 90 && val < 95) {
			return "33ff00";
		} else if (val >= 85 && val < 90) {
			return "66ff00";
		} else if (val >= 80 && val < 85) {
			return "99ff00";
		} else if (val >= 75 && val < 80) {
			return "ccff00";
		} else if (val >= 70 && val < 75) {
			return "FFFF00";
		} else if (val >= 65 && val < 70) {
			return "FFCC00";
		} else if (val >= 60 && val < 65) {
			return "ff9900";
		} else if (val >= 55 && val < 60) {
			return "ff6600";
		} else if (val >= 50 && val < 55) {
			return "FF3300";
		} else if (val >= 0 && val < 50) { 
			return "FF0000";  
		}
		
		 //"#cc3333";  // Persian Red
		 //"orange";  // Orange
	}
	/*
	var siteRequest = new XMLHttpRequest();
	siteRequest.open("GET", "proxies/SitesHistoricalReportDropDownList.php?user=" + curUser + "&pass=" + password + "&serv=" + server, true);
	siteRequest.send();
	siteRequest.onreadystatechange = function() {
		if ((this.readyState === 4) && (this.status === 200)) {
			xmlSiteList = this.responseText.toString();
			var badQuery = xmlSiteList.substring((parseInt(xmlSiteList.search("Resource"))-1), (parseInt(xmlSiteList.search('Result'))-5));
			xmlSiteList = xmlSiteList.replace(badQuery, '');
			var parser = new DOMParser();
			var xmlDoc = parser.parseFromString(xmlSiteList,"text/xml");
			var rowCount = xmlDoc.getElementsByTagName("Tuple").length;
			var rowHTML = "";
			for (var i = 0; i < rowCount; i++) {
				rowHTML = "<li><a href='HistoricPatchComplianceSummaryReport.php?site=" + xmlDoc.getElementsByTagName("Tuple")[i].getElementsByTagName("Answer")[0].childNodes[0].nodeValue + "&cg=" + computerGroup + "&pt=" + patchTuesdayOn + "'>" + xmlDoc.getElementsByTagName("Tuple")[i].getElementsByTagName("Answer")[1].childNodes[0].nodeValue + "</a></li>";
				$("#siteList").append(rowHTML);
			}
		}
	}
	
	var compGroup = new XMLHttpRequest();
	compGroup.open("GET", "proxies/ComputerGroupsHistoricalReportDropDownList.php?user=" + curUser + "&pass=" + password + "&serv=" + server, true);
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
				rowHTML = "<li><a href='HistoricPatchComplianceSummaryReport.php?site=" + site + "&cg=" + xmlDoc.getElementsByTagName("Answer")[i].childNodes[0].nodeValue + "&pt=" + patchTuesdayOn + "'>" + xmlDoc.getElementsByTagName("Answer")[i].childNodes[0].nodeValue + "</a></li>";
				$("#computerGroupList").append(rowHTML);
			}
		}
	}
	*/
	function changeReportType() {
		var checkBoxDiv = document.getElementById("patchTuesdayCheckDiv");
		var checkBox = document.getElementById("patchTuesdayCheck");
		
		var urlBase = "HistoricPatchComplianceSummaryReport.php?site=" + site + "&cg=" + computerGroup + "&pt=";
		if(!patchTuesdayOn) {
			window.location = urlBase + "TRUE";
			$('#patchTuesday').html("include");
		}
		else {
			window.location = urlBase + "FALSE";
			$('#patchTuesday').html("exclude");
		}
	}
	
	function loadCheckBoxStatus() {
		var checkBox = document.getElementById("patchTuesdayCheck");
		
		if(!patchTuesdayOn) {
			checkBox.checked = false;
			//$("#patchTuesdayCheck").prop('checked', false);
			//$(".patchTuesdayCheck").attr('checked', false);
			//$(".patchTuesdayCheck").each(function(){ this.checked = false; });
		}
		else {
			checkBox.checked = true;
			//$("#patchTuesdayCheck").prop('checked', true);
			//$(".patchTuesdayCheck").attr('checked', true);
			//$(".patchTuesdayCheck").each(function(){ this.checked = true; });
		}
	}
	
</script>

<br>
<!-- start page content -->
      <div class="container">

	<div class="span-24 last" style="margin-bottom:12px;">
	
<!-- button dropdown start -->
<div class="span-8">
		   	<div class="btn-group append-bottom">
			   <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
			      Select Site <span class="caret"></span>
			   </button>	          
			      <ul id="siteList" class="dropdown-menu scrollable-menu" role="menu">	    
			      </ul>            
			</div>
<!-- button dropdown end -->

<!-- Current Site dynamic label start -->
 		
		 <div style="width:200px;">
	        Current Site: <br>
	        <span id="currentGroup">
		       <b>
					
			      	<?php
						$sql = 
							"SELECT site_display_name ".
							"FROM sites ".
							"WHERE site_name = :siteName;";
						$querry = $db->prepare($sql);
						$querry->bindParam(":siteName", $_GET['site'], PDO::PARAM_STR);
						$querry->execute();
						$querryArray = $querry->fetch(PDO::FETCH_ASSOC);
						echo $querryArray["site_display_name"];
						//echo $_GET['site'];
			        ?>
		       </b>
	        </span>
         </div>
		 </div>
		 
<!-- button dropdown start -->
<div class="span-8">
		   	<div class="btn-group append-bottom">
			   <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
			      Select Computer Group <span class="caret"></span>
			   </button>	          
			      <ul id="computerGroupList" class="dropdown-menu scrollable-menu" role="menu">	    
			      </ul>            
			</div>
<!-- button dropdown end -->

<!-- Current Computer Group dynamic label start -->
 		
		 <div style="width:200px;">
	        Current Computer Group: <br>
	        <span id="currentGroup">
		       <b>
					<?php 
						echo $_GET['cg']; 
					?>
		       </b>
	        </span>
         </div>
		 </div>
		 
<!-- Current computer group dynamic label end -->
		<div id="patchTuesdayCheckDiv" onmouseover="" style="cursor:pointer;" onclick="changeReportType()">
			<input id="patchTuesdayCheck" class ="patchTuesdayCheck" name="pt" type="checkbox" onmouseover="" style="cursor:pointer;" onload="loadCheckBoxStatus()">
			<b>  <span id="patchTuesday">Exclude</span> Out of Date Systems <br>(Only Applies to Site "Patches for Windows")</b></input>
		</div>
</div>	
<!-- start chart top left of page  -->
        <div class="span-24 end">
            <div id="chartArea" style="height:410px; white-space:nowrap; overflow:hidden; width:925px; margin-bottom:20px; border:1px solid lightgrey;">
	           <div id="wLoad">
					<br><img src="includes/loading.gif">
				</div>
				<div id="canvasDiv">
				</div>
            </div>
         </div>
<!-- end chart top left of page -->	
<div class="span-24 end">
        <div class="span-7 append-1 last">	  


<div id="legendDiv" style="width:280px; height:25px; overflow:auto; margin-bottom:15px; margin-left:320px;"></div>
</div>

</div>

<!-- start table at bottom of screen -->
         <div class="span-24 last">
		 <hr style="width:925px; margin-left:-2px;"/>
		 </div>
		 <div class="span-24 last">
            <div style=" margin-top:8px; width:920px;" id="mainDiv">
			   <table id="sourceSeverityTable" style="border:1px solid lightgrey; table-layout:fixed; ">
			      <thead>
				     <tr>
					    <th width="175">Timestamp</th>
						<th style="display:none">Log ID</th>
						<th >Applicable Patches</th>
						<th >Installed Patches</th>
						<th >Outstanding Patches</th>
						<th >Compliance</th>
						<th >Systems</th>
						<th style="display:none">Site</th>
						<th style="display:none">Computer Group</th>
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





