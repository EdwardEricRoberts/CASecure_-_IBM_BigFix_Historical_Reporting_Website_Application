<?php
require_once './includes/authenticate.php';
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
      <title>CASecure > Patching > Devices</title>

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
               <span style="z-index:0; position:absolute;"><a href="PatchingOverview.php">Patching</a> &nbsp;&raquo;&nbsp; Devices</span>
            </span>
         </div>
      </div>
<!-- end breadcrumbs -->

      <br>
      <div class="span-24 last">
	     <div class="pagetitle2" style="margin-bottom:20px; margin-left:10px; float:left">Devices<br>
		 </div>	  
	  </div>

<script>document.getElementById("patchingNav").className = "current-menu-item";</script>

<?php

	$devices = [
		[   "device_name" => "WIN8-C",
			"last_updated" => "20 minutes ago",
			"updates_available" => "0",
			"patches" => "1" ],
		[   "device_name" => "WIN8-C",
			"last_updated" => "6 minutes ago",
			"updates_available" => "92",
			"patches" => "14" ],
		[   "device_name" => "Win8-D",
			"last_updated" => "20 minutes ago",
			"updates_available" => "0",
			"patches" => "1" ],
		[   "device_name" => "WIN8-D",
			"last_updated" => "7 minutes ago",
			"updates_available" => "69",
			"patches" => "8" ],
		[   "device_name" => "WIN8-E",
			"last_updated" => "16 minutes ago",
			"updates_available" => "68",
			"patches" => "16" ],
	];
	
	$arrayLength = sizeof($devices);
	
	//echo "$arrayLength";
	//echo $devices[0]["device_name"]
	//var_dump($devices);
	//foreach ($devices as $index => $device) {
		//echo "$devices[$index]["device_name"]";
	//}
?>


<div class="container">
<div class="span-24 last">

			<p> <?= "$arrayLength"; ?> Devices </p>
			<table id="deviceTable" style="border-style:solid; border-color:black; width:900px;">
				<th>Device</th>
				<th>Last Updated</th>
				<th>Patches Available</th>
				<th>Patches Updated</th>
				<?php for( $i=0; $i<$arrayLength; $i++): ?>
					<tr style="border-style:solid; border-color:black;">
						<td style="border-style:solid; border-color:black;">
							<?= $devices[$i]["device_name"]; ?>
						</td>
						<td style="border-style:solid; border-color:black;">
							<?= $devices[$i]["last_updated"]; ?>
						</td>
						<td style="border-style:solid; border-color:black;">
							<?= $devices[$i]["updates_available"]; ?>
						</td>
						<td style="border-style:solid; border-color:black;">
							<?= $devices[$i]["patches"]; ?>
						</td>
					</tr>
				<?php endfor; ?> 
			</table>
			<!--<img src="SampleImages/Devices/devices.jpg">--> 

</div>
<br><br><br><br><br><br><br><br><br><br>

<?php require 'includes/footer.php'; ?>
</div>

