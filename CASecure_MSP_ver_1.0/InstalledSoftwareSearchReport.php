<?php
require_once './includes/authenticate.php';
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
      <title>Installed Software Search Report</title>

	  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.js"></script>
      <script src="bootstrap.js"></script>
      <link rel="stylesheet" href="bootstrap.css">
      <link rel="stylesheet" type="text/css" href="TABLE_FORMAT_1.css">
	  
	  <style>
		.hidden {
			display: none;
		}
	  </style>
	  
	  <?php require 'includes/headerRevised_2.php'; ?>
      <?php require 'includes/navigationRevised.php'; ?>

<!-- start breadcrumbs -->                
	  <div class="span-24">
         <div style="background-color:#cacaca; width:100%; margin-top:2px; height:23px; line-height:23px;"> 
            <span style="text-align:center;">
               <img src="includes/breadcrumbs_start.jpg" style="height:23px;">
               <span style="z-index:0; position:absolute;"><a href="Reporting.php" class="breadcrumblink">Reporting</a> &nbsp;&raquo;&nbsp; Installed Software Search Report</span>
            </span>
         </div>
      </div>
<!-- end breadcrumbs -->
      <br>
      <div class="span-24 last">
	     <div class="pagetitle2" style="margin-bottom:20px; margin-left:10px; float:left">Installed Software Search Report<br>
		 </div>	  
	  </div>
<script>
	function numberWithCommas(x) {
		return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}	
	
	document.getElementById("reportingNav").className = "current-menu-item";
	
	var chartHeight = 250;
	var chartWidth = 400;
	
	var curUser = "<?php echo $_SESSION['bigfixuser']; ?>";//"APIAdmin";
	var password = "<?php echo $_SESSION['bigfixpassword']; ?>";//"AllieCat7";
	var server = "<?php echo $_SESSION['bigfixserver']; ?>";//"bigfix.internal.cassevern.com";
	// HTTP encode periods so as to not ruin the URL for the AJAX call
	server = server.replace(/\./g, "%2E");
	
	var computerGroup = "<?php echo $_GET['cg']; ?>";
	var userSearch = "<?php echo $_GET['app'] ?>";
	//$('#searchValue').append(userSearch);
	
	if (userSearch != "") {3
		// AJAX call for the Windows Update Status Report
		var severeVulnerabilityProxy = "proxies/SoftwareComputerSearchReport.php?user=" + curUser + "&pass=" + password + "&serv=" + server + "&cg=" + computerGroup + "&app=" + httpEncode(userSearch.toLowerCase());
		xmlTableParser(severeVulnerabilityProxy, "#searchResultsTable");
		//document.getElementsByClassName("container").style.display = "initial";
	}
	else {
		$("#wLoad").remove();
	}
	
	//$('#searchOutput').removeClass("hidden");
	$('#searchOutput').css("display", 'none');//$('#searchOutput').css("display") === 'none' ? '' : 'none');
	$('#searchOutput').toggle();
	
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
						if (j == 0 || j==3 || j==4 || j==5 || j==6 || j==7) {
							rowHTML += '<td nowrap style="display:none;">' + xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue + '</td>';
						}
						else {
							rowHTML += '<td nowrap>' + xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue + '</td>';
						}
					}
					rowHTML += '</tr>';
					$(tableID).append(rowHTML);
			    }
				$(document).ready( function () {
					$('#searchResultsTable').dataTable({
						
						
						"order": [[7, 'asc']]
						
					});
				});
				
				
