<?php
require_once './includes/authenticate.php';
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
      <title>CASecure > More > Compliance > PCI > DSS Windows 7</title>

	  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.js"></script>
      <script src="bootstrap.js"></script>
      <link rel="stylesheet" href="bootstrap.css">
      <link rel="stylesheet" type="text/css" href="TABLE_FORMAT_1.css">
	  <?php require 'includes/headerRevised_2.php'; ?>
      <?php require 'includes/navigationRevised.php'; ?>
<!-- start breadcrumbs -->                
	  <div class="span-24">
         <div style="background-color:#cacaca; width:100%; margin-top:2px; height:23px; line-height:23px;"> 
            <span style="text-align:center;">
               <img src="includes/breadcrumbs_start.jpg" style="height:23px;">
               <span style="z-index:0; position:absolute;">More &nbsp;&raquo;&nbsp; <a href="ComplianceOverview.php">Compliance Overview</a> &nbsp;&raquo;&nbsp; <a href="PCI.php">PCI</a> &nbsp;&raquo;&nbsp;  DSS Windows 7</span>
            </span>
         </div>
      </div>
<!-- end breadcrumbs -->

      <br>
      <div class="span-24 last">
	     <div class="pagetitle2" style="margin-bottom:20px; margin-left:10px; float:left">DSS Windows 7<br>
		 </div>	  
	  </div>

<script>document.getElementById("complianceNav").className = "current-menu-item";</script>


<br>
<div class="container">

<div class="span-24 last">
	<img src="SampleImages/PCI-DSSWin7/Win7_1-3.jpg">
		</div>
<hr/>
<br><br><br>

<div class="span-8">
	<img src="SampleImages/PCI-DSSWin7/Win7_4.jpg">
	</div>
	<div class="span-3">
		<a href="Win7_checks.php">25 Checks</a>
</div>
<div class="span-8">
		<img src="SampleImages/PCI-DSSWin7/Win7_5.jpg">
		</div>
	<div class="span-3">
	<a href="Win7_computers.php">2 Computers</a></div>


<?php require 'includes/footer.php'; ?>
</div>