<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
      <title>CASecure > Reporting > Microsoft Patch Compliance Executive Summary</title>



	  
	  <style>
		.dropdown-check-list {
			display: inline-block;
			width: 100%;
		}
		
		.dropdown-check-list:focus {
			outline: 1;
			//background-color: grey;
		}
		
		.dropdown-check-list .anchor {
			width: 73%;
			position: relative;
			cursor: pointer;
			display: inline-block;
			padding-top: 5px;
			padding-left: 10px;
			padding-bottom: 5px;
			padding-right: 50px;
			border: 1px #ccc solid;
			margin-right: 50px;
		}
		
		.dropdown-check-list .anchor::after {
			position: absolute;
			content: "";
			border-left: 1pt solid black;
			border-top: 1pt solid black;
			padding: 5px;
			right: 10px;
			top: 20%;
			-moz-transform: rotate(-135deg);
			-ms-transform: rotate(-135deg);
			-o-transform: rotate(-135deg);
			-webkit-transform: rotate(-135deg);
			transform: rotate(-135deg);
		}
		
		.dropdown-check-list .anchor:active::after {
			right: 8px;
			top: 21%;
		}
		
		.dropdown-check-list ul.items {
			width: 100%;
			padding: 2px;
			display: none;
			margin: 0;
			border: 1px solid #ccc;
			border-top: none;
			position:relative;
			z-index:100;
			margin-right: 50px;
		}
		
		.dropdown-check-list ul.items li {
			list-style: none;
			position:relative;
			z-index:100;
		}
		
		.dropdown-check-list.visible .anchor {
			color: #0094ff;
			//background-color: grey;
		}
		
		.dropdown-check-list.visible .anchor::after {
			position: absolute;
			content: "";
			border-left: 1pt solid black;
			border-top: 1pt solid black;
			padding: 5px;
			right: 10px;
			top: 40%;
			-moz-transform: rotate(45deg);
			-ms-transform: rotate(45deg);
			-o-transform: rotate(45deg);
			-webkit-transform: rotate(45deg);
			transform: rotate(45deg);
		}
		
		.dropdown-check-list.visible .anchor:active::after {
			right: 8px;
			//top: 21%;
		}
		
		.dropdown-check-list.visible .items {
			display: block;
			background-color: white;
			position:relative;
			z-index:100;
		}
	  </style>
	  
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
	     <div class="pagetitle2" style="margin-bottom:20px; margin-left:10px; float:left">Microsoft Patch Compliance Executive Summary<br>
		 </div>	  
	  </div>
