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
	$expected = ['ugids', 'title', 'mess', 'exp', 'col'];
	$createAlertURL = 'http://localhost/CASecure_MSP_ver_1.0/database/log/CreateAlert.php?cid='.$currentUserID;
	foreach ($_POST as $key => $value) {
		if (in_array($key, $expected)) {
			$createAlertURL .= '&';
			if (is_array($value)) {
				$finalValue = '['.join(',', $value).']';
				/*
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
				*/
				$createAlertURL .= $key . '=' . urlencode($finalValue);
			}
			else if ($key == 'exp') {
				if($value != "") {
					$expirationArray = explode(". ", $value);
					$expirationString = $expirationArray[2]."-".$expirationArray[1]."-".$expirationArray[0];
					$createAlertURL .= $key . '=' . urlencode($expirationString);
				}
				else {
					$createAlertURL .= $key . '=';
				}
			}
			else {
				$createAlertURL .= $key . '=' . urlencode($value);
			}
		}
	}
	//echo '<script language="javascript">';
	//echo 'alert("'.$createAlertURL.'")';
	//echo '</script>';
	//
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $createAlertURL);
	curl_setopt($ch, CURLOPT_FAILONERROR, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	$newAlertXMLString = curl_exec($ch);
	curl_close($ch);
	//echo '<script language="javascript">';
	//echo 'alert("'.$newAlertXMLString.'")';
	//echo '</script>';
	$newAlertXML = new SimpleXMLElement($newAlertXMLString);
	//
	//echo '<script language="javascript">';
	//echo 'alert("'.$newAlertXML->Query->Result->Actions->Action->Status.'")';
	//echo '</script>';
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
		<title>Create Alert</title>
	
	<style>
		.dropdown-check-list {
			display: inline-block;
			width: 100%;
		}
		
		.dropdown-check-list:focus {
			outline: 1;
			//background-color: grey;
		}
		
		.dropdown-check-list .anchor {
			width: 73%;
			position: relative;
			cursor: pointer;
			display: inline-block;
			padding-top: 5px;
			padding-left: 10px;
			padding-bottom: 5px;
			padding-right: 50px;
			border: 1px #ccc solid;
			margin-right: 50px;
		}
		
		.dropdown-check-list .anchor::after {
			position: absolute;
			content: "";
			border-left: 1pt solid black;
			border-top: 1pt solid black;
			padding: 5px;
			right: 10px;
			top: 20%;
			-moz-transform: rotate(-135deg);
			-ms-transform: rotate(-135deg);
			-o-transform: rotate(-135deg);
			-webkit-transform: rotate(-135deg);
			transform: rotate(-135deg);
		}
		
		.dropdown-check-list .anchor:active::after {
			right: 8px;
			top: 21%;
		}
		
		.dropdown-check-list ul.items {
			width: 100%;
			padding: 2px;
			display: none;
			margin: 0;
			border: 1px solid #ccc;
			border-top: none;
			position:relative;
			z-index:100;
			margin-right: 50px;
		}
		
		.dropdown-check-list ul.items li {
			list-style: none;
			position:relative;
			z-index:100;
		}
		
		.dropdown-check-list.visible .anchor {
			color: #0094ff;
			//background-color: grey;
		}
		
		.dropdown-check-list.visible .anchor::after {
			position: absolute;
			content: "";
			border-left: 1pt solid black;
			border-top: 1pt solid black;
			padding: 5px;
			right: 10px;
			top: 40%;
			-moz-transform: rotate(45deg);
			-ms-transform: rotate(45deg);
			-o-transform: rotate(45deg);
			-webkit-transform: rotate(45deg);
			transform: rotate(45deg);
		}
		
		.dropdown-check-list.visible .anchor:active::after {
			right: 8px;
			//top: 21%;
		}
		
		.dropdown-check-list.visible .items {
			display: block;
			background-color: white;
			position:relative;
			z-index:100;
		}
	</style>
	
	<?php require 'includes/headerRevised_2.php'; ?>
	<?php require 'includes/navigationRevised.php'; ?>
	<link rel="stylesheet" href="includes/the-datepicker.css">
	<script src="includes/the-datepicker.js"></script>
	
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" />
	
	<style>
		tbody tr:nth-child(even) td, tbody tr.even td {background:#ffffff;}
	</style>
	
<!-- start breadcrumbs -->               
		<div class="span-24">
			<div style="background-color:#cacaca; width:100%; margin-top:2px; height:23px; line-height:23px;"> 
				<span style="text-align:center;">
					<img src="includes/breadcrumbs_start.jpg" style="height:23px;">
					<span style="z-index:0; position:absolute;"><a href="Administration.php" class="breadcrumblink">Administration</a> &nbsp;&raquo;&nbsp; Create Alert</span>
				</span>
			</div>
		</div>
<!-- end breadcrumbs -->
		
		<br>
		<div class="span-24 last">
			<div class="pagetitle2" style="margin-bottom:20px; margin-left:10px; float:left">
				Create Alert
				<br>
			</div>	  
		</div>
		
		<div class="container">
		<form action="#" method="post">	
		
			<div class="span-23 last" style="background-color:#f5f5f5; padding:4px;">	
			<!-- <form> -->
				<br>
		
				<div class="span-24 last">
					<div class="row">
				
						<div class="col-xs-4">
						<h4>User Groups to add</h4>
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
						<h4>User Groups for New Alert</h4>
							<select name="ugids[]" id="customSort_to" class="form-control" size="8" multiple="multiple"></select>
						</div>
					</div>
					<br><br>
				</div>
		<!--	<div class="span-7 prepend-1 last"> 
					<p>
						<div class="dropdown-check-list" style="float: left; positon: relative; width: 912px;">
							<span class="anchor">Select User Groups</span>
				<form action="#" method="post">		
							<ul id="customSort" class="items" style="columns: 4;">
								<li id="firstListItem"><button type="button" id="reChecked" onclick="reChecked()">Select All</button></li>
						
							</ul>
						</div>
						
					</p>
				</div>
		-->
			<!--	
				<div class="span-22 last">
					<hr noshade width="100&">
				</div>
			-->	
				<div class="span-7 prepend-3 last">
					<p>
						<label for="alerttitle">Alert Title</label><br>
						<input id="alerttitle" name="title" type="text" required>
					</p>
					<p>
						<label for="alertmessage">Alert Message (optional)</label><br>
						<!--<textarea id="alertmessage" name="mess"></textarea>
						<script src="http://js.nicedit.com/nicEdit-latest.js" type="text/javascript"></script>
						<script type="text/javascript">bkLib.onDomLoaded(nicEditors.allTextAreas);</script>-->
						
						<textarea name="mess" id="alertmessage" class="alert_textarea" style="resize:none; height:75px; width:300px;" maxlength="130">  </textarea>
						
					</p>
					<p>
						<label for="alertexpiry">Alert Expiration (optional)</label><br>
						<input type="text" id="alertexpiry" name="exp" size="20">
					</p>
					<p>
						<label for="selectPriority">Alert Priority</label><br>
						<select id="selectPriority" name="col" width="30" onchange="setColor()" required>
							<option value="alertPriorityInstructions" style="background-color: #FFFFFF;">Select Priority</option>
						</select>
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
				
				<input id="submit" name="submit" type="submit" value="Create Alert">
			</div>
			
			<!-- </form> -->
			<div class="span-24 last">
				<br>
				<div style="text-align:center;">
					<!--- placeholder for error messages here. --- -->
					<?php
					if (isset($_POST["submit"])) {
						if (isset($newAlertXML->Connection->Error) || isset($newAlertXML->Query->Error)) {
							echo $newAlertXML->xpath("//Action[last()]/Status")[0];
							echo '<br/>';
							echo $newAlertXML->xpath("//Action[last()]/Details")[0];
						}
						else {
							foreach($newAlertXML->Query->Result->Actions->Action as $action) {
								//echo $action->Status;
								//echo '<br/>';
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
			<!--
						<label for="selectBigFix">Select User Group for New Alert</label><br>
						<select name="bigfix" id="selectBigFix" width="30" required onChange="makeSelection()">
							<option value="userBigfixInstructions">Select User Group</option>
							<option value="option">User Group 1</option>
							<option value="option">User Group 2</option>
							<option value="option">User Group 3</option>
							<option value="option">User Group 4</option>
							<option value="option">User Group 5</option>
							<option value="option">User Group 6</option> 
						</select>
						-->
			<script>
				(function(){
					var currentUserID = "<?php echo $_SESSION['userid']; ?>";
					
					var userGroupsListFetchURL = "http://localhost/CASecure_MSP_ver_1.0/database/fetch/FetchUserGroupsList.php?cid=" + currentUserID;
					
					var userGroupsListRequest = new XMLHttpRequest();
					userGroupsListRequest.open("GET", userGroupsListFetchURL, true);
					userGroupsListRequest.send();
					userGroupsListRequest.onreadystatechange = function() {
						if ((this.readyState === 4) && (this.status === 200)) {
							var userGroupsListXML = this.responseXML;
							//alert(this.responseText.toString());
							if (userGroupsListXML.getElementsByTagName("Error").length == 0) {
								var userGroupsListCount = userGroupsListXML.getElementsByTagName("user_group").length;
								var userGroupsListRowHTML = "", userGroupID = "", userGroupName = "";
								//alert(userGroupsListCount);
								for (var i = 0; i < userGroupsListCount; i++) {
									userGroupID = encodeURI(userGroupsListXML.getElementsByTagName("user_group_id")[i].childNodes[0].nodeValue);
									userGroupName = userGroupsListXML.getElementsByTagName("user_group_name")[i].childNodes[0].nodeValue;
									//userGroupsListRowHTML = "<li><input type='checkbox' class='groupSelection' name='ugids[]' id='" + userGroupID + "' value = '" + userGroupID + "' /><label>&nbsp;" + userGroupName + "</label></li>";
									userGroupsListRowHTML = '<option value="' + userGroupID + '" data-position="' + userGroupID + '">' + userGroupName + '</option>';
									//alert(userGroupsListRowHTML);
									$('#customSort').append(userGroupsListRowHTML);
								}
							}
							else {
								
							}
						}
					}
					
					var alertPrioritiesListFetchURL = "http://localhost/CASecure_MSP_ver_1.0/database/fetch/FetchAlertPrioritiesList.php?cid=" + currentUserID;
					
					var alertPrioritiesListRequest = new XMLHttpRequest();
					alertPrioritiesListRequest.open("GET", alertPrioritiesListFetchURL, true);
					alertPrioritiesListRequest.send();
					alertPrioritiesListRequest.onreadystatechange = function() {
						if ((this.readyState === 4) && (this.status === 200)) {
							var alertPrioritiesListXML = this.responseXML;
							
							if (alertPrioritiesListXML.getElementsByTagName("Error").length == 0) {
								var alertPrioritiesListCount = alertPrioritiesListXML.getElementsByTagName("alert_priority").length;
								var alertPrioritesListRowHTML = "", priorityID = "", priorityName = "", htmlColorHexCode = "";
								for (var i = 0; i < alertPrioritiesListCount; i++) {
									priorityID = alertPrioritiesListXML.getElementsByTagName("priority_id")[i].childNodes[0].nodeValue;
									priorityName = alertPrioritiesListXML.getElementsByTagName("priority_name")[i].childNodes[0].nodeValue;
									htmlColorHexCode = alertPrioritiesListXML.getElementsByTagName("html_color_hex_code")[i].childNodes[0].nodeValue;
									alertPrioritesListRowHTML = '<option value="' + priorityID + '" style="background-color: ' + htmlColorHexCode + ';">' + priorityName + '</option>';
									$('#selectPriority').append(alertPrioritesListRowHTML);
								}
							}
							else {
								
							}
						}
					}
					
					/*
					var currentUserID = "<?php echo $_SESSION['userid']; ?>";
					//alert(currentUserID);
					var userGroupsListFetchURL = "http://localhost/CASecure_MSP_ver_1.0/database/fetch/FetchUserGroupsList.php?cid=" + currentUserID;
					alert(userGroupsListFetchURL);
					var userGroupsListRequest = new XMLHttpRequest();
					userGroupsListRequest.open("GET", userGroupsListFetchURL, true);
					userGroupsListRequest.send();
					userGroupsListRequest.onreadystatechange = function() {
						if ((this.readyState === 4) && (this.status === 200)) {
							alert(this.responseText.toString());
							var userGroupsListXML = this.responseXML;
							
							if (userGroupsListXML.getElementsByTagName("Error").length == 0) {
								var userGroupsListCount = userGroupsListXML.getElementsByTagName("user_group").length;
								var userGroupsListRowHTML = "";
								for (var i = 0; i < userGroupsListCount; i++) {
									userGroupsListRowHTML = '<option value="' + encodeURI(userGroupsListXML.getElementsByTagName("user_group_id")[i].childNodes[0].nodeValue) + '">';
									userGroupsListRowHTML += userGroupsListXML.getElementsByTagName("user_group_name")[i].childNodes[0].nodeValue;
									userGroupsListRowHTML += '</option>';
									//alert(userGroupsListRowHTML);
									$("#selectBigFix").append(userGroupsListRowHTML);
								}
							}
							else {
								
							}
						}
					}
					*/
				/*	
					 jQuery(function ($) {
						var checkList = $('.dropdown-check-list');
						checkList.on('click', 'span.anchor', function(event){
							var element = $(this).parent();
							
							if ( element.hasClass('visible') )
							{
								element.removeClass('visible');
							}
							else
							{
								element.addClass('visible');
							}
						});
					});
					
					function removeChecked() {
						$("#removeChecked").remove();
						$(".groupSelection").attr("checked", false);
						$("#firstListItem").append('<button id="reChecked" onclick="reChecked()">Select All</button>');
					};
					
					function reChecked() {
						$("#reChecked").remove();
						$(".groupSelection").attr("checked", true);
						$("#firstListItem").append('<button id="removeChecked" onclick="removeChecked()">Remove All</button>');
					};
					*/

				})();
				
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
				
				setColor = function(val) {
					var e = document.getElementById("selectPriority");
					e.style.backgroundColor = rgb2hex(e.options[e.selectedIndex].style.backgroundColor);
					//alert(rgb2hex(e.options[e.selectedIndex].style.backgroundColor));
				}
				
				var hexDigits = new Array("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f"); 
				
				//Function to convert rgb color to hex format
				function rgb2hex(rgb) {
					rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
					return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
				}

				function hex(x) {
					return isNaN(x) ? "00" : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16];
				}
			//
				(function () {
					var input = document.getElementById('alertexpiry');

					var datepicker = new TheDatepicker.Datepicker(input);
					datepicker.render();

					var updateSetting = function (settingInput, callback) {
						settingInput.className = '';
						try {
							callback();
							datepicker.render();
						} catch (error) {
							console.error(error);
							settingInput.className = 'invalid';
						}
					};

					var createCallback = function (argumentsString, body) {
						var wrap = function () {
							return '{ return function( ' + argumentsString + ' ){ ' + body + ' } };'
						};
						return (new Function(wrap()))();
					};

					var updateListener = function(eventName, body, argumentsString) {
						eventName = eventName.charAt(0).toUpperCase() + eventName.slice(1);

						datepicker.options['off' + eventName]();

						if (body === '') {
							return;
						}

						datepicker.options['on' + eventName](createCallback(argumentsString, body));
					};

					var initialDateInput = document.getElementById('initialDate');
					initialDateInput.onchange = function () {
						updateSetting(initialDateInput, function () {
							datepicker.options.setInitialDate(initialDateInput.value !== '' ? initialDateInput.value : null);
						});
					};

					var initialMonthInput = document.getElementById('initialMonth');
					initialMonthInput.onchange = function () {
						updateSetting(initialMonthInput, function () {
							datepicker.options.setInitialMonth(initialMonthInput.value !== '' ? initialMonthInput.value : null);
						});
					};

					var openInput = document.getElementById('open');
					openInput.onclick = function () {
						datepicker.open();
					};

					var closeInput = document.getElementById('close');
					closeInput.onclick = function () {
						datepicker.close();
					};

					var destroyInput = document.getElementById('destroy');
					destroyInput.onclick = function () {
						datepicker.destroy();
					};

					var hideOnBlurInput = document.getElementById('hideOnBlur');
					hideOnBlurInput.onchange = function () {
						updateSetting(hideOnBlurInput, function () {
							datepicker.options.setHideOnBlur(hideOnBlurInput.checked);
						});
					};

					var hideOnSelectInput = document.getElementById('hideOnSelect');
					hideOnSelectInput.onchange = function () {
						updateSetting(hideOnSelectInput, function () {
							datepicker.options.setHideOnSelect(hideOnSelectInput.checked);
						});
					};

					var inputFormatInput = document.getElementById('inputFormat');
					inputFormatInput.onchange = function () {
						updateSetting(inputFormatInput, function () {
							datepicker.options.setInputFormat(inputFormatInput.value);
						});
					};

					var firstDayOfWeekInput = document.getElementById('firstDayOfWeek');
					firstDayOfWeekInput.onchange = function () {
						updateSetting(firstDayOfWeekInput, function () {
							datepicker.options.setFirstDayOfWeek(firstDayOfWeekInput.value);
						});
					};

					var minDateInput = document.getElementById('minDate');
					minDateInput.onchange = function () {
						updateSetting(minDateInput, function () {
							datepicker.options.setMinDate(minDateInput.value !== '' ? minDateInput.value : null);
						});
					};

					var maxDateInput = document.getElementById('maxDate');
					maxDateInput.onchange = function () {
						updateSetting(maxDateInput, function () {
							datepicker.options.setMaxDate(maxDateInput.value !== '' ? maxDateInput.value : null);
						});
					};

					var dropdownItemsLimitInput = document.getElementById('dropdownItemsLimit');
					dropdownItemsLimitInput.onchange = function () {
						updateSetting(dropdownItemsLimitInput, function () {
							datepicker.options.setDropdownItemsLimit(dropdownItemsLimitInput.value !== '' ? dropdownItemsLimitInput.value : null);
						});
					};

					var daysOutOfMonthVisibleInput = document.getElementById('daysOutOfMonthVisible');
					daysOutOfMonthVisibleInput.onchange = function () {
						updateSetting(daysOutOfMonthVisibleInput, function () {
							datepicker.options.setDaysOutOfMonthVisible(daysOutOfMonthVisibleInput.checked);
						});
					};

					var fixedRowsCountInput = document.getElementById('fixedRowsCount');
					fixedRowsCountInput.onchange = function () {
						updateSetting(fixedRowsCountInput, function () {
							datepicker.options.setFixedRowsCount(fixedRowsCountInput.checked);
						});
					};

					var toggleSelectionInput = document.getElementById('toggleSelection');
					toggleSelectionInput.onchange = function () {
						updateSetting(toggleSelectionInput, function () {
							datepicker.options.setToggleSelection(toggleSelectionInput.checked);
						});
					};

					var showDeselectButtonInput = document.getElementById('showDeselectButton');
					showDeselectButtonInput.onchange = function () {
						updateSetting(showDeselectButtonInput, function () {
							datepicker.options.setShowDeselectButton(showDeselectButtonInput.checked);
						});
					};

					var allowEmptyInput = document.getElementById('allowEmpty');
					allowEmptyInput.onchange = function () {
						updateSetting(allowEmptyInput, function () {
							datepicker.options.setAllowEmpty(allowEmptyInput.checked);
						});
					};

					var showCloseButtonInput = document.getElementById('showCloseButton');
					showCloseButtonInput.onchange = function () {
						updateSetting(showCloseButtonInput, function () {
							datepicker.options.setShowCloseButton(showCloseButtonInput.checked);
						});
					};

					var titleInput = document.getElementById('title');
					titleInput.onchange = function () {
						updateSetting(titleInput, function () {
							datepicker.options.setTitle(titleInput.value);
						});
					};

					var showResetButtonInput = document.getElementById('showResetButton');
					showResetButtonInput.onchange = function () {
						updateSetting(showResetButtonInput, function () {
							datepicker.options.setShowResetButton(showResetButtonInput.checked);
						});
					};

					var monthAsDropdownInput = document.getElementById('monthAsDropdown');
					monthAsDropdownInput.onchange = function () {
						updateSetting(monthAsDropdownInput, function () {
							datepicker.options.setMonthAsDropdown(monthAsDropdownInput.checked);
						});
					};

					var yearAsDropdownInput = document.getElementById('yearAsDropdown');
					yearAsDropdownInput.onchange = function () {
						updateSetting(yearAsDropdownInput, function () {
							datepicker.options.setYearAsDropdown(yearAsDropdownInput.checked);
						});
					};

					var monthAndYearSeparatedInput = document.getElementById('monthAndYearSeparated');
					monthAndYearSeparatedInput.onchange = function () {
						updateSetting(monthAndYearSeparatedInput, function () {
							datepicker.options.setMonthAndYearSeparated(monthAndYearSeparatedInput.checked);
						});
					};

					var positionFixingInput = document.getElementById('positionFixing');
					positionFixingInput.onchange = function () {
						updateSetting(positionFixingInput, function () {
							datepicker.options.setPositionFixing(positionFixingInput.checked);
						});
					};

					var dateAvailabilityResolverInput = document.getElementById('dateAvailabilityResolver');
					dateAvailabilityResolverInput.onchange = function () {
						if (dateAvailabilityResolverInput.value === '') {
							datepicker.options.setDateAvailabilityResolver(null);
						} else {
							datepicker.options.setDateAvailabilityResolver(createCallback('date', dateAvailabilityResolverInput.value));
						}
						datepicker.render();
					};

					var cellContentResolverInput = document.getElementById('cellContentResolver');
					cellContentResolverInput.onchange = function () {
						if (cellContentResolverInput.value === '') {
							datepicker.options.setCellContentResolver(null);
						} else {
							datepicker.options.setCellContentResolver(createCallback('day', cellContentResolverInput.value));
						}
						datepicker.render();
					};

					var cellClassesResolverInput = document.getElementById('cellClassesResolver');
					cellClassesResolverInput.onchange = function () {
						if (cellClassesResolverInput.value === '') {
							datepicker.options.setCellClassesResolver(null);
						} else {
							datepicker.options.setCellClassesResolver(createCallback('day', cellClassesResolverInput.value));
						}
						datepicker.render();
					};

					var onBeforeSelectInput = document.getElementById('onBeforeSelect');
					onBeforeSelectInput.onchange = function () {
						updateListener('beforeSelect', onBeforeSelectInput.value, 'event, day, previousDay');
					};

					var onSelectInput = document.getElementById('onSelect');
					onSelectInput.onchange = function () {
						updateListener('select', onSelectInput.value, 'event, day, previousDay');
					};

					var onBeforeOpenAndCloseInput = document.getElementById('onBeforeOpenAndClose');
					onBeforeOpenAndCloseInput.onchange = function () {
						updateListener('beforeOpenAndClose', onBeforeOpenAndCloseInput.value, 'event, isOpening');
					};

					var onOpenAndCloseInput = document.getElementById('onOpenAndClose');
					onOpenAndCloseInput.onchange = function () {
						updateListener('openAndClose', onOpenAndCloseInput.value, 'event, isOpening');
					};

					var onBeforeMonthChangeInput = document.getElementById('onBeforeMonthChange');
					onBeforeMonthChangeInput.onchange = function () {
						updateListener('beforeMonthChange', onBeforeMonthChangeInput.value, 'event, month, previousMonth');
					};

					var onMonthChangeInput = document.getElementById('onMonthChange');
					onMonthChangeInput.onchange = function () {
						updateListener('monthChange', onMonthChangeInput.value, 'event, month, previousMonth');
					};

					var classesPrefixInput = document.getElementById('classesPrefix');
					classesPrefixInput.onchange = function () {
						updateSetting(classesPrefixInput, function () {
							datepicker.options.setClassesPrefix(classesPrefixInput.value);
						});
					};

					var todayInput = document.getElementById('today');
					todayInput.onchange = function () {
						updateSetting(todayInput, function () {
							datepicker.options.setToday(todayInput.value);
						});
					};

					var goBackHtmlInput = document.getElementById('goBackHtml');
					goBackHtmlInput.onchange = function () {
						updateSetting(goBackHtmlInput, function () {
							datepicker.options.setGoBackHtml(goBackHtmlInput.value);
						});
					};

					var goForwardHtmlInput = document.getElementById('goForwardHtml');
					goForwardHtmlInput.onchange = function () {
						updateSetting(goForwardHtmlInput, function () {
							datepicker.options.setGoForwardHtml(goForwardHtmlInput.value);
						});
					};

					var closeHtmlInput = document.getElementById('closeHtml');
					closeHtmlInput.onchange = function () {
						updateSetting(closeHtmlInput, function () {
							datepicker.options.setCloseHtml(closeHtmlInput.value);
						});
					};

					var resetHtmlInput = document.getElementById('resetHtml');
					resetHtmlInput.onchange = function () {
						updateSetting(resetHtmlInput, function () {
							datepicker.options.setResetHtml(resetHtmlInput.value);
						});
					};

					var deselectHtmlInput = document.getElementById('deselectHtml');
					deselectHtmlInput.onchange = function () {
						updateSetting(deselectHtmlInput, function () {
							datepicker.options.setReselectHtml(deselectHtmlInput.value);
						});
					};

					for (var i = 0; i < 7; i++) {
						(function () {
							var dayOfWeek = i;
							var dayOfWeekTranslationInput = document.getElementById('dayOfWeekTranslation' + dayOfWeek);
							dayOfWeekTranslationInput.onchange = function () {
								updateSetting(dayOfWeekTranslationInput, function () {
									datepicker.options.translator.setDayOfWeekTranslation(dayOfWeek, dayOfWeekTranslationInput.value);
								});
							};
						})();
					}

					for (var j = 0; j < 12; j++) {
						(function () {
							var month = j;
							var monthTranslationInput = document.getElementById('monthTranslation' + month);
							monthTranslationInput.onchange = function () {
								updateSetting(monthTranslationInput, function () {
									datepicker.options.translator.setMonthTranslation(month, monthTranslationInput.value);
								});
							};
						})();
					}
				})();
				
				//
				
				
			/* Code to prevent leaving text area when tab key is pressed, instead returning a tab character */
				(function() {
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
				})();
				
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

