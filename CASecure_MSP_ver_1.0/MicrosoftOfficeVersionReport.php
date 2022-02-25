<?php
require_once './includes/authenticate.php';
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
      <title>Microsoft Office Version Report</title>


	  <?php require 'includes/headerRevised_2.php'; ?>
      <?php require 'includes/navigationRevised.php'; ?>

<!-- start breadcrumbs -->                
	  <div class="span-24">
         <div style="background-color:#cacaca; width:100%; margin-top:2px; height:23px; line-height:23px;"> 
            <span style="text-align:center;">
               <img src="includes/breadcrumbs_start.jpg" style="height:23px;">
               <span style="z-index:0; position:absolute;"><a href="Reporting.php" class="breadcrumblink">Reporting</a> &nbsp;&raquo;&nbsp; Microsoft Office Version Report</span>
            </span>
         </div>
      </div>
<!-- end breadcrumbs -->
      <br>
      <div class="span-24 last">
	     <div class="pagetitle2" style="margin-bottom:20px; margin-left:10px; float:left">Microsoft Office Version Report<br>
		 </div>	  
	  </div>
      <script>
	
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
	
	     function randomColor() {
		    return ('#' + (Math.floor(Math.random() * 0x1000000) + 0x1000000).toString(16).substr(1));
	     }	
	
	     document.getElementById("reportingNav").className = "current-menu-item";
	
	     var chartHeight = 500;
	     var chartWidth = 800;
	
	     var curUser = "<?php echo $_SESSION['bigfixuser']; ?>"; //"APIAdmin";
	     var password = "<?php echo $_SESSION['bigfixpassword']; ?>"; //"AllieCat7";
	     var server = "<?php echo $_SESSION['bigfixserver']; ?>"; //"bigfix.internal.cassevern.com";
// HTTP encode periods so as to not ruin the URL for the AJAX call
	     server = server.replace(/\./g, "%2E");
	
	     var computerGroup = "<?php echo $_GET['cg']; ?>";
	
// AJAX call for the Windows Update Status Report
	     var microsoftOfficeVersionProxy = "proxies/MicrosoftOfficeVersionReport.php?user=" + curUser + "&pass=" + password + "&serv=" + server + "&cg=" + computerGroup;
	     xmlTableParser(microsoftOfficeVersionProxy, "#microsoftOfficeVersionTable");
	
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
				     if (j==0 || j == 3) {
					    rowHTML += '<td nowrap class="hiddenColumn">' + xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue + '</td>';
					 }
					 else {
					    rowHTML += '<td nowrap>' + xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue + '</td>';
					 }
				  }
				  rowHTML += '</tr>';
			   $(tableID).append(rowHTML);}
			   	  $(document).ready( function () {
				     $('#microsoftOfficeVersionTable').dataTable({
                           dom: 'Blfrtip',
						   
                           buttons: [
                               'copy', 'csv', 'excel', 'print'
                           ]
                        } );
                   } );
// end datatable
			   
				
			   sortTable(3, "microsoftOfficeVersionTable");
				
			   var microsoftOfficeVersionLabelArray = [];
			   var microsoftOfficeVersionDataArray = [];
			   //var microsoftOfficeVersionObjectArray = [];
			   var msOfficeVersionTable = document.getElementById('microsoftOfficeVersionTable');
				
			   var msOfficeVersionRows = msOfficeVersionTable.rows, 
			   msOfficeVersionRowCount = msOfficeVersionRows.length, 
			   msOfficeVersionCells, msOfficeVersionCellsNext, msOfficeVersionRowSpan,
			   msOfficeVersionCount = 0, i = 0;
				
			   for (var r = 1; r < msOfficeVersionRowCount; r++) {
			      msOfficeVersionCells = msOfficeVersionRows[r].cells;
				  msOfficeVersionRowSpan = 1;
				  if (r != (msOfficeVersionRowCount - 1)) {
				     msOfficeVersionCellsNext = msOfficeVersionRows[r+1].cells;
					 while (msOfficeVersionCells[3].innerHTML == msOfficeVersionCellsNext[3].innerHTML && r != (msOfficeVersionRowCount - 1)) {
					    msOfficeVersionRowSpan++;
						r++;
						msOfficeVersionCells = msOfficeVersionRows[r].cells;
						if (r != (msOfficeVersionRowCount - 1))
						    msOfficeVersionCellsNext = msOfficeVersionRows[r+1].cells;
						else
						    msOfficeVersionCellsNext[3] == "";
					 }
				  }
				  msOfficeVersionCount++;
				  microsoftOfficeVersionLabelArray[i] = msOfficeVersionCells[3].innerHTML;
				  microsoftOfficeVersionDataArray[i] = msOfficeVersionRowSpan;
				  //microsoftOfficeVersionObjectArray[i] = {label: msOfficeVersionCells[3].innerHTML, y: msOfficeVersionRowSpan, name: msOfficeVersionCells[3].innerHTML};
				  i++;
			   }
			   //alert(microsoftOfficeVersionLabelArray + "\n" + microsoftOfficeVersionDataArray);
			   //alert(microsoftOfficeVersionObjectArray);
				
			   sortTable(0, "microsoftOfficeVersionTable");
				
