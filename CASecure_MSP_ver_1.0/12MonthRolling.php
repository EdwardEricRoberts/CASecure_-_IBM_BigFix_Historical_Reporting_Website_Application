<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
      <title>CASecure > Reporting > 12 Month Rolling Compliance</title>

      <?php require 'includes/headerRevised_2.php'; ?>
      <?php require 'includes/navigationRevised.php'; ?>
                        
                 
                    
<!-- start breadcrumbs -->
                
	  <div class="span-24">
         <div style="background-color:#cacaca; width:100%; margin-top:2px; height:23px; line-height:23px;">      
            <span style="text-align:center;">
               <img src="includes/breadcrumbs_start.jpg" style="height:23px;">
               <span style="z-index:0; position:absolute;"><a href="Reporting.php" class="breadcrumblink">Reporting</a> &nbsp;&raquo;&nbsp; 12 Month Rolling Compliance</span>
            </span>
         </div>
      </div>
<!-- end breadcrumbs -->      	
      <br>
      <div class="span-24 last">
	     <div class="span-24">
	        <div class="pagetitle2">&nbsp;&nbsp;12 Month Rolling Compliance</div>
	     </div>
      </div> 

      <script>

         document.getElementById("dashboardNav").className = "current-menu-item";

	     var chartHeight = 250;
	     var chartWidth = 450;
	
	     var curUser = "APIAdmin";
	     var password = "AllieCat7";
	     var server = "bigfix.internal.cassevern.com";
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
						   else if (j == 6) {
							   rowHTML += '<td>' + complianceColor(xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue) + '</td>';
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
			      }
				  $(document).ready( function () {
				     $('#inventoryTable').dataTable();
				  } );
				  var computerCount = $(tableID + " tr").length - 1;
				$('#sysCount').append("(" + numberWithCommas(computerCount) + " Systems)");
				  
			   }
		    }
	     }
		 
		function numberWithCommas(x) {
			return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		}
		
		function complianceColor(val) {
		var regEx = /^([A-Z][a-z][a-z]),\s([A-Z][a-z][a-z])\s(\d\d),\s(\d\d\d\d)\s<br>(\d\d):(\d\d):(\d\d)\s([AP]M)$/;
		var dateArray = val.match(regEx);
		if (dateArray){
			if (dateArray[0] == "") { return '<span>' + val + '</span>';}
			var newDate = new Date(dateArray[1] + ", " + dateArray[2] + " " + dateArray[3] + ", " + dateArray[4] + " " + dateArray[5] + ":" + dateArray[6] + ":" + dateArray[7] + " " + dateArray[8]).getTime();
			var currentDate = new Date().getTime();
			var millisecDiff = parseInt(currentDate) - parseInt(newDate);
			var hrsDiff = millisecDiff / (1000 * 60 * 60);
			
			if (hrsDiff <= 6) {
				return '<span style="color:#006600;">' + val + '</span>';
			} else if (hrsDiff > 6 && hrsDiff <= 12) {
				return '<span style="color:#FFD500;">' + val + '</span>';
			} else if (hrsDiff > 12 && hrsDiff <= 24) {
				return '<span style="color:#FF8000;">' + val + '</span>';
			} else if (hrsDiff > 24) {
				return '<span style="color:#FF0000;">' + val + '</span>';
			}
		}
		else {
			return '<span>' + val + '</span>';
		}
	} 
$(function() {
  $('input[name="datetimes"]').daterangepicker({
    timePicker: true,
    startDate: moment().startOf('hour'),
    endDate: moment().startOf('hour').add(32, 'hour'),
    locale: {
      format: 'M/DD hh:mm A'
    }
  });
});
      </script>
	<input type="text" name="datetimes" style="width:400px;"/>
      <br>
<!-- start page content -->
<!--
      <div class="container">
         <div class="span-24 last">
	        <table style="border:1px solid lightgrey;">
	           <tr>
		          <td align="center">
			         <div style=" margin-bottom:-20; font-size:14pt; font-weight:bold; text-align:center; color:#747474;">System Inventory <span id="sysCount" /></div>	
			
			         <table id="inventoryTable" class="hover">
				        <thead>
						   <tr>
					          <th nowrap style="align:left; cursor:pointer;">Computer</th>
					          <th nowrap style="align:left; cursor:pointer;">Users</th>
					          <th nowrap style="align:left; cursor:pointer;">Operating System</th>
					          <th nowrap style="align:left; cursor:pointer;">IP Addresses</th>					
					          <th nowrap style="align:left; cursor:pointer;">Last Report Time</th>
					       </tr>
					   </thead>
				       <tbody id="resultsHere">				
				       </tbody>
			         </table>
		          </td>
	           </tr>
            </table>
         </div>
			
	-->	





<!--<?php require 'includes/footer.php'; ?>-->
</div>

