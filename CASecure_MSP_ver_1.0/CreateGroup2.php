<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
		<title>Create User Group</title>




	<?php require 'includes/headerRevised_2.php'; ?>
	<?php require 'includes/navigationRevised.php'; ?>


	 <link rel="stylesheet" href="css/bootstrap.min.css" />
	 <link rel="stylesheet" href="css/style2.css" />
<!-- start breadcrumbs -->                
		<div class="span-24">
			<div style="background-color:#cacaca; width:100%; margin-top:2px; height:23px; line-height:23px;"> 
				<span style="text-align:center;">
					<img src="includes/breadcrumbs_start.jpg" style="height:23px;">
					<span style="z-index:0; position:absolute;"><a href="Administration.php" class="breadcrumblink">Administration</a> &nbsp;&raquo;&nbsp; Create User Group</span>
				</span>
			</div>
		</div>
<!-- end breadcrumbs -->
		
		<br>
		<div class="span-24 last">
			<div class="pagetitle2" style="margin-bottom:20px; margin-left:10px; float:left">Create User Group<br>
			</div>	  
		</div>
		
		<div class="container">
		<form action="#" method="post">
			<div class="span-24 last" >
				
			<br>
				<div class="span-5 prepend-1">
					<p>
						<label for="firstname">User Group Name</label><br>
						<input id="firstname" name="firstname" type="text" required>
					</p>
					
				</div>
			</div>
			
			
			<div class="span-24 last">
			            
        
            
                <div id="wrap" class="container">            
        <h4 id="demo-keep-rendering-sort">Keep rendering sort</h4>
            
            <div class="row">
                <div class="col-xs-5">
                    <select name="from[]" id="customSort" class="form-control" size="8" multiple="multiple">
                        <option value="1" data-position="1">User 1</option>
                        <option value="5" data-position="2">User 5</option>
                        <option value="2" data-position="3">User 2</option>
                        <option value="4" data-position="4">User 4</option>
                        <option value="3" data-position="5">User 3</option>
                    </select>
                </div>
                
                <div class="col-xs-2">
                    <button type="button" id="customSort_rightAll" class="btn btn-block"><i class="glyphicon glyphicon-forward"></i></button>
                    <button type="button" id="customSort_rightSelected" class="btn btn-block"><i class="glyphicon glyphicon-chevron-right"></i></button>
                    <button type="button" id="customSort_leftSelected" class="btn btn-block"><i class="glyphicon glyphicon-chevron-left"></i></button>
                    <button type="button" id="customSort_leftAll" class="btn btn-block"><i class="glyphicon glyphicon-backward"></i></button>
                </div>
                
                <div class="col-xs-5">
                    <select name="to[]" id="customSort_to" class="form-control" size="8" multiple="multiple"></select>
                </div>
            </div>
            
            
    </div>

<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>


<script type="text/javascript" src="dist/js/multiselect.min.js"></script>



<script type="text/javascript">
$(document).ready(function() {
    

    $('#customSort').multiselect({
        sort: {
            left: function(a, b) {
                return a.value > b.value ? 1 : -1;
            },
            right: function(a, b) {
                return a.value < b.value ? 1 : -1;
            }
        }
    });
});
</script>
		</div>

