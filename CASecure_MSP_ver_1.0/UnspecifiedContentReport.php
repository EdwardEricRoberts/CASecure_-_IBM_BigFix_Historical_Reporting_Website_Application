<?php
require_once './includes/authenticate.php';
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
      <title>Unspecified Content Report</title>

	  
	  <?php require 'includes/headerRevised_2.php'; ?>
      <?php require 'includes/navigationRevised.php'; ?>
<!-- start breadcrumbs -->                
	  <div class="span-24">
         <div style="background-color:#cacaca; width:100%; margin-top:2px; height:23px; line-height:23px;"> 
            <span style="text-align:center;">
               <img src="includes/breadcrumbs_start.jpg" style="height:23px;">
               <span style="z-index:0; position:absolute;"><a href="Reporting.php" class="breadcrumblink">Reporting</a> &nbsp;&raquo;&nbsp; Unspecified Content Report</span>
            </span>
         </div>
      </div>
<!-- end breadcrumbs -->

      <br>
      <div class="span-24 last">
	     <div class="pagetitle2" style="margin-bottom:20px; margin-left:10px; float:left">Unspecified Content Report<br>
		 </div>	  
	  </div>
<script>
	function numberWithCommas(x) {
		return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}	
	
	document.getElementById("reportingNav").className = "current-menu-item";
	
	var chartHeight = 250;
	var chartWidth = 400;
	
	var curUser = "<?php echo $_SESSION['bigfixuser']; ?>"; //"APIAdmin";
	var password = "<?php echo $_SESSION['bigfixpassword']; ?>"; //"AllieCat7";
	var server = "<?php echo $_SESSION['bigfixserver']; ?>"; //"bigfix.internal.cassevern.com";
	// HTTP encode periods so as to not ruin the URL for the AJAX call
	server = server.replace(/\./g, "%2E");
	
	var computerGroup = "<?php echo $_GET['cg']; ?>";
	
	// AJAX call for the Windows Update Status Report
	var unspecifiedContentProxy = "proxies/UnspecifiedContentReport.php?user=" + curUser + "&pass=" + password + "&serv=" + server + "&cg=" + computerGroup;
	xmlTableParser(unspecifiedContentProxy, "#unspecifiedContentTable");
	
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
						if (j == 0) {
							rowHTML += '<td style="display:none;">' + xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue + '</td>';
						}
						else if (j == 1) {
							rowHTML += '<td style="word-wrap:break-word;">' + xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue + '</td>';
						}
						else {
							rowHTML += '<td style="word-wrap:break-word;">' + xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue + '</td>';
						}
					}
					rowHTML += '</tr>';
					$(tableID).append(rowHTML); 
				}
				$(document).ready( function () {
					$.fn.dataTable.moment( 'ddd, MMM DD, YYYY' );
					$('#unspecifiedContentTable').dataTable({
                           dom: 'Blfrtip',
						   
                           buttons: [
                               'copy', 'csv', 'excel', 'print'
                           ]
                        } );
                   } );
<!-- end datatable -->
				
				var a = {};
				var patchCount = 0;
				$(tableID + ' tr td:first-child').each(function(){
					if (!a[$(this).text()]) {
						patchCount++;
						a[$(this).text()] = true;
					}
				});
				document.getElementById("patchCount").innerHTML = (patchCount == 1 ? "1 Patch" : numberWithCommas(patchCount) + " Patches");
				
				$("#wLoad").remove();
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
				rowHTML = "<li><a href='UnspecifiedContentReport.php?cg=" + xmlDoc.getElementsByTagName("Answer")[i].childNodes[0].nodeValue + "'>" + xmlDoc.getElementsByTagName("Answer")[i].childNodes[0].nodeValue + "</a></li>";
				$("#computerGroupList").append(rowHTML);
			}
		}
	}
	
</script>
<!-- start page content -->

	<div class="container">
	
<!-- start chart top left of page  -->
		<div class="span-15 append-1">
			<div id="wLoad">
				<br><img src="includes/loading.gif">
			</div>
			<div>
				<div>
					<span style="font-size:13px">Total:</span>
				
					<span style="padding-left:30px; font-family: Arial, Helvetica, sans-serif; font-size: 18px; white-space: nowrap;"  id="patchCount">Patches</span>
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
			<div style="width:200px;">
				Current Computer Group: <br>
				<span id="currentGroup">
					<b>
						<?php echo $_GET['cg']; ?>
					</b>
				</span>
			</div> 
<!-- Current computer group dynamic label end -->

		</div>

<!-- start table at bottom of screen -->
		<div class="span-24 last">		 
			<div style=" margin-top:8px; width:920px;" id="mainDiv">		 
				<table id="unspecifiedContentTable" style="border:1px solid lightgrey; ">
					<thead>
						<tr>
							<th style="display:none;">Patch ID</th>
							<th>Patch Name and KB Article</th>
							<th>Source Release Date</th>
							<th>Re-mediated Systems</th>
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

     		 







