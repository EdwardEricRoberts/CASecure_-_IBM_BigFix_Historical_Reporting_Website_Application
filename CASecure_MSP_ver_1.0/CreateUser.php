<?php
require_once './includes/authenticateAdmin.php';
//print_r($_SESSION); 
$currentUserID = $_SESSION['userid'];

if (isset($_POST["submit"])) {
	require_once './includes/db_connect.php';
	/*
	$output = "";
	foreach($_POST as $formKey => $formData) {
		$output .= "[".$formKey."] => ".$formData.", ";
	}
	echo '<script language="javascript">';
	echo 'alert("'.$output.'")';
	echo '</script>';
	*/

	$expected = ['wel', 'name', 'email', 'pass', 'bigfix', 'ds', 'dcg', 'admin', 'phone', 'ext', 'car'];
	$createUserURL = 'http://localhost/CASecure_MSP_ver_1.0/database/log/CreateNewUser3.php?cid='.$currentUserID;
	foreach ($_POST as $key => $value) {
		if (in_array($key, $expected)) {
			$createUserURL.='&';
			$createUserURL .= $key . '=' . $value;
			//$$key = trim($value);
		}
	}
	//echo '<script language="javascript">';
	//echo 'alert("'.$createUserURL.'")';
	//echo '</script>';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $createUserURL);
	curl_setopt($ch, CURLOPT_FAILONERROR, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	$newUserXMLString = curl_exec($ch);
	curl_close($ch);
	$newUserXML = new SimpleXMLElement($newUserXMLString);
	
	
}
?>

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
			<div class="pagetitle2" style="margin-bottom:20px; margin-left:10px; float:left">
				Create User
				<br>
			</div>	  
		</div>
		
		<div class="container">
		<form action="#" method="post">
			<div class="span-24 last" style="background-color:#f5f5f5; padding:4px;">
				
			<br>
				<div class="span-5 prepend-1">
					<p>
						<label for="firstname">First Name</label><br>
						<input id="firstname" name="wel" type="text" required>
					</p>
					<p>
						<label for="username">Username</label><br>
						<input id="username" name="name" type="text" required>
					</p>
					<p>
						<label for="email">Email</label><br>
						<input id="email" name="email" type="text" required>
					</p>
					<p>
						<label for="phone_number">Phone Number</label><br>
						<input id="phone_number" name="phone" type="text" required>
						
						
					</p>
				</div>
  
				<div class="span-5 prepend-1">
					<p>
						<label for="password">Password</label><br>
						<input id="password" name="pass" type="password" required>
						<span style="color:red;">Enter a password longer than 8 characters</span> 
					</p>
					<p>
						<label for="confirm_password">Confirm Password</label><br>
						<input id="confirm_password" name="confirm_password" type="password">
						<span style="color:red;">Please confirm your password</span> 
					</p>
					<p>&nbsp;<br><br></p>
					<p>
					    <label for="extention" style="margin-top:17px;">Extension (optional)</label><br>
						<input id="extention" name="ext" type="text">
					</p>
				</div>
				<div class="span-7 prepend-1">
					<p>
						<label for="selectBigFix">Select BigFix User to Map to</label><br>
						<select name="bigfix" id="selectBigFix" width="30" required onChange="makeSelection()">
							<option value="userBigfixInstructions">BigFix User</option>
							<!--<option value="option">BigFix User 1</option>
							<option value="option">BigFix User 2</option>
							<option value="option">BigFix User 3</option>
							<option value="option">BigFix User 4</option>
							<option value="option">BigFix User 5</option>
							<option value="option">BigFix User 6</option> -->
						</select>
					</p>
					<p>
						<label for="selectSite" id="labelSite" style="visibility:hidden; margin-top:5px;">Select the default Site for this user</label><br>
						<select name="ds" id="selectSite" width="30" required style="visibility:hidden;">
							<option value="userSiteInstructions">Default Site</option>
							<!--<option value="option">Site 1</option>
							<option value="option">Site 2</option>
							<option value="option">Site 3</option>
							<option value="option">Site 4</option>
							<option value="option">Site 5</option>
							<option value="option">Site 6</option> -->
						</select>
					</p>
					<p>
						<label for="selectComputerGroup" id="labelComputerGroup" style="visibility:hidden; margin-top:7px;">Select the default Computer Group</label><br>
						<select name="dcg" id="selectComputerGroup" width="30" required style="visibility:hidden;">
							<option value="computerGroupInstructions">Default Computer Group</option>
							<!--<option value="option">Computer Group 1</option>
							<option value="option">Computer Group 2</option>
							<option value="option">Computer Group 3</option>
							<option value="option">Computer Group 4</option>
							<option value="option">Computer Group 5</option>
							<option value="option">Computer Group 6</option> -->
						</select>
					</p>
					<p>
					<label for="phone_carrier" style="margin-top:3px;">Select Phone Carrier</label><br>
						<select name="car" id="phone_carrier" width="30" required>
							<option value="phoneCarrierInstructions">Please select</option>
							
						</select>
					</p>

				</div>
				<div class="span-4 prepend-1">
					<p>
						<label for="admin">Will this user have admin privileges?</label><br>
						<input type="radio" name="admin" value="true">Yes<br>
						<input type="radio" name="admin" value="false" checked>No<br>
					</p>
				</div>
  

				<script src="http://code.jquery.com/jquery-1.11.3.min.js"></script> 
				<script src="js/app.js" type="text/javascript" charset="utf-8"></script>
				<script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
				<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
				<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>

				<script src="js/app.js"></script>

			</div>
			<div class="span-24 last">

				<input type="reset" value="Reset">
				<input id="submit" name="submit" type="submit" value="Create User">
			</div>
			<div class="span-24 last">
				<br>
				<div id="output" style="text-align:center;">
			<!--	--- placeholder for error messages here. ---  -->
				<?php
				if (isset($_POST["submit"])) {
					if (isset($newUserXML->Connection->Error) || isset($newUserXML->Query->Error)) {
						echo $newUserXML->xpath('//Action[last()]/Status')[0];//$newUserXML->Query->Result->Actions->Action[0]->Status;
						echo '<br/>';
						echo $newUserXML->xpath('//Action[last()]/Details')[0];//$newUserXML->Query->Result->Actions->Action[0]->Details;
					}
					else {
						foreach ($newUserXML->Query->Result->Actions->Action as $action) {
							echo $action->Status;
							echo '<br/>';
							echo $action->Details;
							echo '<br/><br/>';
						}
					}
				}
				?>
				</div>
				<br>


			</div>

			<script>
				
				var currentUserID = "<?php echo $_SESSION['userid']; ?>";
				
				var bigFixUsersFetchURL = "database/fetch/FetchBigFixUsersList.php?cid=" + currentUserID;
				
				var bigFixUsersRequest = new XMLHttpRequest();
				bigFixUsersRequest.open("GET", bigFixUsersFetchURL, true);
				bigFixUsersRequest.send();
				bigFixUsersRequest.onreadystatechange = function() {
					if ((this.readyState === 4) && (this.status === 200)) {
						var bigFixUsersXML = this.responseXML;
						
						if (bigFixUsersXML.getElementsByTagName("Error").length == 0) {
							var bigFixUsersCount = bigFixUsersXML.getElementsByTagName("bigfix_user_name").length;
							var bigFixUsersRowHTML = "";
							for (var i = 0; i < bigFixUsersCount; i++) {
								bigFixUsersRowHTML = '<option value=' + encodeURI(bigFixUsersXML.getElementsByTagName("bigfix_user_name")[i].childNodes[0].nodeValue) + '>';
								bigFixUsersRowHTML += bigFixUsersXML.getElementsByTagName("bigfix_user_name")[i].childNodes[0].nodeValue;
								bigFixUsersRowHTML += '</option>';
								$("#selectBigFix").append(bigFixUsersRowHTML);
							}
						}
						else {
							
						}
					}
				}
				
				var phoneCarriersFetchURL = "database/fetch/FetchPhoneCarriersList.php?cid=" + currentUserID;
				
				var phoneCarriersRequest = new XMLHttpRequest();
				phoneCarriersRequest.open("GET", phoneCarriersFetchURL, true);
				phoneCarriersRequest.send();
				phoneCarriersRequest.onreadystatechange = function() {
					if ((this.readyState === 4) && (this.status === 200)) {
						var phoneCarriersXML = this.responseXML;
						
						if (phoneCarriersXML.getElementsByTagName("Error").length == 0) {
							var phoneCarriersCount = phoneCarriersXML.getElementsByTagName("phone_carrier").length;
							var phoneCarriersRowHTML = "";
							for (var i = 0; i < phoneCarriersCount; i++) {
								phoneCarriersRowHTML = '<option value=' + encodeURI(phoneCarriersXML.getElementsByTagName("carrier_id")[i].childNodes[0].nodeValue) + '>';
								phoneCarriersRowHTML += phoneCarriersXML.getElementsByTagName("sms_domain_name")[i].childNodes[0].nodeValue;
								phoneCarriersRowHTML += '</option>';
								$("#phone_carrier").append(phoneCarriersRowHTML);
							}
						}
						else {
							
						}
					}
				}
				
				function makeSelection() {
					$('#selectSite').empty().append('<option value="userSiteInstructions">Default Site</option>');
					$('#selectComputerGroup').empty().append('<option value="computerGroupInstructions">Default Computer Group</option>');
					
					if (document.getElementById("selectBigFix").selectedIndex != 0) {
						var selectedBigFixUser = $("#selectBigFix").val();
						//alert(selectedBigFixUser);
						var sitesFetchURL = "database/fetch/FetchSitesByBigFixUser.php?cid=" + currentUserID + "&bf=" + selectedBigFixUser;
						
						var sitesRequest = new XMLHttpRequest();
						sitesRequest.open("GET", sitesFetchURL, true);
						sitesRequest.send();
						sitesRequest.onreadystatechange = function() {
							if ((this.readyState === 4) && (this.status === 200)) {
								var sitesXML = this.responseXML;
								
								if (sitesXML.getElementsByTagName("Error").length == 0) {
									var sitesCount = sitesXML.getElementsByTagName("site").length;
									var sitesRowHTML = "";
									for (var i = 0; i < sitesCount; i++) {
										sitesRowHTML = '<option value=' + encodeURI(sitesXML.getElementsByTagName("site_name")[i].childNodes[0].nodeValue) + '>';
										sitesRowHTML += sitesXML.getElementsByTagName("site_display_name")[i].childNodes[0].nodeValue;
										sitesRowHTML += '</option>';
										$("#selectSite").append(sitesRowHTML);
									}
									document.getElementById("selectSite").style.visibility = "visible";
								}
								else {
									
								}
							}
						}
						
						
						var computerGroupFetchURL = "database/fetch/FetchComputerGroupsByBigFixUser.php?cid=" + currentUserID + "&bf=" + selectedBigFixUser;
						
						var computerGroupRequest = new XMLHttpRequest();
						computerGroupRequest.open("GET", computerGroupFetchURL, true);
						computerGroupRequest.send();
						computerGroupRequest.onreadystatechange = function() {
							if ((this.readyState === 4) && (this.status === 200)) {
								var computerGroupsXML = this.responseXML;
								
								if (computerGroupsXML.getElementsByTagName("Error").length == 0) {
									var computerGroupCount = computerGroupsXML.getElementsByTagName("computer_group").length;
									computerGroupsRowHTML = '';
									for (var i = 0; i < computerGroupCount; i++) {
										computerGroupsRowHTML = '<option value=' + encodeURI(computerGroupsXML.getElementsByTagName("computer_group_name")[i].childNodes[0].nodeValue) + '>';
										computerGroupsRowHTML += computerGroupsXML.getElementsByTagName("computer_group_name")[i].childNodes[0].nodeValue;
										computerGroupsRowHTML += '</option>';
										$("#selectComputerGroup").append(computerGroupsRowHTML);
									}
									document.getElementById("selectComputerGroup").style.visibility = "visible";
								}
								else {
									
								}
							}
						}
						document.getElementById("labelSite").style.visibility = "visible";
						document.getElementById("labelComputerGroup").style.visibility = "visible";
					} else {
						document.getElementById("selectSite").style.visibility = "hidden";
						document.getElementById("selectComputerGroup").style.visibility = "hidden";
						document.getElementById("labelSite").style.visibility = "hidden";
						document.getElementById("labelComputerGroup").style.visibility = "hidden";
					}
				}
				
				
			/* Code to prevent leaving text area when tab key is pressed, instead returning a tab character */
			/*	var textareas = document.getElementsByTagName('textarea');
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
				*/
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
			/*
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
				*/
				
				
				
				
				
				
				// check to make sure password is verified


				
				
				
				
			</script>
			<?php require 'includes/footer.php'; ?>
		</form>
		</div>
		</div>

