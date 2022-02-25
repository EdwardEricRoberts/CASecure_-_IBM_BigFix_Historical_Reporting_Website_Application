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
	
	$expected = ['user', 'pass', 'serv'];
	$createBigFixProxyURL = 'http://localhost/CASecure_MSP_ver_1.0/proxies/InventoryReport.php?cg=All%20Machines&';

	$i = 0;
	foreach ($_POST as $key => $value) {
		if (in_array($key, $expected)) {
			if ($i != 0) {
				$createBigFixProxyURL.='&';
			}
			$createBigFixProxyURL .= $key . '=' . urlencode($value);
			//$$key = trim($value);
			$i++;
		}
	}
	//echo '<script language="javascript">';
	//echo 'alert("'.$createBigFixProxyURL.'")';
	//echo '</script>';
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $createBigFixProxyURL);
	curl_setopt($ch, CURLOPT_FAILONERROR, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	$bigFixProxyXMLString = curl_exec($ch);
	curl_close($ch);
	//echo '<script language="javascript">';-
	//echo 'alert("'.$bigFixProxyXMLString.'")';
	//echo '</script>';
	$bigFixProxyXML = new SimpleXMLElement($bigFixProxyXMLString);
	
	if(!isset($bigFixProxyXML->Query->Error)) {  // || (!isset($bigFixProxyXML->Query->HTTP_Error_Number) && ($bigFixProxyXML->Query->HTTP_Error_Number != "401"))
		$expected2 = ['user', 'pass'];
		$confirmBigFixUserURL = 'http://localhost/CASecure_MSP_ver_1.0/database/update/ConfirmBigFixLogin.php?cid='.$currentUserID.'&';
		$i = 0;
		foreach ($_POST as $key => $value) {
			if (in_array($key, $expected2)) {
				if ($i != 0) {
					$confirmBigFixUserURL.='&';
				}
				$confirmBigFixUserURL .= $key . '=' . $value;
				//$$key = trim($value);
				$i++;
			}
		}
		//echo '<script language="javascript">';
		//echo 'alert("'.$confirmBigFixUserURL.'")';
		//echo '</script>';
		$ch2 = curl_init();
		curl_setopt($ch2, CURLOPT_URL, $confirmBigFixUserURL);
		curl_setopt($ch2, CURLOPT_FAILONERROR, true);
		curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch2, CURLOPT_TIMEOUT, 15);
		$confirmBigFixLoginXMLString = curl_exec($ch2);
		curl_close($ch2);
		
		$confirmBigFixLoginXML = new SimpleXMLElement($confirmBigFixLoginXMLString);
	}
	//else {
	//	echo '<script language="javascript">';
	//	echo 'alert("Wrong Password")';
	//	echo '</script>';
	//}
	
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
		<title>Validate User</title>


	<?php require 'includes/headerRevised_2.php'; ?>
	<?php require 'includes/navigationRevised.php'; ?>
		<link rel="stylesheet" href="includes/the-datepicker.css">
		<script src="includes/the-datepicker.js"></script>
		
		<style>
		
			tbody tr:nth-child(even) td, tbody tr.even td {background:#ffffff;}
	
		</style>
<!-- start breadcrumbs -->                
		<div class="span-24">
			<div style="background-color:#cacaca; width:100%; margin-top:2px; height:23px; line-height:23px;"> 
				<span style="text-align:center;">
					<img src="includes/breadcrumbs_start.jpg" style="height:23px;">
					<span style="z-index:0; position:absolute;"><a href="Administration.php" class="breadcrumblink">Administration</a> &nbsp;&raquo;&nbsp; Validate User</span>
				</span>
			</div>
		</div>
<!-- end breadcrumbs -->
		
		<br>
		<div class="span-24 last">
			<div class="pagetitle2" style="margin-bottom:20px; margin-left:10px; float:left">Validate User<br>
			</div>	  
		</div>
		
		<div class="container">
		
			<div class="span-23 last" style="background-color:#f5f5f5; padding:4px;">
				
			<!--<form name='bfUserForm'>-->
				<div class="span-7 prepend-1 last">
					
					<input id="searchBigFixUserName" type="text" placeholder="Search BigFix User ..." name="searchBigFixUserName" style="margin-top:-2; padding:5px;" required>
					<button type="button" id="bfUserSubmit" style="height:30px; padding-top:7px; ">Submit</button>
					<br><br>
				</div><br>
			<!--</form>	-->
				<!--<div class="span-22 last">-->
					<hr noshade width="100%">
				<!--</div>-->
		<form action="#" method="post">
			<!--<form>	-->
				<div id="passwordArea" class="span-7 prepend-3 last" style="display:none;">
					<p>
						<label for="serv">BigFix Server</label><br>
						<input id="serv" name="serv" type="text" readonly required>
					</p>
					<p>
						<label for="user">BigFix User</label><br>
						<input id="user" name="user" type="text" readonly required>
					</p>
					<p>
						<label for="pass">BigFix User Password</label><br>
						<input id="pass" name="pass" type="text" required>
					</p>
					
					<script src="http://code.jquery.com/jquery-1.11.3.min.js"></script> 
					<script src="js/app.js" type="text/javascript" charset="utf-8"></script>
					<script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
					<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
					<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>

					<script src="js/app.js"></script>
					
				</div>
					
			</div>
			<div class="span-24 last">
				
				<input id="submit" name="submit" type="submit" value="Validate User" style="display:none;">
				
			</div>
			
			<!--</form>-->
			<div class="span-24 last">
				<br>
				<div style="text-align:center;">
				<!-- --- placeholder for error messages here. --- -->
				
					<?php 
					if (isset($_POST["submit"])) {
						if(isset($bigFixProxyXML->Query->Error)) {
							echo "Error";
							echo '<br/>';
							echo "Provided BigFix Password is Invalid!";
							echo '<br/><br/>';
						}
						else {
							if(isset($confirmBigFixLoginXML->Query->Error)) {
								foreach ($confirmBigFixLoginXML->Query->Result->Actions->Action as $action) {
									echo $action->Status;
									echo '<br/>';
									echo $action->Details;
									echo '<br/><br/>';
								}
							}
							else {
								foreach ($confirmBigFixLoginXML->Query->Result->Actions->Action as $action) {
									echo $action->Status;
									echo '<br/>';
									echo $action->Details;
									echo '<br/><br/>';
								}
							}
						}
					}
					?>
				</div>
				<br>
				
			</div>

			<script>
				
				var currentUserID = "<?php echo $_SESSION['userid']; ?>";
				
				var bigFixUsersFetchURL = "database/fetch/FetchUnconfirmedBigFixUserList.php?cid=" + currentUserID;
				
				var bigFixUserNames = [];
				var bigFixServers = [];
				
				var bigFixUsersRequest = new XMLHttpRequest();
				bigFixUsersRequest.open("GET", bigFixUsersFetchURL, true);
				bigFixUsersRequest.send();
				bigFixUsersRequest.onreadystatechange = function() {
					if ((this.readyState === 4) && (this.status === 200)) {
						//alert(this.responseText.toString());
						var bigFixUsersXML = this.responseXML;
						
						if (bigFixUsersXML.getElementsByTagName("Error").length == 0) {
							var bigFixUsersCount = bigFixUsersXML.getElementsByTagName("bigfix_login").length;
							//var bigFixUsersRowHTML = "";
							for (var i = 0; i < bigFixUsersCount; i++) {
								bigFixUserNames[i] = bigFixUsersXML.getElementsByTagName("bigfix_user_name")[i].childNodes[0].nodeValue;
								bigFixServers[i] = bigFixUsersXML.getElementsByTagName("bigfix_server")[i].childNodes[0].nodeValue;
								//bigFixUsersRowHTML = '<option value=' + encodeURI(bigFixUsersXML.getElementsByTagName("bigfix_user_name")[i].childNodes[0].nodeValue) + '>';
								//bigFixUsersRowHTML += bigFixUsersXML.getElementsByTagName("bigfix_user_name")[i].childNodes[0].nodeValue;
								//bigFixUsersRowHTML += '</option>';
								//$("#selectBigFix").append(bigFixUsersRowHTML);
							}
							
							autocomplete(document.getElementById("searchBigFixUserName"), bigFixUserNames);
							
							$(document).ready(function(){
								$('#bfUserSubmit').click(function() {
									var bigFixUserName = $('#searchBigFixUserName').val();
									
									if($.inArray(bigFixUserName, bigFixUserNames) != -1) {
										if($('#searchBigFixUserName').val() != "") {
											
											$('#passwordArea').css('display', 'block');
											$('#submit').css('display', 'block');
											$('#user').val(bigFixUserName);
											$('#searchBigFixUserName').val("");
											
											var bigFixServer = bigFixServers[$.inArray(bigFixUserName, bigFixUserNames)];
											$('#serv').val(bigFixServer);
										}
										else {
											$('#passwordArea').css('display', 'none');
											$('#submit').css('display', 'none');
										}
									}
									else {
										$('#passwordArea').css('display', 'none');
										$('#submit').css('display', 'none');
									}
								});
							});
						}
						else {
							
						}
						
					}
				}
				
				function autocomplete(inp, arr) {
					/*the autocomplete function takes two arguments,
					the text field element and an array of possible autocompleted values:*/
					var currentFocus;
					/*execute a function when someone writes in the text field:*/
					inp.addEventListener("input", function(e) {
						var a, b, i, val = this.value;
						/*close any already open lists of autocompleted values*/
						closeAllLists();
						if (!val) { return false;}
						currentFocus = -1;
						/*create a DIV element that will contain the items (values):*/
						a = document.createElement("DIV");
						a.setAttribute("id", this.id + "autocomplete-list");
						a.setAttribute("class", "autocomplete-items");
						/*append the DIV element as a child of the autocomplete container:*/
						this.parentNode.appendChild(a);
						/*for each item in the array...*/
						for (i = 0; i < arr.length; i++) {
							/*check if the item starts with the same letters as the text field value:*/
							if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
								/*create a DIV element for each matching element:*/
								b = document.createElement("DIV");
								/*make the matching letters bold:*/
								b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
								b.innerHTML += arr[i].substr(val.length);
								/*insert a input field that will hold the current array item's value:*/
								b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
								/*execute a function when someone clicks on the item value (DIV element):*/
								b.addEventListener("click", function(e) {
									/*insert the value for the autocomplete text field:*/
									inp.value = this.getElementsByTagName("input")[0].value;
									/*close the list of autocompleted values,
									(or any other open lists of autocompleted values:*/
									closeAllLists();
								});
								a.appendChild(b);
							}
						}
					});
					/*execute a function presses a key on the keyboard:*/
					inp.addEventListener("keydown", function(e) {
						var x = document.getElementById(this.id + "autocomplete-list");
						if (x) x = x.getElementsByTagName("div");
						if (e.keyCode == 40) {
							/*If the arrow DOWN key is pressed,
							increase the currentFocus variable:*/
							currentFocus++;
							/*and and make the current item more visible:*/
							addActive(x);
						} else if (e.keyCode == 38) { //up
							/*If the arrow UP key is pressed,
							decrease the currentFocus variable:*/
							currentFocus--;
							/*and and make the current item more visible:*/
							addActive(x);
						} else if (e.keyCode == 13) {
							/*If the ENTER key is pressed, prevent the form from being submitted,*/
							e.preventDefault();
							if (currentFocus > -1) {
								/*and simulate a click on the "active" item:*/
								if (x) x[currentFocus].click();
							}
						}
					});
					function addActive(x) {
						/*a function to classify an item as "active":*/
						if (!x) return false;
						/*start by removing the "active" class on all items:*/
						removeActive(x);
						if (currentFocus >= x.length) currentFocus = 0;
						if (currentFocus < 0) currentFocus = (x.length - 1);
						/*add class "autocomplete-active":*/
						x[currentFocus].classList.add("autocomplete-active");
					}
					function removeActive(x) {
						/*a function to remove the "active" class from all autocomplete items:*/
						for (var i = 0; i < x.length; i++) {
							x[i].classList.remove("autocomplete-active");
						}
					}
					function closeAllLists(elmnt) {
						/*close all autocomplete lists in the document,
						except the one passed as an argument:*/
						var x = document.getElementsByClassName("autocomplete-items");
						for (var i = 0; i < x.length; i++) {
							if (elmnt != x[i] && elmnt != inp) {
								x[i].parentNode.removeChild(x[i]);
							}
						}
					}
					/*execute a function when someone clicks in the document:*/
					document.addEventListener("click", function (e) {
						closeAllLists(e.target);
					});
				}
				
				
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

				
				
			</script>
			
			
			<?php require 'includes/footer.php'; ?>
		</form>
		</div>
		</div>

