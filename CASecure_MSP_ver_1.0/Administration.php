<?php
require_once './includes/authenticateAdmin.php';
//print_r($_SESSION); 
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
      <title>CASecure > Administration</title>

      <?php require 'includes/headerRevised_2.php'; ?>
      <?php require 'includes/navigationRevised.php'; ?>



<!-- start breadcrumbs -->
                
	     <div class="span-24">
            <div style="background-color:#cacaca; width:100%; margin-top:2px; height:23px; line-height:23px;">
                  
               <span style="text-align:center;">
                  <img src="includes/breadcrumbs_start.jpg" style="height:23px;">
                  <span style="z-index:0; position:absolute;">Administration</span>
               </span>
            </div>
         </div>
<!-- end breadcrumbs -->
<div class="span-24 last">
<div class="pagetitle2" style="margin-bottom:20px;">&nbsp;&nbsp;Administration<br></div>
</div>

<div class="container">
   <div class="span-24 last">
      <div class="span-11 prepend-1">
	     <div style="border:1px solid lightgrey; padding:20px;">
		    <span class="reportheader">Users</span><br><br>
		    &raquo;&nbsp;&nbsp;<a href="CreateUser.php" class="reportlink">Create New User</a><br><br>
			&raquo;&nbsp;&nbsp;<a href="EditUser.php" class="reportlink">Edit Existing User</a><br><br>
		    &raquo;&nbsp;&nbsp;<a href="DeleteUser.php" class="reportlink">Delete User</a><br>

		 </div>
	  </div>
      <div class="span-11">
	     <div style="border:1px solid lightgrey; padding:20px;">
		    <span class="reportheader">BigFix Users</span><br><br>
		    &raquo;&nbsp;&nbsp;<a href="ValidateUser.php" class="reportlink">Validate User</a><br><br><br>
		    &nbsp;&nbsp;&nbsp;<br><br>

		 </div>
	  </div>
     
   </div>
   <div class="span-24 last">
      &nbsp;
   </div>
   <div class="span-24 last">
    <div class="span-11 prepend-1">
	     <div style="border:1px solid lightgrey; padding:20px;">
		    <span class="reportheader">User Groups</span><br><br>
		    &raquo;&nbsp;&nbsp;<a href="CreateGroup.php" class="reportlink">Create New User Group</a><br><br>
		    &raquo;&nbsp;&nbsp;<a href="EditGroup.php" class="reportlink">Edit Existing User Group</a><br><br>

		 </div>
	  </div>
      <div class="span-11">
	     <div style="border:1px solid lightgrey; padding:20px;">
		    <span class="reportheader">Alerts</span><br><br>
		    &raquo;&nbsp;&nbsp;<a href="CreateAlert.php" class="reportlink">Create New Alert</a><br><br>
		    &raquo;&nbsp;&nbsp;<a href="EditAlert.php" class="reportlink">Edit Existing Alert</a><br><br>

		     
		 </div>   
      </div>
	  
	  
      

  </div>
 <div class="span-24 last">
      &nbsp;
   </div>
  <div class="span-22 prepend-1 last">

	     <div style="border:1px solid lightgrey; padding:20px;">
		    <span class="reportheader">Database Refresh</span><br><br>
		    <span>Use this option if data related to sites, user groups or other entities are not populating as expected in corresponding reports.</span><br>
			<button type="button" onclick="alert('This will be replaced with a command to refresh the database')" style="margin-bottom:-10px;">Refresh Database</button>
			<br>
			&nbsp;&nbsp;<a></a>
	     </div>   
      </div>
<?php require 'includes/footer.php'; ?>
</div>




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