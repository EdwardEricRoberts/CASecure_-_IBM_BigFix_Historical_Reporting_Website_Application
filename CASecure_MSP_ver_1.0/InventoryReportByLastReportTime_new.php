<?php
require_once './includes/authenticate.php';
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
      <title>Inventory Report by Last Report Time</title>

	
      <link rel="stylesheet" href="bootstrap.css">
      <!--<link rel="stylesheet" type="text/css" href="TABLE_FORMAT_1.css">-->
	  <?php require 'includes/headerRevised_2.php'; ?>
      <?php require 'includes/navigationRevised.php'; ?>

<!-- start breadcrumbs -->                
	  <div class="span-24">
         <div style="background-color:#cacaca; width:100%; margin-top:2px; height:23px; line-height:23px;"> 
            <span style="text-align:center;">
               <img src="includes/breadcrumbs_start.jpg" style="height:23px;">
               <span style="z-index:0; position:absolute;"><a href="Reporting.php" class="breadcrumblink">Reporting</a> &nbsp;&raquo;&nbsp; Inventory Report by First Report Time</span>
            </span>
         </div>
      </div>
<!-- end breadcrumbs -->
      <br>
      <div class="span-24 last">
	     <div class="pagetitle2" style="margin-bottom:20px; margin-left:10px; float:left">Inventory Report by Last Report Time<br>
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
	var inventoryByFirstReportTimeProxy = "proxies/InventoryReportWithFirstReportTime.php?user=" + curUser + "&pass=" + password + "&serv=" + server + "&cg=" + computerGroup;
	
	httpEncode(inventoryByFirstReportTimeProxy);
	xmlTableParser(inventoryByFirstReportTimeProxy, "#inventoryByFirstReportTimeTable");
	
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
						if (j == 0) {
							rowHTML += '<td nowrap style="display:none;">' + xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue + '</td>';
						}
						else {
							rowHTML += '<td nowrap>' + xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue + '</td>';
						}
					}
					rowHTML += '</tr>';
					$(tableID).append(rowHTML);
			    }
				
				
				$.fn.dataTable.moment = function ( format, locale ) {
                   var types = $.fn.dataTable.ext.type;
 
                // Add type detection
                   types.detect.unshift( function ( d ) {
                      return moment( d, format, locale, true ).isValid() ?
                         'moment-'+format :
                         null;
                } );
 
                // Add sorting method - use an integer for the sorting
                   types.order[ 'moment-'+format+'-pre' ] = function ( d ) {
                      return moment( d, format, locale, true ).unix();
                   };
                };
				$(document).ready( function () {
					    $.fn.dataTable.moment( 'ddd, MMM DD, YYYY' );
				        $('#inventoryByFirstReportTimeTable').dataTable({
                           dom: 'Blfrtip',
						   
                           buttons: [
                               'copy', 'csv', 'excel', 'print'
                           ]
						   
                        } );
                   } );
				
				
<!-- end datatable -->
				//
				//var appCountArray = [0,0];
				//var appCountTable = document.getElementById('inventoryByFirstReportTimeTable');
				
				//var appCountRows = appCountTable.rows, 
				//	appCountRowCount = appCountRows.length;
				
				var a = {};
				var computerCount = 0;
				$(tableID + ' tr td:first-child').each(function(){
					if (!a[$(this).text()]) {
						computerCount++;
						a[$(this).text()] = true;
					}
				});
				//var computerCount = appCountRowCount - 1;
				document.getElementById("computerCount").innerHTML = (computerCount == 1 ? "1 System" : numberWithCommas(computerCount) + " Systems");
				
				
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
				rowHTML = "<li><a href='InventoryReportByFirstReportTime.php?cg=" + xmlDoc.getElementsByTagName("Answer")[i].childNodes[0].nodeValue + "'>" + xmlDoc.getElementsByTagName("Answer")[i].childNodes[0].nodeValue + "</a></li>";
				$("#computerGroupList").append(rowHTML);
			}
		}
	}
	
	/* Function to encode user generated fields to be URL friendly */
	function httpEncode(input) {
		input = input.replace(/%/g,  "%25");
		input = input.replace(/\t/g, "%09");
		input = input.replace(/\n/g, "%0A");
		input = input.replace(/\r/g, "%0D");
		input = input.replace(/\s/g, "%20");
		input = input.replace(/\!/g, "%21");
		input = input.replace(/\"/g, "%22");
		input = input.replace(/\#/g, "%23");
		input = input.replace(/\$/g, "%24");
		input = input.replace(/\&/g, "%26");
		input = input.replace(/\'/g, "%27");
		input = input.replace(/\*/g, "%2A");
		input = input.replace(/\+/g, "%2B");
		input = input.replace(/\,/g, "%2C");
		input = input.replace(/\-/g, "%2D");
		input = input.replace(/\./g, "%2E");
		input = input.replace(/\//g, "%2F");
		input = input.replace(/:/g,  "%3A");
		input = input.replace(/;/g,  "%3B");
		input = input.replace(/\</g, "%3C");
		input = input.replace(/\=/g, "%3D");
		input = input.replace(/\>/g, "%3E");
		input = input.replace(/\?/g, "%3F");
		input = input.replace(/@/g,  "%40");
		input = input.replace(/\\/g, "%5C");
		input = input.replace(/\_/g, "%5F");
		input = input.replace(/~/g,  "%7E");
		
		return input;
	}
	
</script>

<br>
					
<!-- start page content -->
      <div class="container">

	  
<!-- start chart top left of page "Total Systems Vulnerability" -->
		 <div class="span-15 append-1" >    
	        <div id="severeVulnerabilityChart" style="height:100px; white-space:nowrap; overflow:hidden; width:400px; margin-bottom:20px; "> 
	           <div id="wLoad">
		          <br><img src="includes/loading.gif">
	           </div>
			   <div>
					<div>
						<span style="font-size:13px">Total:</span>
					
						<span style="padding-left:30px; font-family: Arial, Helvetica, sans-serif; font-size: 18px; white-space: nowrap;"  id="computerCount">Systems</span>
					</div>
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
			         <?php echo $_GET['cg']; ?>
		          </b>
	           </span>
            </div> 
			
<!-- Current computer group dynamic label end -->	
			<!--
			<div style="margin-top:20px;">
 
				<p style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; white-space: nowrap;" id="computerCount">Systems</p>
			</div>
			-->
         </div>

<!-- start table at bottom of screen -->
<div class="span-24 last">
		 <hr style="width:900px;"/>
            <div style=" margin-top:8px; width:920px;" id="mainDiv"> 
<table id="inventoryByFirstReportTimeTable">
			      <thead>
				     <tr>
						<th style="display:none;">Computer ID </th>
						<th>Computer</th>
						<th>Users</th>
						<th>Operating System</th>
						<th>IP Addresses</th>
						<th>First Report Time</th>
					 </tr>
				  </thead>
				  <tbody>
				  </tbody>
               </table>
</div></div>
<!-- end table bottom of screen -->	




<?php require 'includes/footer.php'; ?>
               
</div>

