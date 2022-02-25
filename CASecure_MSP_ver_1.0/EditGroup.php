<?php
require_once './includes/authenticateAdmin.php';
//print_r($_SESSION); 
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
		<title>Edit User Group</title>

<!-- something about this controls the shuttling of options but also breaks our local styles -->



<!-- mystery include -->


	<?php require 'includes/headerRevised_2.php'; ?>
	<?php require 'includes/navigationRevised.php'; ?>


	 <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" />
	 <!--<link rel="stylesheet" href="css/style2.css" />-->
<!-- start breadcrumbs -->                
		<div class="span-24">
			<div style="background-color:#cacaca; width:100%; margin-top:2px; height:23px; line-height:23px;"> 
				<span style="text-align:center;">
					<img src="includes/breadcrumbs_start.jpg" style="height:23px;">
					<span style="z-index:0; position:absolute;"><a href="Administration.php" class="breadcrumblink">Administration</a> &nbsp;&raquo;&nbsp; Edit User Group</span>
				</span>
			</div>
		</div>
<!-- end breadcrumbs -->
		
		<br>
		<div class="span-24 last">
			<div class="pagetitle2" style="margin-bottom:20px; margin-left:10px; float:left">Edit User Group<br>
			</div>	  
		</div>
		
		<div class="container">
		<form action="#" method="post">
			<div class="span-24 last" >
				
			<br>
				<div class="span-5">
					<p>
						<label for="selectUserGroup">User Group to Edit</label><br>
						<select name="selectUserGroup" id="selectUserGroup" width="30"required>
							<option value="computerGroupInstructions">Default User Group</option>
							<option value="option">User Group 1</option>
							<option value="option">User Group 2</option>
							<option value="option">User Group 3</option>
							<option value="option">User Group 4</option>
							<option value="option">User Group 5</option>
							<option value="option">User Group 6</option> 
						</select>
					</p>
					
				</div>
				
			</div>
			
			
			<div class="span-24 last">
			            
        
            
            <div class="row">
			
                <div class="col-xs-4">
				<h4>Users to add</h4>
                    <select name="from[]" id="customSort" class="form-control" size="8" multiple="multiple">
                        <option value="1" data-position="1">User 1</option>
                        <option value="5" data-position="2">User 5</option>
                        <option value="2" data-position="3">User 2</option>
                        <option value="4" data-position="4">User 4</option>
                        <option value="3" data-position="5">User 3</option>
                    </select>
                </div>
                
                <div class="col-xs-2">
				<h4>&nbsp;</h4>
                    <button type="button" id="customSort_rightAll" class="btn btn-block"><i class="glyphicon glyphicon-forward"></i></button>
                    <button type="button" id="customSort_rightSelected" class="btn btn-block"><i class="glyphicon glyphicon-chevron-right"></i></button>
                    <button type="button" id="customSort_leftSelected" class="btn btn-block"><i class="glyphicon glyphicon-chevron-left"></i></button>
                    <button type="button" id="customSort_leftAll" class="btn btn-block"><i class="glyphicon glyphicon-backward"></i></button>
                </div>
                
                <div class="col-xs-4">
				<h4>Selected group</h4>
                    <select name="to[]" id="customSort_to" class="form-control" size="8" multiple="multiple"></select>
                </div>
            </div>
			<br><br>
            
            
    
	</div>
			
			
			
			
			
			
			<div class="span-24 last">

				<input type="reset" value="Cancel">
				<input id="submit" type="submit" value="Save Changes">
			</div>
			<div class="span-24 last">
<hr noshade>
				<input type="submit" value="Delete User Group">
				
			</div>
			<div class="span-24 last">
				<br>
				<div style="text-align:center;">--- placeholder for error messages here. ---</div>
				<br>


			</div>