<script>
	function numberWithCommas(x) {
		return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}	
	
	document.getElementById("reportingNav").className = "current-menu-item";
	
	var chartHeight = 250;
	var chartWidth = 400;
	
	var curUser = "APIAdmin";
	var password = "AllieCat7";
	var server = "bigfix.internal.cassevern.com";
	// HTTP encode periods so as to not ruin the URL for the AJAX call
	server = server.replace(/\./g, "%2E");
	
	var computerGroups = <?php echo $_GET['cg']; ?>;
	
	//for (var i = 0; i < <?php echo sizeof(json_decode($_GET['cg'])) ?>; i++) {
	//	computerGroups
	//}
	
	// AJAX call for the Windows Update Status Report
	var severeVulnerabilityProxy = "proxies/MicrosoftPatchTuesdayComplianceExecutiveSummaryReport.php?user=" + curUser + "&pass=" + password + "&serv=" + server + "&cg=[" + computerGroups + "]";
	xmlTableParser(severeVulnerabilityProxy, "#executiveSummaryTable");
	
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
				
				var summaryHTML = "";
				
				summaryHTML = '<tr>';
				
				for (var j = 0; j < columnCount; j++) {
					if (j == 0) {
						summaryHTML += '<td nowrap style="display:none;">' + xmlDoc.getElementsByTagName("Answer")[j].childNodes[0].nodeValue + '</td>';
					}
					else if (j==1) {
						summaryHTML += '<td id="summaryTitle" nowrap style="padding-left:1em;">' + xmlDoc.getElementsByTagName("Answer")[j].childNodes[0].nodeValue + '</td>';
					}
					else if (j==7) {
						summaryHTML += '<td id="totalCompliance" nowrap style="text-align:right; padding-right:2em;">' + complianceColor(xmlDoc.getElementsByTagName("Answer")[j].childNodes[0].nodeValue) + '</td>';
					}
					else {
						summaryHTML += numberWithCommas('<td nowrap style="text-align:right; padding-right:3em;">' + xmlDoc.getElementsByTagName("Answer")[j].childNodes[0].nodeValue) + '</td>';
					}
				}
				
				summaryHTML += '</tr>';
				
				$('#executiveSummaryTotalsTable').append(summaryHTML);
				
				$(document).ready( function () {
					$('#executiveSummaryTotalsTable').dataTable();
				});
				
				var rowHTML = "";
				
				for (var i = 1; i < tupleCount; i++) {
					rowHTML = '<tr>';
					for (var j = 0; j < columnCount; j++) {
						if (j == 0) {
							rowHTML += '<td nowrap style="display:none;">' + xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue + '</td>';
						}
						else if (j==1) {
							if (i==(tupleCount-1))
								rowHTML += '<td id="rowTitle" nowrap style="padding-left:1em;">' + xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue + '</td>';
							else 
								rowHTML += '<td nowrap style="padding-left:1em;">' + xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue + '</td>';
						}
						else if (j==7) {
							rowHTML += '<td nowrap style="text-align:right; padding-right:2em;">' + complianceColor(xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue) + '</td>';
						}
						else {
							rowHTML += numberWithCommas('<td nowrap style="text-align:right; padding-right:3em;">' + xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue) + '</td>';
						}
					}
					rowHTML += '</tr>';
					$(tableID).append(rowHTML);
			    }
				
				$(document).ready( function () {
					$('#executiveSummaryTotalsTable').dataTable();
				});
				$(document).ready( function () {
					$('#executiveSummaryTable').dataTable(
					{
                           dom: 'Blfrtip',
						   
                           buttons: [
                               'copy', 'csv', 'excel', 'print'
                           ]
						   
                        } 
					);
				});
				//var tableTitleWidth = document.getElementById("rowTitle");
				//var summaryTitleWidth = document.getElementById("summaryTitle");
				//summaryTitleWidth.style.width = tableTitleWidth.offsetWidth;
				//tableTitleWidth.style.width = tableTitleWidth.offsetWidth;
