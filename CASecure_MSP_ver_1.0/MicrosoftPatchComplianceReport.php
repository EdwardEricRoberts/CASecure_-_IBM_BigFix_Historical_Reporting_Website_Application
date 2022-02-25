<?php
require_once './includes/authenticate.php';
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
   <head>
<title>Microsoft Patch Compliance Report
</title>


	
	

	  
<!-- <script type="text/javascript" src="/path/to/jquery.tablesorter.js"></script> -->
      <!--<link rel="stylesheet" type="text/css" href="TABLE_FORMAT_1.css">-->

	  <!--<script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js?ver=1.3.2'></script>-->
      <?php require 'includes/headerRevised_2.php'; ?>
      <?php require 'includes/navigationRevised.php'; ?>

	  


<!-- start breadcrumbs -->
                
	  <div class="span-24">
         <div style="background-color:#cacaca; width:100%; margin-top:2px; height:23px; line-height:23px;"> 
            <span style="text-align:center;">
               <img src="includes/breadcrumbs_start.jpg" style="height:23px;">
               <span style="z-index:0; position:absolute;"><a href="Reporting.php" class="breadcrumblink">Reporting</a> &nbsp;&raquo;&nbsp; Microsoft Patch Compliance Report</span>
            </span>
         </div>
      </div>
<!-- end breadcrumbs -->
      <br>
      <div class="span-24 last">
	     <div class="pagetitle2" style="margin-bottom:20px; margin-left:10px; float:left">Microsoft Patch Compliance Report<br></div>	  
	  </div>
	  
	  
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
		 //var defComputerGroup = "<?php echo $_SESSION['defaultcomputergroup']; ?>";
	     var computerGroup = "<?php echo $_GET['cg']; ?>";
	     var applicableCount = 0;
	     var installedCount = 0;
	     var outstandingCount = 0;
	     var complianceTotal = "";
	
// AJAX call for the Post Deployment Report
	     var postDeploymentProxy = "proxies/MicrosoftPatchComplianceReport.php?user=" + curUser + "&pass=" + password + "&serv=" + server + "&cg=" + computerGroup;
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
					/* $(document).ready( function () {
					    $('#postDeploymentTable').dataTable();
					 });*/
				  }
				   $(document).ready( function () {
					   $.fn.dataTable.moment( 'ddd, MMM DD, YYYY hh:mm:ss a' );
					    $('#postDeploymentTable').dataTable({
							
                           dom: 'Blfrtip',
						   
                           buttons: [
                               'copy', 'csv', 'excel', 'print'
                           ]
                        } );
                   } );
