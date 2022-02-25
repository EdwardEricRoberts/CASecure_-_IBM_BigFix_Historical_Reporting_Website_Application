<?php
require_once './includes/authenticate.php';
//print_r($_SESSION); 
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
      <title>CASecure > Reporting</title>

      <?php require 'includes/headerRevised_2.php'; ?>
      <?php require 'includes/navigationRevised.php'; ?>



<!-- start breadcrumbs -->
                
	     <div class="span-24">
            <div style="background-color:#cacaca; width:100%; margin-top:2px; height:23px; line-height:23px;">
                  
               <span style="text-align:center;">
                  <img src="includes/breadcrumbs_start.jpg" style="height:23px;">
                  <span style="z-index:0; position:absolute;">Reporting</span>
               </span>
            </div>
         </div>
<!-- end breadcrumbs -->
<div class="span-24 last">
<div class="pagetitle2" style="margin-bottom:20px;">&nbsp;&nbsp;Reporting<br></div>
</div>

<div class="container">
   <div class="span-24 last">
      <div class="span-7 prepend-1">
	     <div style="border:1px solid lightgrey; padding:20px;">
		    <span class="reportheader">System Reports</span><br><br>
		    &raquo;&nbsp;&nbsp;<a href="MicrosoftPatchComplianceReport.php?cg=<?php echo $_SESSION['defaultcomputergroup']; ?>" class="reportlink">Microsoft Patch Compliance</a><br><br>
		    &raquo;&nbsp;&nbsp;<a href="SevereVulnerabilityReport.php?cg=<?php echo $_SESSION['defaultcomputergroup']; ?>" class="reportlink">Severe Vulnerability</a><br><br>
		    &nbsp;&nbsp;<br><br>
		    &nbsp;&nbsp;<br><br>
			&nbsp;&nbsp;<a></a><br><br>
		 </div>
	  </div>
      <div class="span-7">
	     <div style="border:1px solid lightgrey; padding:20px;">
		    <span class="reportheader">Content Reports</span><br><br>
		    &raquo;&nbsp;&nbsp;<a href="SourceSeverityReport.php?cg=All Machines" class="reportlink">Source Severity of Deployed Content</a><br><br>
		    &raquo;&nbsp;&nbsp;<a href="OutstandingComplianceByContentReport.php?cg=All Machines" class="reportlink">Outstanding Compliance by Content</a><br><br>
		    &raquo;&nbsp;&nbsp;<a href="DeployedContentReport.php?cg=All Machines" class="reportlink">Deployed Content</a><br><br>
		    &raquo;&nbsp;&nbsp;<a href="UnspecifiedContentReport.php?cg=All Machines" class="reportlink">Unspecified Content</a><br><br>
			&raquo;&nbsp;&nbsp;<a href="ReleasedPatchesReport.php?cg=All Machines" class="reportlink">Released Patches</a><br><br>
		 </div>
	  </div>
      <div class="span-7">
	     <div style="border:1px solid lightgrey; padding:20px;">
		    <span class="reportheader">Inventory Reports</span><br><br>
		    &raquo;&nbsp;&nbsp;<a href="ApplicationSoftwareCountReport.php?cg=All Machines" class="reportlink">Application Software Count</a><br><br>
		    &raquo;&nbsp;&nbsp;<a href="InstalledSoftwareSearchReportInit.php" class="reportlink">Installed Software Search</a><br><br>
		    &raquo;&nbsp;&nbsp;<a href="MicrosoftOfficeVersionReport.php?cg=All Machines" class="reportlink">Microsoft Office Version</a><br><br>
			&raquo;&nbsp;&nbsp;<a href="InventoryReportByFirstReportTime.php?cg=All%20Machines" class="reportlink">Inventory by First Report Time</a><br><br>
			&raquo;&nbsp;&nbsp;<a href="InventoryReportByLastReportTime.php?cg=All%20Machines" class="reportlink">Inventory by Last Report Time</a><br><br>
		     
		 </div>
	  </div>
   </div>
   <div class="span-24 last">
      &nbsp;
   </div>
   <div class="span-24 last">
      <div class="span-7 prepend-1">
	     <div style="border:1px solid lightgrey; padding:20px;">
		    <span class="reportheader">Additional Reports</span><br><br>
		    &raquo;&nbsp;&nbsp;<a href="OperatingSystemsReport.php?cg=All Machines" class="reportlink">Operating Systems</a><br><br>
		    &raquo;&nbsp;&nbsp;<a href="WindowsUpdateStatusReport.php?cg=All Machines" class="reportlink">Windows Update Status</a><br><br>
		    &raquo;&nbsp;&nbsp;<a href="SystemFreeSpaceReport.php?cg=All Machines" class="reportlink">System Free Space</a><br>
			&nbsp;&nbsp;<a></a>
	     </div>   
      </div>
      <div class="span-7">
	     <div style="border:1px solid lightgrey; padding:20px;">
		    <span class="reportheader">Executive Summary</span><br><br>
		     &raquo;&nbsp;&nbsp;<a id="patchComplianceExecutiveSummary" href="MicrosoftPatchComplianceExecutiveSummaryReport.php?cg=[]" class="reportlink">Microsoft Patch Compliance</a><br><br>
		     <br><br><br><br>
		 </div>
	  </div>
      <div class="span-7">
	     <div style="border:1px solid lightgrey; padding:20px;">
		    <span class="reportheader">Historical Reports</span><br><br>
		     &raquo;&nbsp;&nbsp;<a href="MicrosoftPatchComplianceHistoricSummaryReport.php?cg=<?php echo $_SESSION['defaultcomputergroup']; ?>" class="reportlink">Microsoft Patch Compliance</a><br><br>
			 &raquo;&nbsp;&nbsp;<a href="HistoricPatchComplianceSummaryReport.php?site=<?php echo $_SESSION['defaultsite']; ?>&cg=<?php echo $_SESSION['defaultcomputergroup']; ?>&pt=FALSE" class="reportlink">Patch Compliance</a><br><br>
	         <br><br>
		 </div>   
      </div>
	</div>

  

<?php require 'includes/footer.php'; ?>
</div>


<script>document.getElementById("reportingNav").className = "current-menu-item";</script>

<script>
	var allComputerGroups = [];
	
	var curUser = "APIAdmin";
	var password = "AllieCat7";
	var server = "bigfix.internal.cassevern.com";
		
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
						allComputerGroups[i] = xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue;
				}
			}
			
			$("#patchComplianceExecutiveSummary").prop("href", "MicrosoftPatchComplianceExecutiveSummaryReport.php?cg=[" + allComputerGroups + "]")
		}
	} //52,53,54,55,56,57,58,59,60,61,464,465,466,467,468,469,470,471,472,473,474
	
	
</script>