<!-- end datatable -->
				//
				//var appCountArray = [0,0];
				
				var appCountTable = document.getElementById('executiveSummaryTable');
				
				var appCountRows = appCountTable.rows, 
					appCountRowCount = appCountRows.length;
					
				document.getElementById("complianceValue").innerHTML = document.getElementById("totalCompliance").innerHTML
				
				var a = {};
				var computerCount = 0;
				$(tableID + ' tr td:first-child').each(function(){
					if (!a[$(this).text()]) {
						computerCount++;
						a[$(this).text()] = true;
					}
				});
				//var computerCount = appCountRowCount - 1;
				document.getElementById("computerCount").innerHTML = (computerCount == 1 ? "1 Computer Group" : numberWithCommas(computerCount) + " Computer Groups");
				
				$("#wLoad").remove();
			}
			
		}
		
		
		
	}
	
	function complianceColor(val) {
		var regEx = /^([\d]?[\d]?\d)[%]$/;
		var dateArray = val.match(regEx);
		if (dateArray){
			var compNum = parseInt(dateArray[1]);
			
			if (compNum >= 90) {
				return '<span style="color:#008800;">' + val + '</span>';
			} else if (compNum >= 70 && compNum < 90) {
				return '<span style="color:#15428b;">' + val + '</span>';
			} else if (compNum >= 20 && compNum < 70) {
				return '<span style="color:orange;">' + val + '</span>';
			} else if (compNum >= 0 && compNum < 20) {
				return '<span style="color:#cc3333;">' + val + '</span>';
			} else if (compNum == -99) {
				return '<span style="color:#15428b;">--</b></span>';
			} else {
				return '<span>' + val + '</span>';
			}
		}
		else {
			if (val == "N/A") {
				return '<span style="color:#000000;">' + val + '</span>';
			} else if (val == "Unknown") {
				return '<span style="color:#FF0000;">' + val + '</span>';
			} else {
				return '<span>' + val + '</span>';
			}
		}
	} 
	//
	
	 var compGroup = new XMLHttpRequest();
	     compGroup.open("GET", "proxies/ComputerGroupsCheckList.php?user=" + curUser + "&pass=" + password + "&serv=" + server, true);
	     compGroup.send();
	     compGroup.onreadystatechange = function() {
		 if ((this.readyState === 4) && (this.status === 200)) {
		    xmlCompGrpList = this.responseText.toString();
			var badQuery = xmlCompGrpList.substring((parseInt(xmlCompGrpList.search("Resource"))-1), (parseInt(xmlCompGrpList.search('Result'))-5));
			xmlCompGrpList = xmlCompGrpList.replace(badQuery, '');
			var parser = new DOMParser();
			var xmlDoc = parser.parseFromString(xmlCompGrpList,"text/xml");
			var tupleCount = xmlDoc.getElementsByTagName("Tuple").length;
			var columnCount = xmlDoc.getElementsByTagName("Answer").length / tupleCount;
			var rowHTML = "", groupID = "", groupName = "";
			for (var i = 0; i < tupleCount; i++) {
				for(var j = 0; j < columnCount; j++) {
					if (j == 0)
						groupID = xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue;
					
					else if (j == 1)
						groupName = xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue;
				}
				rowHTML = "<li><input type='checkbox' class='groupSelection' name='bes_computer_groups' id='" + groupID + "' value='" + groupID + "' " + ((computerGroups.indexOf(parseInt(groupID)) > -1) ? "checked" : "") + "/><label>&nbsp;" + groupName + "</label></li>";
				$("#cgItems").append(rowHTML);
			}
			$('#submitButton').click(function() {
				var values = [];
				$('.groupSelection').each(function() {
					var $this = $(this);
					if ($this.is(':checked')) {
						values.push($this.val());
					}
					
				});
				//alert(values);
				window.location = "MicrosoftPatchComplianceExecutiveSummaryReport.php?cg=[" + values + "]";
			});
			
		 }
	  }
	  
	
	
	
	  
	 // 
	  jQuery(function ($) {
		var checkList = $('.dropdown-check-list');
		checkList.on('click', 'span.anchor', function(event){
			var element = $(this).parent();
			
			if ( element.hasClass('visible') )
			{
				element.removeClass('visible');
			}
			else
			{
				element.addClass('visible');
			}
		});
	});
	
	function removeChecked() {
		$("#removeChecked").remove();
		$(".groupSelection").attr("checked", false);
		$("#firstListItem").append('<button id="reChecked" onclick="reChecked()">Select All</button>');
	};
	
	function reChecked() {
		$("#reChecked").remove();
		$(".groupSelection").attr("checked", true);
		$("#firstListItem").append('<button id="removeChecked" onclick="removeChecked()">Remove All</button>');
	};
	
	
	//	var seletedGroups = []; j = 0;
	//	$('.groupSelection').foreach(function(i) {
	//		seletedGroups[i] = this[i].value;
	//	});
	//	alert(seletedGroups);
	$("#submitButton").click(function() {
		var seletedGroups = []; j = 0;
		var inputElements = document.getElementsByClassName('groupSelection');
		for (var i = 0; inputElements[i]; i++) {
			if (inputElements[i].checked) {
				seletedGroups[j] = inputElements[i].value;
				j++;
			}
		}
		alert(seletedGroups);
		//window.location = "MicrosoftPatchComplianceExecutiveSummaryReport.php?cg="
	});
	    
