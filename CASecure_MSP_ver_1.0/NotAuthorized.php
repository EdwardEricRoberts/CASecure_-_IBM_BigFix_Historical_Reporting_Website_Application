<?php
require_once './includes/authenticate.php';
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
      <title>CASecure > Dashboard</title>

      <?php require 'includes/headerRevised_2.php'; ?>
      <?php require 'includes/navigationRevised.php'; ?>
      <?php require 'includes/alertRevised.php'; ?>                        
                 
                    
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
      <br>
      <div class="span-24 last">
	     <div class="span-24">
	        <div class="pagetitle2">&nbsp;&nbsp;Administration</div>
	     </div>
      </div> 
<!-- start page content -->
	<div class="container">
		<div>
			<h1>You are not authorized to access this page.</h1>
		</div>
		
		<br><br><br><br><br><br>


<?php require 'includes/footer.php'; ?>
</div>