<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript" src="includes/multiselect.min.js"></script>
<script type="text/javascript" src="includes/multiselect.js"></script>

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

			<script>
			
			
			
			
				var curUser = "eer";
				var password = "AllieCat4";
				var server = "bigfix.internal.cassevern.com";
				// HTTP encode periods so as to not ruin the URL for the AJAX call
				server = server.replace(/\./g, "%2E");
				
			/* Fetches Request Type options from the Database */
				var reqType = new XMLHttpRequest();
				reqType.open("GET", "database/RequestTypeFetch.php", true);
				reqType.send();
				reqType.onreadystatechange = function() {
					if ((this.readyState === 4) && (this.status === 200)) {
						xmlReqTypList = this.responseText.toString();
						var parser = new DOMParser();
						var xmlDoc = parser.parseFromString(xmlReqTypList,"text/xml");
						
						var tupleCount = xmlDoc.getElementsByTagName("Tuple").length;
						var columnCount = xmlDoc.getElementsByTagName("Answer").length / tupleCount;
						
						var typeOptionHTML = "";
						
						for (var i = 0; i < tupleCount; i++) {
							for (var j = 0; j < columnCount; j++) {
								if (j == 0) {
									typeOptionHTML = '<option value="' + xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue + '">'
								}
								else {
									typeOptionHTML += xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue + '</option>';
								}
							}
							$("#typeSelect").append(typeOptionHTML);
						}
					}
				}
				
			/* Fetches Criticality options from the Database */
				var reqCrit = new XMLHttpRequest();
				reqCrit.open("GET", "database/RequestCriticalityFetch.php", true);
				reqCrit.send();
				reqCrit.onreadystatechange = function() {
					if ((this.readyState === 4) && (this.status === 200)) {
						xmlReqCritList = this.responseText.toString();
						var parser = new DOMParser();
						var xmlDoc = parser.parseFromString(xmlReqCritList,"text/xml");
						
						var tupleCount = xmlDoc.getElementsByTagName("Tuple").length;
						var columnCount = xmlDoc.getElementsByTagName("Answer").length / tupleCount;
						
						var critOptionHTML = "";
						
						for (var i = 0; i < tupleCount; i++) {
							for (var j = 0; j < columnCount; j++) {
								if (j == 0) {
									critOptionHTML = '<option value="' + xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue + '">'
								}
								else {
									critOptionHTML += xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue + '</option>';
								}
							}
							$("#criticalitySelect").append(critOptionHTML);
						}
					}
				}
				
				document.getElementById("infoNav").className = "current-menu-item";
				
			/* Code to prevent leaving text area when tab key is pressed, instead returning a tab character */
				var textareas = document.getElementsByTagName('textarea');
				var count = textareas.length;
				for(var i=0;i<count;i++){
					textareas[i].onkeydown = function(e){
						if(e.keyCode==9 || e.which==9){
							e.preventDefault();
							var s = this.selectionStart;
							this.value = this.value.substring(0,this.selectionStart) + "\t" + this.value.substring(this.selectionEnd);
							this.selectionEnd = s+1; 
						}
					}
				}
				
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
					input = input.replace(/ \*/g, "%2A");
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
				
			/*  */
			//
				$("#submitRequest").on("click", function() {
					var subject = document.getElementById("subject").value;
					var typeSelection = document.getElementById("typeSelect").value;
					var critSelection = document.getElementById("criticalitySelect").value;
					var description = document.getElementById("description").value;
					
					subject = httpEncode(subject);
					description = httpEncode(description);
					
					var postURL = "database/RequestsFormLog.php?user=" + curUser + "&subj=" + subject + "&type=" + typeSelection + "&crit=" + critSelection + "&desc=" + description;
					
					//alert(postURL)
					
					var queryPost = new XMLHttpRequest();
					queryPost.open("POST", postURL, true);
					queryPost.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
					queryPost.onreadystatechange = function() {
						alert(queryPost.readyState);
						if (queryPost.readyState == 4 && queryPost.status == 200) {
							alert(queryPost.responseText);
						}
						else {
							alert("Request Failed");
						}
					}
					queryPost.send();
				});
				
				
				
				
				
				
				
				// check to make sure password is verified


				
				
				
				
			</script>
			<?php require 'includes/footer.php'; ?>
		</form>
		</div>
		</div>

