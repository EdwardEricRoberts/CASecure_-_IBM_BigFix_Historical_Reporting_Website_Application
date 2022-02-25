<?php
require_once './includes/authenticate.php';
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
      <title>Windows Update Status Report</title>


	  <?php require 'includes/headerRevised_2.php'; ?>
      <?php require 'includes/navigationRevised.php'; ?>
	  
<!-- start breadcrumbs -->                
	  <div class="span-24">
         <div style="background-color:#cacaca; width:100%; margin-top:2px; height:23px; line-height:23px;"> 
            <span style="text-align:center;">
               <img src="includes/breadcrumbs_start.jpg" style="height:23px;">
               <span style="z-index:0; position:absolute;"><a href="Reporting.php" class="breadcrumblink">Reporting</a> &nbsp;&raquo;&nbsp; Windows Update Status Report</span>
            </span>
         </div>
      </div>
<!-- end breadcrumbs -->
      <br>
      <div class="span-24 last">
	     <div class="pagetitle2" style="margin-bottom:20px; margin-left:10px; float:left">Windows Update Status Report<br>
		 </div>	  
	  </div>

<script>
	document.getElementById("reportingNav").className = "current-menu-item";
	
	var chartHeight = 400;
	var chartWidth = 550;
	
	var curUser = "<?php echo $_SESSION['bigfixuser']; ?>"; //"APIAdmin";
	var password = "<?php echo $_SESSION['bigfixpassword']; ?>"; //"AllieCat7";
	var server = "<?php echo $_SESSION['bigfixserver']; ?>"; //"bigfix.internal.cassevern.com";
	// HTTP encode periods so as to not ruin the URL for the AJAX call
	server = server.replace(/\./g, "%2E");
	
	var defSite = "<?php echo $_SESSION['defaultsite']; ?>";
	var defComputerGroup = "<?php echo $_SESSION['defaultcomputergroup']; ?>";
	//var computerGroup = "<?php echo $_GET['cg']; ?>";
	
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
						if (j == 0 || j == 4) {}
						else {
							rowHTML += '<td nowrap>' + xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue + '</td>';
						}
					}
					rowHTML += '</tr>';
				$(tableID).append(rowHTML);}
				     $(document).ready( function () {
				        $('#windowsUpdateStatusTable').dataTable({
                           dom: 'Blfrtip',
						   
                           buttons: [
                               'copy', 'csv', 'excel', 'print'
                           ]
                        } );
                   } );
<!-- end datatable -->
				var windowsUpdatesArray = [0,0];
				var windowsUpdatesTable = document.getElementById('windowsUpdateStatusTable');
				
				var windowsUpdatesRows = windowsUpdatesTable.rows, 
					windowsUpdatesRowCount = windowsUpdatesRows.length, 
					windowsUpdatesCells;
				var currentUpdateState;
				
				for (var r = 1; r < windowsUpdatesRowCount; r++) {
					windowsUpdatesCells = windowsUpdatesRows[r].cells;
					currentUpdateState = windowsUpdatesCells[3].innerHTML;
					if (currentUpdateState == "Enabled")
						windowsUpdatesArray[1] += 1;
					else
						windowsUpdatesArray[0] += 1;
				}
				//alert(windowsUpdatesArray);
				
<!-- start new chart code -->
				var computerCount = windowsUpdatesTable.rows.length - 1;
				document.getElementById("computerCount").innerHTML = (computerCount == 1 ? "1 System" : computerCount + " Systems");
				
				compliancePct = Math.round((windowsUpdatesArray[0] / computerCount) * 100);
				
				var complianceChartData = 
					[
						{category: 'Auto Updates Disabled', value: windowsUpdatesArray[0], percent: Math.round((windowsUpdatesArray[0]/computerCount)*100, 5), color: '#006600'},
						{category: 'Auto Updates Enabled',  value: windowsUpdatesArray[1], percent: Math.round((windowsUpdatesArray[1]/computerCount)*100, 5), color: '#FF0000'}
					];
				
				d3PieChart(complianceChartData, 'windowsUpdateStatus', 'Windows Update Status', '#canvasDiv', '#legendDiv', 550, 400, 50);
<!-- end new chart code -->			

				$("#wLoad").remove();
				
<!-- add in this chunk of code -->

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

<!-- end code chunk -->
				
				
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
				rowHTML = "<li><a href='WindowsUpdateStatusReport.php?cg=" + xmlDoc.getElementsByTagName("Answer")[i].childNodes[0].nodeValue + "'>" + xmlDoc.getElementsByTagName("Answer")[i].childNodes[0].nodeValue + "</a></li>";
				$("#computerGroupList").append(rowHTML);
			}
		}
	}
	
</script>

<br>


<!-- start page content -->
      <div class="container">
	  
	  
<!-- start chart top left of page "Total Systems Vulnerability" -->	  
	     <div class="span-15 append-1"> 
	        <div id="windowsStatusUpdateChart" style="height:350px; white-space:nowrap; overflow:hidden; width:550px; margin-bottom:20px; border:1px solid lightgrey;">
	           <div id="wLoad">
		          <br><img src="includes/loading.gif">
	           </div>
			    <div id="canvasDiv"  style="top:0px; left:100px;">
	           </div>

		<br>
            </div>
	     </div>
<!-- end chart top left of page -->		  
	  
        <div class="span-8 last">	  

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

		    <div style="width:200px;">
	           Current Computer Group: <br>
	           <span id="currentGroup">
		          <b>
			         <?php echo $_GET['cg']; ?>
		          </b>
	           </span>
            </div> 

<!-- Current computer group dynamic label end -->

<!-- Compliance section start -->
		<div style="top:0px; right:0px">
		       <div>
			   <div align="right" style="margin-top:5px; width:25%; top:0px; left:90px;">
			      
			         <span style="font-family: Arial, Helvetica, sans-serif; font-size:15pt;">Compliance</span><br>
				     <span style="font-family: Arial, Helvetica, sans-serif; font-size:35pt;" id="complianceValue">0</span><br>
				     <span style="font-family: Arial, Helvetica, sans-serif; font-size:15pt; white-space:nowrap;" id="computerCount">Systems</span><br>
			      
			   </div>
		       </div>
            </div>


<!-- Compliance section end -->

<!-- legend chart start -->
<div class="prepend-top">
<div id="legendDiv" style="width:300px;" class="legend"></div>
</div>
<!-- legend chart end -->
        </div>

<!-- start table at bottom of screen -->
<div class="span-24 last">
		 <hr style="width:900px;"/>
		 <div style="margin-top:8px; width:920px;" id="mainDiv">
          <table id="windowsUpdateStatusTable" style="border:1px solid lightgrey;">
		     <thead>
			    <tr>
	               <!--<th>Computer Fixlet ID</th>-->
				   <th>Computer</th>
				   <th>Users</th>
				   <th>Operating System</th>
				   <!-- <th>IP Address</th> -->
				   <th>Automatic Update State</th>
				   <th>Automatic Update Method</th>
				   <th>No Reboot with Logged on User</th>
                 </tr>
			 </thead>
			 <tbody id="resultsHere">
			 </tbody>
		  </table>
		  </div></div>

<!-- end table at bottom of screen -->
	 <?php require 'includes/footer.php'; ?> 
</div>	  
<!--
	<ul>		
		<li><button id="postReportSummary" onmouseover="" style="cursor:pointer;">Post Report Summary</button></li>
	</ul>
-->






