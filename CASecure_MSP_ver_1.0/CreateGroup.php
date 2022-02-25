<?php
require_once './includes/authenticateAdmin.php';
//print_r($_SESSION); 
$currentUserID = $_SESSION['userid'];

if (isset($_POST["submit"])) {
	require_once './includes/db_connect.php';
	/*
	$output = "";
	foreach($_POST as $formKey => $formData) {
		if (is_array($formData)) {
			$outputText = "[";
			$i = 0;
			foreach($formData as $arrayData) {
				if ($i != 0) {
					$outputText .= ",";
				}
				$outputText .= $arrayData;
				$i++;
			}
			$outputText .= "]";
			$output .= "[".$formKey."] => ".$outputText.", ";
		}
		else {
			$output .= "[".$formKey."] => ".$formData.", ";
		}
	}
	echo '<script language="javascript">';
	echo 'alert("'.$output.'")';
	echo '</script>';
	*/
	$expected = ['group', 'ids'];
	$createUserGroupURL = 'http://localhost/CASecure_MSP_ver_1.0/database/log/CreateNewUserGroup.php?cid='.$currentUserID;
	foreach ($_POST as $key => $value) {
		if (in_array($key, $expected)) {
			$createUserGroupURL .= '&';
			if(is_array($value)) {
				$finalValue = '[';
				$i = 0;
				foreach ($value as $arrayValue) {
					if ($i != 0) {
						$finalValue .= ',';
					}
					$finalValue .= '"'.$arrayValue.'"';
					$i++;
				}
				$finalValue .= ']';
				$createUserGroupURL .= $key . '=' . $finalValue;
			}
			else {
				$createUserGroupURL .= $key . '=' . $value;
			}
		}
	}
	//echo '<script language="javascript">';
	//echo 'alert("'.$createUserGroupURL.'")';
	//echo '</script>';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $createUserGroupURL);
	curl_setopt($ch, CURLOPT_FAILONERROR, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	$newUserGroupXMLString = curl_exec($ch);
	curl_close($ch);
	//echo '<script language="javascript">';
	//echo 'alert("'.$newUserGroupXMLString.'")';
	//echo '</script>';
	$newUserGroupXML = new SimpleXMLElement($newUserGroupXMLString);
	
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
		<title>Create User Group</title>

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
					<span style="z-index:0; position:absolute;"><a href="Administration.php" class="breadcrumblink">Administration</a> &nbsp;&raquo;&nbsp; Create User Group</span>
				</span>
			</div>
		</div>
<!-- end breadcrumbs -->
		
		<br>
		<div class="span-24 last">
			<div class="pagetitle2" style="margin-bottom:20px; margin-left:10px; float:left">
				Create User Group
				<br>
			</div>	  
		</div>
		
		<div class="container">
		<form action="#" method="post">
			<div class="span-24 last" >
				
				<br>
				<div class="span-5">
					<p>
						<label for="ugroupname">User Group Name</label><br>
						<input id="ugroupname" name="group" type="text" required>
					</p>
					
				</div>
			</div>
			
			<div class="span-24 last">
				
				<div class="row">
				
					<div class="col-xs-4">
					<h4>Users to add</h4>
						<select name="from[]" id="customSort" class="form-control" size="8" multiple="multiple">
						<!--    <option value="1" data-position="1">User 1</option>
							<option value="5" data-position="2">User 5</option>
							<option value="2" data-position="3">User 2</option>
							<option value="4" data-position="4">User 4</option>
							<option value="3" data-position="5">User 3</option>  -->
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
					<h4>New user group</h4>
						<select name="ids[]" id="customSort_to" class="form-control" size="8" multiple="multiple" required></select>
					</div>
				</div>
				<br><br>
            
			</div>
			
			
			<div class="span-24 last">
				
				<input type="reset" value="Reset">
				<input id="submit" name="submit" type="submit" value="Create User Group">
			</div>
			<div class="span-24 last">
				<br>
				<div style="text-align:center;">
					<!--  --- placeholder for error messages here. ---  -->
					<?php
					if (isset($_POST["submit"])) {
						if (isset($newUserGroupXML->Connection->Error) || isset($newUserGroupXML->Query->Error)) {
							echo $newUserGroupXML->xpath("//Action[last()]/Status")[0];
							echo '<br/>';
							echo $newUserGroupXML->xpath("//Action[last()]/Details")[0];
						}
						else {
							foreach ($newUserGroupXML->Query->Result->Actions->Action as $action) {
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
			
			<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
			<script type="text/javascript" src="includes/multiselect.min.js"></script>
			<script type="text/javascript" src="includes/multiselect.js"></script>

			<script type="text/javascript">
				
				var currentUserID = "<?php echo $_SESSION['userid']; ?>";
				
				var usersFetchURL = "database/fetch/FetchUsersList.php?cid=" + currentUserID;
				
				var usersListRequest = new XMLHttpRequest();
				usersListRequest.open("GET", usersFetchURL, true);
				usersListRequest.send();
				usersListRequest.onreadystatechange = function() {
					if ((this.readyState === 4) && (this.status === 200)) {
						var usersListXML = this.responseXML;
						
						if (usersListXML.getElementsByTagName("Error").length == 0) {
							var usersListCount = usersListXML.getElementsByTagName("user").length;
							var usersListRowHTML = "";
							for (var i =0; i < usersListCount; i++) {
								usersListRowHTML = '<option value="' + encodeURI(usersListXML.getElementsByTagName("user_id")[i].childNodes[0].nodeValue) + '" data-position="' + encodeURI(usersListXML.getElementsByTagName("user_id")[i].childNodes[0].nodeValue) + '">';
								usersListRowHTML += usersListXML.getElementsByTagName("user_name")[i].childNodes[0].nodeValue;
								usersListRowHTML += '</option>';
								$("#customSort").append(usersListRowHTML);
							}
						}
					}
				}
				
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
				
			</script>
			<?php require 'includes/footer.php'; ?>
		</form>
		</div>
</div>

