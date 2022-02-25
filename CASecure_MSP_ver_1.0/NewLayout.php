<?php
require_once './includes/authenticate.php';
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
      <title>CASecure</title>

<?php require 'includes/headerRevised.php'; ?>
<?php require 'includes/navigationRevised.php'; ?>
<?php require 'includes/alertRevised.php'; ?>                        
                 
                    
<!-- start breadcrumbs -->
                
	     <div class="span-24">
            <div style="background-color:#cacaca; width:100%; margin-top:2px; height:23px; line-height:23px;">
                  
               <span style="text-align:center;">
                  <img src="includes/breadcrumbs_start.jpg" style="height:23px;">
                  <span style="z-index:0; position:absolute;">Dashboard</span>
               </span>
            </div>
         </div>
<!-- end breadcrumbs -->
                               		     
      </div> 
      	
      <br>
      <div class="container">
	     <div class="span-24">
	        <div class="span-5">
               <div class="pagetitle2">&nbsp;&nbsp;Dashboard</div>
	        </div>
            

<script>

document.getElementById("dashboardNav").className = "current-menu-item";

	var chartHeight = 250;
	var chartWidth = 450;
	
	var curUser = "<?php echo $_SESSION['bigfixuser']; ?>"; //"APIAdmin";
	var password = "<?php echo $_SESSION['bigfixpassword']; ?>"; //"AllieCat7";
	var server = "<?php echo $_SESSION['bigfixserver']; ?>"; //"bigfix.internal.cassevern.com";
	// HTTP encode periods so as to not ruin the URL for the AJAX call
	server = server.replace(/\./g, "%2E");
	
	// AJAX call for the Inventory Report
	var inventoryProxy = "proxies/InventoryReport.php?user=" + curUser + "&pass=" + password + "&serv=" + server + "&cg=All Machines";
	xmlTableParser(inventoryProxy, "#inventoryTable");
	
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
						if (tableID == "#inventoryTable") {
							if (j == 0 || j == 5) continue;
						/*	if (j == 2) {
								if ((xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue).substring(0,3) == "Win") {
									rowHTML += 
										'<td nowrap>' + 
											(xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue) + //.substring(0, indexOf(" "))
											//'<br>' +
											//(xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue).substring(indexOf(" "), (xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue).length) +
										'</td>';
									//alert((xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue).substring(0, indexOf(" ")));
								}
								else {
									rowHTML += '<td>' + (xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue) + '</td>';
								}
							}
						*/	if(j == 3 || j == 6) {
								rowHTML += '<td nowrap>' + (xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue) + '</td>';
							}
							else {
								rowHTML += '<td>' + xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue + '</td>';
							}
						}
						else {
							rowHTML += '<td>' + xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue + '</td>';
						}
					}
					rowHTML += '</tr>';
					$(tableID).append(rowHTML);
					$(document).ready( function () {
						$('#inventoryTable').dataTable();
					} );
				}
			}
		}
	}

</script>

<div class="span-24">	
<br>
<table style="border:1px solid lightgrey;">
	<tr>
		<td align="center">
			<div style=" margin-bottom:-20; font-size:14pt; font-weight:bold; text-align:center;">System Inventory</div>	

			
			<table id="inventoryTable" class="hover">
				<thead><tr>
					<!-- <th nowrap style="align:left; ">Computer ID</th> -->
					<th nowrap style="align:left; cursor:pointer;">Computer</th>
					<th nowrap style="align:left; cursor:pointer;">Users</th>
					<th nowrap style="align:left; cursor:pointer;">Operating System</th>
					<th nowrap style="align:left; cursor:pointer;">IP Addresses</th>
					
					<th nowrap style="align:left; cursor:pointer;">Last Report Time</th>
					</tr>
					</thead>
				</thead>
				<tbody id="resultsHere">
				
				</tbody>
			</table>
		</td>
	</tr>
</table>
			<!--</font>-->
		</div>
	<!--</div>-->
	<br><br><br><br><br><br>
	<!--<hr width="100%" noshade> -->
	<br><br><br><br>
	<div class="span-10 prepend-1">
	<iframe src="SourceSeverityChart.php" style="width:390px; height:250px; border:1px solid lightgrey;" scrolling="no"></iframe>
	</div>
	<div class="span-10 last prepend-1">
		<iframe src="WindowsUpdateStatusChart.php" style="width:390px; height:250px;border:1px solid lightgrey;" scrolling="no"></iframe>
	</div>
	
	<div class="span-24 last">&nbsp;</div>
	<div class="span-24 last prepend-6 prepend-top-1">
	 <iframe src="WindowsPatchComplianceChart.php" style="width:425px; height:290px;border:1px solid lightgrey;" scrolling="no"></iframe>
	</div>
</div>


<div class="container prepend-top">
         <hr />
            <p class="alt right">
               &copy; 2019 CAS Severn, Inc.	
            </p>
      </div>

<!-- </div> -->

<?php require 'includes/footer.php'; ?>