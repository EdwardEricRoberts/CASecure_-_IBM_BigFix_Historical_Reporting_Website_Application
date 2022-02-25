<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
		<title>Create User</title>




	<?php require 'includes/headerRevised_2.php'; ?>
	<?php require 'includes/navigationRevised.php'; ?>


	<style>
	tbody tr:nth-child(even) td, tbody tr.even td {background:#ffffff;}
	</style>
<!-- start breadcrumbs -->                
		<div class="span-24">
			<div style="background-color:#cacaca; width:100%; margin-top:2px; height:23px; line-height:23px;"> 
				<span style="text-align:center;">
					<img src="includes/breadcrumbs_start.jpg" style="height:23px;">
					<span style="z-index:0; position:absolute;"><a href="Administration.php" class="breadcrumblink">Administration</a> &nbsp;&raquo;&nbsp; Create User</span>
				</span>
			</div>
		</div>
<!-- end breadcrumbs -->
		
		<br>
		<div class="span-24 last">
			<div class="pagetitle2" style="margin-bottom:20px; margin-left:10px; float:left">Create User<br>
			</div>	  
		</div>
		
		<div class="container">
			<div class="span-24 last" style="background-color:#f5f5f5; padding:4px;">
				
			<form action="#" method="post"><br>
  <div class="span-5 prepend-1">
  <p>
    <label for="firstname">First Name</label><br>
    <input id="firstname" name="firstname" type="text" required>
  </p>
  <p>
    <label for="username">Username</label><br>
    <input id="username" name="username" type="text" required>
  </p>
    <p>
    <label for="email">Email</label><br>
    <input id="email" name="email" type="text" required>
  </p>
  </div>
  
  <div class="span-5 prepend-1">
  <p>
    <label for="password">Password</label><br>
    <input id="password" name="password" type="password" required>
    <span style="color:red;">Enter a password longer than 8 characters</span> </p>
  <p>
    <label for="confirm_password">Confirm Password</label><br>
    <input id="confirm_password" name="confirm_password" type="password">
    <span style="color:red;">Please confirm your password</span> </p>

  </div>
  <div class="span-7 prepend-1">
  <p>
  <label for="selectBigFix">Select BigFix User to Map to</label><br>
	<select name="selectBigFix" id="selectBigFix" width="30" required>
		<option value="userBigfixInstructions">BigFix User</option>
		<option value="option">BigFix User 1</option>
		<option value="option">BigFix User 2</option>
		<option value="option">BigFix User 3</option>
		<option value="option">BigFix User 4</option>
		<option value="option">BigFix User 5</option>
		<option value="option">BigFix User 6</option> 
	</select>
  </p>
  <p>
	<label for="selectBigFix">Select the default Site for this user</label><br>
	<select name="selectSite" id="selectSite" width="30"required>
		<option value="userSiteInstructions">Default Site</option>
		<option value="option">Site 1</option>
		<option value="option">Site 2</option>
		<option value="option">Site 3</option>
		<option value="option">Site 4</option>
		<option value="option">Site 5</option>
		<option value="option">Site 6</option> 
	</select>
  </p>
  <p>
	<label for="selectBigFix">Select the default Computer Group</label><br>
	<select name="selectComputerGroup" id="selectComputerGroup" width="30"required>
		<option value="computerGroupInstructions">Default Computer Group</option>
		<option value="option">Computer Group 1</option>
		<option value="option">Computer Group 2</option>
		<option value="option">Computer Group 3</option>
		<option value="option">Computer Group 4</option>
		<option value="option">Computer Group 5</option>
		<option value="option">Computer Group 6</option> 
	</select>
  </p>

  </div>
  <div class="span-4 prepend-1">
    <p>
	<label for="isAdmin">Will this user have admin privileges?</label><br>
	<input type="radio" name="isAdmin" value="yes">Yes<br>
	<input type="radio" name="isAdmin" value="yes">No<br>
  </p>
  </div>
  
</form>
<script src="http://code.jquery.com/jquery-1.11.3.min.js"></script> 
<script src="js/app.js" type="text/javascript" charset="utf-8"></script>
			<script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
			<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
			<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>

<script src="js/app.js"></script>

</div>
<div class="span-24 last">

	<input type="reset" value="Cancel">
							<input id="submit" type="submit" value="Create User">
</div>


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
		
		</div>
		</div>

