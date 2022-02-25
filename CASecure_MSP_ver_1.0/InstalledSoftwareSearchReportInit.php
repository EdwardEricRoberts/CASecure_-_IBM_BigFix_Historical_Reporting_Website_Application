<?php
require_once './includes/authenticate.php';
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
      <title>CASecure > Reporting > Installed Software Search Report</title>

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
	
	var curUser = "<?php echo $_SESSION['bigfixuser']; ?>"; //"APIAdmin";
	var password = "<?php echo $_SESSION['bigfixpassword']; ?>"; //"AllieCat7";
	var server = "<?php echo $_SESSION['bigfixserver']; ?>"; //"bigfix.internal.cassevern.com";
	// HTTP encode periods so as to not ruin the URL for the AJAX call
	server = server.replace(/\./g, "%2E");
	
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
<div class="span-24 last">
<div>
	<form action="InstalledSoftwareSearchReport.php" method="get" target="_self">
		<table>
			<tr>
				<td>
					<textarea name="cg" value="All Machines" style="display:none;">All Machines</textarea>
					<input type="text" name="app" id="searchInput" value="" placeholder="Search for Computers by Application Name or Version Number..." size="120">&nbsp;&nbsp;
				</td>
				<td>
					<button type="submit">Search</button>
				</td>
			</tr>
		</table>
	</form>
</div>

<br>

<?php require 'includes/footer.php'; ?>
               </div>