<!-- end datatable -->
				
				var resultsCount = $(tableID + ' tr').length - 1;
				//document.getElementById("resultsCount").innerHTML = (resultsCount == 1 ? "1 Result Found" : numberWithCommas(resultsCount) + " Results Found");
				
				var a = {};
				var systemCount = 0;
				$(tableID + ' tr td:first-child').each(function(){
					if (!a[$(this).text()]) {
						systemCount++;
						a[$(this).text()] = true;
					}
				});
				
				//document.getElementById("computerCount").innerHTML = (systemCount == 1 ? "1 System" : numberWithCommas(systemCount) + " Systems");
				
				a = {};
				var softwareCount = 0;
				$(tableID + ' tr td:first-child+td+td+td+td+td+td+td+td').each(function(){
					if (!a[$(this).text()]) {
						softwareCount++;
						a[$(this).text()] = true;
					}
				});
				
				//document.getElementById("softwareCount").innerHTML = (softwareCount == 1 ? "1 Application" : numberWithCommas(softwareCount) + " Applications");
				
				a = {};
				var versionCount = 0;
				$(tableID + ' tr td:first-child+td+td+td+td+td+td+td').each(function(){
					if (!a[$(this).text()]) {
						versionCount++;
						a[$(this).text()] = true;
					}
				});
				
				//document.getElementById("versionsCount").innerHTML = (versionCount == 1 ? "1 Application Version" : numberWithCommas(versionCount) + " Application Versions");
				
				var svg = d3.select("#resultsCount")
					.append('svg')
						//.style('background-color', '#EBEDF2')
						.attr('width', 500)
						.attr('height', "1.5em");
						
				var Tooltip = d3.select("#resultsCount")
					.append("div")
					.style("opacity", 0)
					.attr("class", "tooltip")
					.style("background-color", "white")
					.style("border", "solid")
					.style("border-width", "2px")
					.style("border-radius", "5px")
					.style("padding", "5px")
					
				var resultsText = svg.append('text')
					.attr("id", "resultsText")
					.attr('x', 0)
					.attr('y', 14)
					.attr('text-anchor', 'start')
					.text((resultsCount == 1 ? "1 Result Found" : numberWithCommas(resultsCount) + " Results Found"))
					.on("mouseover", function(d) {
						Tooltip
							.style("opacity", 1)
						d3.select(this)
							.style("stroke", "black")
							.style("opacity", 1)
					})
					.on("mousemove", function(d) {
						Tooltip
							.html(
								"<b>" + (systemCount == 1 ? "1 System" : numberWithCommas(systemCount) + " Systems") + "</b>" + 
								"<br>" + 
								"<b>" + (softwareCount == 1 ? "1 Application" : numberWithCommas(softwareCount) + " Applications") + "</b>" + 
								"<br>" + 
								"<b>" + (versionCount == 1 ? "1 Application Version" : numberWithCommas(versionCount) + " Application Versions") + "</b>" 
							)
							//.attr("transform", "translate(" + d3.mouse(this)[0] + 70 + "," + d3.mouse(this)[1] + 40 + ")")
							.style("left", (d3.mouse(this)[0]+150) + "px")
							.style("top", (d3.mouse(this)[1]+15) + "px")
						d3.select(this)
							.style("cursor", "default")
					})
					.on("mouseleave", function(d) {
						Tooltip
							.style("opacity", 0)
						d3.select(this)
							.style("stroke", "none")
							.style("opacity", 0.8)
					});
				
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
				rowHTML = "<li><a href='InstalledSoftwareSearchReport.php?cg=" + xmlDoc.getElementsByTagName("Answer")[i].childNodes[0].nodeValue + "&app=" + userSearch + "'>" + xmlDoc.getElementsByTagName("Answer")[i].childNodes[0].nodeValue + "</a></li>";
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

<div>
	<form action="InstalledSoftwareSearchReport.php" method="get" target="_self">
		<table>
			<tr>
				<td>
					<textarea name="cg" value="All Machines" style="display:none;">All Machines</textarea>
					<input type="text" name="app" id="searchInput" value="<?php echo $_GET['app'] ?>" placeholder="Search by Application Name or Version Number for Installed Computers ..." size="120">&nbsp;&nbsp;
				</td>
				<td>
					<button type="submit">Search</button>
				</td>
			</tr>
		</table>
	</form>
</div>

<br>
<!-- start page content -->
      <div class="container">

	  <div id="searchOutput"> 
<!-- start chart top left of page "Total Systems Vulnerability" -->
		 <div class="span-15 append-1" >    
	        <div> 
	          <div>
			   <span>Search Parameter:</span>
			   <span style="padding-left:10px; padding-bottom:-50px; font-family: Arial, Helvetica, sans-serif; font-size: 18px; " id="searchValue"><?php echo $_GET['app'] ?></span>
			</div>
			<div>
			   
			   <span style="position:absolute; margin-top:20px; margin-left:70px;">Total:</span>
			   <span style="position:absolute; margin-top:20px; padding-left:112px; font-family: Arial, Helvetica, sans-serif; font-size: 18px; " id="resultsCount"></span>
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
            </div> <br>
			
<!-- Current computer group dynamic label end -->	
			<!--
			<div style="margin-top:20px;">
				<p style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; white-space: nowrap;" id="computerCount"> Systems</p>
				<p style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; white-space: nowrap;" id="softwareCount"> Applications</p>
				<p style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; white-space: nowrap;" id="versionsCount"> Application Versions</p>
			</div>
			-->
         </div>

<!-- start table at bottom of screen -->
<div class="span-24 last">
		 <hr style="width:900px;"/>
            <div style=" margin-top:8px; width:920px;" id="mainDiv"> 
<table id="searchResultsTable">
			      <thead>
				     <tr>
						<th style="display:none;">Computer ID</th>
						<th>Computer</th>
						<th>Users</th>
						<th style="display:none;">Operating System</th>
						<th style="display:none;">IP Addresses</th>
						<th style="display:none;">CPU</th>
						<th style="display:none;">Last Report Time</th>
	                    <th style="display:none;">Application Full Name</th>
						<th>Application</th>
						<th>Version</th>
					 </tr>
				  </thead>
				  <tbody id="resultsHere">
				  </tbody>
               </table>
			   
</div></div>
<!-- end table bottom of screen -->	
<?php require 'includes/footer.php'; ?>
</div>

</div>


               