</script>

<br>
					
<!-- start page content -->
      <div class="container">

	  
<!-- start chart top left of page "Total Systems Vulnerability" -->
		<!-- <div class="span-15 append-1" >    
	       <!-- <div id="severeVulnerabilityChart" style="height:100px; white-space:nowrap; overflow:hidden; width:400px; margin-bottom:20px; "> -->
	          
			  
          <!--  </div> -->
        <!-- </div>-->

<!-- end chart top left of page -->	 

        <!-- <div class="span-8 last">-->

<!-- button dropdown start -->















<!-- new dropdown experiment -->
<div class="span-23 append-1" >

    <select name="multicheckbox[]" multiple="multiple" class="4col formcls" id="cgItems" class="items">
            
    </select>


	
	
	
	
	
	
<script src="includes/jquery.multiselect.js"></script>
<script>
$('select[multiple]').multiselect({
    columns: 4,
    placeholder: 'Select computer groups',
    selectAll : true
});
</script>




<style>
.ms-options-wrap > .ms-options > ul label {
    position: relative;
    display: inline-block;
    width: 100%;
    padding: 4px;
    margin: 1px 0;
    list-style: none;
    
}

.ms-options-wrap > .ms-options > ul li.selected label,
.ms-options-wrap > .ms-options > ul label:hover {
     background-color: #9DCEFF;
    color:#fff;
    list-style: none;
}
</style>

<!-- end new dropdown experiment -->












<!--

			<div id="cgList" class="dropdown-check-list" style="float: left; position: relative;">
				<span class="anchor">Select Computer Groups</span>
				<ul id="cgItems" class="items">
					<li id="firstListItem"><button id="removeChecked" onclick="removeChecked()">Remove All</button></li>
					
				</ul>
			</div>
			
			<button id="submitButton">submit</button>-->
<!-- button dropdown end --> 

<!-- Compliance section start -->

	        <div style="top:0px; right:0px">
		       <div>
			   <div align="right" style="margin-top:5px; width:25%; top:0px; left:90px;">
			      
			         <span style="font-family: Arial, Helvetica, sans-serif; font-size:15pt;">Compliance</span><br>
			      
				     <span style="font-family: Arial, Helvetica, sans-serif; font-size:35pt;" id="complianceValue">0</span><br>
			      
				     <span style="font-family: Arial, Helvetica, sans-serif; font-size:14pt; white-space:nowrap;" id="computerCount">Computer Groups</span><br>
			      
			   </div>
		       </div>
            </div>
 <!--Compliance section end -->		
</div>
<!-- start table at bottom of screen -->
<div class="span-24 last">
	<hr style="width:900px;"/>
	<div style=" margin-top:8px; width:920px;" id="mainDiv"> 
		<table id="executiveSummaryTotalsTable">
			<thead>
				<tr>
					<th style="display:none; text-align:center;">Summary ID </th>
					<th style="text-align:center;">Summary</th>
					<th style="text-align:center;">Total Systems</th>
					<th style="text-align:center;">Updated Systems</th>
					<th style="text-align:center;">Applicable Patches</th>
					<th style="text-align:center;">Installed Patches</th>
					<th style="text-align:center;">Outstanding Patches</th>
					<th style="text-align:center;">Compliance</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
		<br>
		<table id="executiveSummaryTable">
			<thead>
				<tr>
					<th style="display:none; text-align:center;">Computer Group ID </th>
					<th style="text-align:center;">Computer Group</th>
					<th style="text-align:center;">Total Systems</th>
					<th style="text-align:center;">Updated Systems</th>
					<th style="text-align:center;">Applicable Patches</th>
					<th style="text-align:center;">Installed Patches</th>
					<th style="text-align:center;">Outstanding Patches</th>
					<th style="text-align:center;">Compliance</th>
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