<!-- end datatable -->
				/*  var deploymentArray = [0,0,0];
				  var deploymentTable = document.getElementById('postDeploymentTable');
				
				  var computerCount = deploymentTable.rows.length - 1;
				  document.getElementById("computerCount").innerHTML = (computerCount == 1 ? "1 System" : computerCount + " Systems");
				
				  var deploymentRows = deploymentTable.rows, 
				  deploymentRowCount = deploymentRows.length,
				  deploymentCells;
				
				  for (var r = 1; r < deploymentRowCount; r++) {
				     deploymentCells = deploymentRows[r].cells;
					 deploymentArray[0] += isNaN(deploymentCells[4].innerHTML) ? 0 : parseInt(deploymentCells[4].innerHTML);
					 deploymentArray[1] += isNaN(deploymentCells[5].innerHTML) ? 0 : parseInt(deploymentCells[5].innerHTML);
					 deploymentArray[2] += isNaN(deploymentCells[6].innerHTML) ? 0 : parseInt(deploymentCells[6].innerHTML);;
				  }
				  //alert(deploymentArray);
				
				  compliancePct = Math.round((deploymentArray[1]/deploymentArray[0])*100); 
				
				  applicableCount = deploymentArray[0];
				  installedCount = deploymentArray[1];
				  outstandingCount = deploymentArray[2];
				  complianceTotal = compliancePct + "%25";
				*/
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
				
				var deploymentArray = [0,0,0];
				var deploymentTable = document.getElementById('postDeploymentTable');
				
				var computerCount = deploymentTable.rows.length - 1;
				document.getElementById("computerCount").innerHTML = (computerCount == 1 ? "1 System" : computerCount + " Systems");
				
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
				
				  compliancePct = Math.round((deploymentArray[1]/deploymentArray[0])*100); 
				
				  applicableCount = deploymentArray[0];
				  installedCount = deploymentArray[1];
				  outstandingCount = deploymentArray[2];
				  complianceTotal = compliancePct + "%25";
				
				
				<!-- new code from Eric -->
				
				
				   var complianceChartData = 
	                  [
	                     {category: 'Applicable Patches',  value: applicableCount,  percent: Math.round((applicableCount/applicableCount)*100, 5),  color: '#0000CC'},
		                 {category: 'Installed Patches',   value: installedCount,   percent: Math.round((installedCount/applicableCount)*100, 5),   color: '#006600'},
		                 {category: 'Outstanding Patches', value: outstandingCount, percent: Math.round((outstandingCount/applicableCount)*100, 5), color: '#FF0000'}
	                  ];
	
	                  $("#pLoad").remove();
	                  d3BarChart(complianceChartData, 'microsoftPatchCompliance', 'Microsoft Patch Compliance', '#canvasDiv', '#legendDiv', 550, 400, 50);
				
				
				
				<!-- end new code from Eric -->
				
				
				
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




				  var newTableObject = document.getElementById("postDeploymentTable");
				
				  

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
	
	
	
	<!-- start my % sort -->
	
	
	
      </script>
      <br>
     
	  
	  
	  
<!-- start page content -->	  
	  <div class="container">
	     
 
	  
	  
	  &nbsp;
<!-- start chart top left of page  -->

         <div class="span-15 append-1">
            <div style="min-width:1000px; max-width:1000px; margin-bottom:20px; height:300; z-index:0;">
	           <div id="pLoad">
		          <br><img src="includes/loading.gif"> 
	           </div>
	           <div id="canvasDiv" style="top:0px; left:0px;">
		          <!--<canvas id="chart" width="900" height="600" style = "display: none;"></canvas>-->
	           </div>
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
			         <?php 
			            if ($_GET['cg'] == "All Machines") 
			               {echo "";} 
			            else 
			               {echo $_GET['cg'];} 
			         ?>
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
 <!--Compliance section end -->			  
			  
			  
			  
			  
			  
<!-- Subchart start -->
<div class="prepend-top">
<div id="legendDiv" class="legend"></div>

<!-- addition from Eric for new charts -->
		<!--<div id="legendDiv" style="position:absolute; top:230px; left:10px;"></div>-->
<!-- end addition from Eric -->	


<!--<table id="chartData" style=" top:230px; left:10px; width:300px;">
			   <tr>
				  <th>Category</th>
				  <th>Patches</th>
				  <th>Percent</th>
			   </tr>
		    </table>-->
</div>
<!-- Subchart end -->	  




		    
	     </div>

         
<hr/>
<!-- start table at bottom of screen -->
     <div class="span-24 last">
	   <div style=" margin-top:8px;  z-index:0;" id="mainDiv">
	        <table id="postDeploymentTable" style="border:1px solid lightgrey;">
			<thead>
			<tr>
		       <th>Computer</th>
		       <th>Users</th>
		       <th>Operating System</th>
		       <th>Last Report Time</th>
		       <th>Applicable Patches</th>
		       <th>Installed Patches</th>
		       <th>Outstanding Patches</th>
		       <th>Compliance</th>
			   </tr>
			   </thead>
			   <tbody>
			   </tbody>
	        </table>
         </div>
		 
		
      </div>
	  <?php require 'includes/footer.php'; ?>
	  </div>
      