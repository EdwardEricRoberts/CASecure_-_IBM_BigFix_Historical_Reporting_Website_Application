<?php
require_once './includes/authenticate.php';
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
      <title>Application Software Count Report</title>


	  <?php require 'includes/headerRevised_2.php'; ?>
      <?php require 'includes/navigationRevised.php'; ?>

<!-- start breadcrumbs -->                
	  <div class="span-24">
         <div style="background-color:#cacaca; width:100%; margin-top:2px; height:23px; line-height:23px;"> 
            <span style="text-align:center;">
               <img src="includes/breadcrumbs_start.jpg" style="height:23px;">
               <span style="z-index:0; position:absolute;"><a href="Reporting.php" class="breadcrumblink">Reporting</a> &nbsp;&raquo;&nbsp; Application Software Count Report</span>
            </span>
         </div>
      </div>
<!-- end breadcrumbs -->
      <br>
      <div class="span-24 last">
	     <div class="pagetitle2" style="margin-bottom:20px; margin-left:10px; float:left">Application Software Count Report<br>
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
	var severeVulnerabilityProxy = "proxies/ApplicationSoftwareCountReport.php?user=" + curUser + "&pass=" + password + "&serv=" + server + "&cg=" + computerGroup;
	xmlTableParser(severeVulnerabilityProxy, "#applicationSoftwareCountTable");
	
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
				var rowFullApp = "";
				for (var i = 0; i < tupleCount; i++) {
					rowHTML = '<tr>';
					for (var j = 0; j < columnCount; j++) {
						if (j == 0) {
							rowFullApp = xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue;
							rowHTML += '<td nowrap style="display:none;">' + rowFullApp + '</td>';
						}
						else if (j==3) {
							rowHTML += '<td nowrap><a href="http://localhost/CASecure_MSP_ver_1.0/ApplicationSystemsReport.php?app=' + httpEncode(rowFullApp) + '&cg=' + computerGroup + '">' + xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue + '</a></td>';
						}
						else {
							rowHTML += '<td nowrap>' + xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue + '</td>';
						}
					}
					rowHTML += '</tr>';
				$(tableID).append(rowHTML);}
				     $(document).ready( function () {
				        $('#applicationSoftwareCountTable').dataTable({
                           dom: 'Blfrtip',
						   
                           buttons: [
                               'copy', 'csv', 'excel', 'print'
                           ]
                        } );
                   } );
			    
<!-- end datatable -->
				//
				//var appCountArray = [0,0];
				var appCountTable = document.getElementById('applicationSoftwareCountTable');
				
				var a = {};
				var softwareCount = 0;
				$(tableID + ' tr td:first-child+td').each(function(){
					if (!a[$(this).text()]) {
						softwareCount++;
						a[$(this).text()] = true;
					}
				});
				
				document.getElementById("softwareCount").innerHTML = (softwareCount == 1 ? "1 Total Application" : numberWithCommas(softwareCount) + " Total Applications");
				
				var a = {};
				var versionCount = 0;
				$(tableID + ' tr td:first-child').each(function(){
					if (!a[$(this).text()]) {
						versionCount++;
						a[$(this).text()] = true;
					}
				});
				
				document.getElementById("versionsCount").innerHTML = (versionCount == 1 ? "1 Total Application Version" : numberWithCommas(versionCount) + " Total Application Versions");
				
				var softwareCount = 0;
				
				$("#wLoad").remove();
			}
			
		}
		
		var compCountProxy = "proxies/ApplicationSoftwareComputerCount.php?user=" + curUser + "&pass=" + password + "&serv=" + server + "&cg=" + computerGroup;
		var computerCount = 0;
		
		var compCountRequest = new XMLHttpRequest();
		compCountRequest.open("GET", compCountProxy, true);
		compCountRequest.send();
		compCountRequest.onreadystatechange = function() {
			if ((this.readyState === 4) && (this.status === 200)) {
				compCountXmlString = this.responseText.toString();
				var badQuery = compCountXmlString.substring((parseInt(compCountXmlString.search("Resource"))-1), (parseInt(compCountXmlString.search('Result'))-5));
				compCountXmlString = compCountXmlString.replace(badQuery, '');
				var compParser = new DOMParser();
				var compCountXmlDoc = compParser.parseFromString(compCountXmlString,"text/xml");
				var computerCount = compCountXmlDoc.getElementsByTagName("Answer")[0].childNodes[0].nodeValue;
				document.getElementById("computerCount").innerHTML = (computerCount == 1 ? "1 Total System" : numberWithCommas(computerCount) + " Total Systems");
			}
			else {
				document.getElementById("computerCount").innerHTML = "0 Total Systems";
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
				rowHTML = "<li><a href='ApplicationSoftwareCountReport.php?cg=" + xmlDoc.getElementsByTagName("Answer")[i].childNodes[0].nodeValue + "'>" + xmlDoc.getElementsByTagName("Answer")[i].childNodes[0].nodeValue + "</a></li>";
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
	        <div id="severeVulnerabilityChart" style="height:110px; white-space:nowrap; overflow:hidden; width:400px; margin-bottom:20px; "> 
	           <div id="wLoad">
		          <br><img src="includes/loading.gif" style="margin-top:-50px;">
	           </div>
			   
				<div style="margin-top:10px;">
					<div style="padding-left:50px; font-family: Arial, Helvetica, sans-serif; font-size: 14pt; white-space: nowrap;" id="versionsCount">Total Application Versions</div>
					<div style="padding-left:50px; font-family: Arial, Helvetica, sans-serif; font-size: 14pt; white-space: nowrap;" id="softwareCount"> Total Applications</div>
					<div style="padding-left:50px; font-family: Arial, Helvetica, sans-serif; font-size: 14pt; white-space: nowrap;" id="computerCount">Total Systems</div>
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
			
			

         </div>

<!-- start table at bottom of screen -->
<div class="span-24 last">
		 <hr style="width:900px;"/>
            <div style=" margin-top:8px; width:920px;" id="mainDiv"> 
<table id="applicationSoftwareCountTable">
			      <thead>
				     <tr>
	                    <th style="display:none;">Application Full Name</th>
						<th>Application</th>
						<th>Version</th>
						<th>System Count</th>
					 </tr>
				  </thead>
				  <tbody id="resultsHere">
				  </tbody>
               </table>
</div></div>
<!-- end table bottom of screen -->	




<?php require 'includes/footer.php'; ?>
               
</div>