<!-- new content -->				
				
				var softwareCount = microsoftOfficeVersionLabelArray.length;
				document.getElementById("softwareCount").innerHTML = (softwareCount == 1 ? "1 Application" : softwareCount + " Applications");
				
				//var computerCount = msOfficeVersionTable.rows.length - 1;
				var computerCount = 0;
				
				for (var r = 1; r < msOfficeVersionRowCount; r++) {
					msOfficeVersionCells = msOfficeVersionRows[r].cells;
					if (r != (msOfficeVersionRowCount - 1)) {
						msOfficeVersionCellsNext = msOfficeVersionRows[r+1].cells;
						while (msOfficeVersionCells[0].innerHTML == msOfficeVersionCellsNext[0].innerHTML && r != (msOfficeVersionRowCount - 1)) {
							r++;
							msOfficeVersionCells = msOfficeVersionRows[r].cells;
							if (r != (msOfficeVersionRowCount - 1))
								msOfficeVersionCellsNext = msOfficeVersionRows[r+1].cells;
							else
								msOfficeVersionCellsNext[0] == "";
						}
					}
					computerCount++;
				}
				document.getElementById("computerCount").innerHTML = (computerCount == 1 ? "1 System" : computerCount + " Systems");
				
				var dataTotal = 0;
				
				for (var i = 0; i < microsoftOfficeVersionDataArray.length; i++) {
					dataTotal += microsoftOfficeVersionDataArray[i];
				}
				
				var complianceChartData = [];
				
<!-- end new content -->
				
				
			   /*
			   $("#postReportSummary").on("click", function() {
			      var queryPost = new XMLHttpRequest();
				  queryPost.open("POST", "database/WindowsUpdateStatusSummaryLog.php?dis=" +  microsoftOfficeVersionArray[0] + "&en=" + microsoftOfficeVersionArray[1] + "&cg=" + computerGroup, true);
				  queryPost.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
					queryPost.onreadystatechange = function() {
						if (queryPost.readyState == 4 && queryPost.status == 200) {
							alert(queryPost.responseText);
						}
					}
					queryPost.send();
				});
			   */
					
			   //var chartColours = [];            // Chart colours (pulled from the HTML table)
			   for (var i = 0; i < microsoftOfficeVersionLabelArray.length; i++) {
			      // Extract and store the cell colour
			      //chartColours[i] = randomColor();
                  complianceChartData[i] = 
						{
							category: microsoftOfficeVersionLabelArray[i].split(" | ")[0] + " | " + microsoftOfficeVersionLabelArray[i].split(" | ")[1], 
							value: microsoftOfficeVersionDataArray[i], 
							percent: Math.round((microsoftOfficeVersionDataArray[i]/dataTotal)*100, 5), 
							color: randomColor()
						}
				}
				
				d3PieChart(complianceChartData, 'microsoftOfficeVersion', 'Microsoft Office Versions', '#canvasDiv', '#legendDiv', 600, 450, 40);
				
			   $("#wLoad").remove();
				
			   var hiddenColumn = document.getElementsByClassName("hiddenColumn");
			   for (var i = 0; hiddenColumn.length; i++) {
			      hiddenColumn[i].style.display = "none";
			   }
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
			   rowHTML = "<li><a href='MicrosoftOfficeVersionReport.php?cg=" + xmlDoc.getElementsByTagName("Answer")[i].childNodes[0].nodeValue + "'>" + xmlDoc.getElementsByTagName("Answer")[i].childNodes[0].nodeValue + "</a></li>";
			   $("#computerGroupList").append(rowHTML);
			}
		 }
	  }
	
      </script>

      <br>
<!-- start page content -->

      <div class="container">

	  
<!-- start chart top left of page -->
        <div class="span-16">
            <div style="height:440px; white-space:nowrap; overflow:hidden; width:600px; margin-bottom:20px; border:1px solid lightgrey;">
	           <div id="wLoad">
		<br><img src="includes/loading.gif">
	</div>
	<div id="canvasDiv">
	</div>
            </div>
         </div>
<!-- end chart top left of page -->

<!-- button dropdown start -->
         <div class="span-7 end">
         <div class="btn-group append-bottom">
		    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
			   Select Computer Group <span class="caret"></span>
			</button>
			<ul id="computerGroupList" class="dropdown-menu scrollable-menu" role="menu">
			</ul>
		 </div>
		 
<!-- button dropdown end --> 


<!-- Current computer group dynamic label end -->
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
		<div style="top:0px; right:0px">
		       <div>
			   <div align="right" style="margin-top:5px; width:25%; top:0px; left:90px;">
			    <!--<div><p style="font-family: Arial, Helvetica, sans-serif; font-size: 40px;">Compliance</p><br></div>-->
				<!--<div align="right" style="margin-top:-130px;"><p style="font-family: Arial, Helvetica, sans-serif; font-size: 80px;" id="complianceValue">0</p></div>-->
				<div align="right"><p style="font-family: Arial, Helvetica, sans-serif; font-size: 15pt; white-space: nowrap;" id="softwareCount">Applications</p></div>
				<div align="right"><p style="font-family: Arial, Helvetica, sans-serif; font-size: 15pt; white-space: nowrap;" id="computerCount">Systems</p></div>
			      
			   </div>
		       </div>
            </div>


<!-- Compliance section end -->
<!-- legend chart start -->
<div>
<div id="legendDiv" style="width:280px; height:230px; overflow:auto;" class="legend"></div>
</div>
<!-- legend chart end -->
</div>
<!-- start table at bottom of screen -->
         <div class="span-24 last">
            <hr style="width:900px;"/>
            <div style=" margin-top:8px; width:920px;" id="mainDiv">
               
			   
			   <table id="microsoftOfficeVersionTable" style="border:1px solid lightgrey;">
                  <thead>
                     <tr>
	                    <th class="hiddenColumn">ID</th>
		                <th>Computer</th>
						<th>Users</th>
		                <th class="hiddenColumn">Application Full Name</th>
		                <th>Application</th>
		                <th>Version</th>
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






