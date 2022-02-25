<?php
require_once './includes/authenticateAdmin.php';
//print_r($_SESSION); 
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
		<title>Edit User</title>




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
					<span style="z-index:0; position:absolute;"><a href="Administration.php" class="breadcrumblink">Administration</a> &nbsp;&raquo;&nbsp; Edit User</span>
				</span>
			</div>
		</div>
<!-- end breadcrumbs -->
		
		<br>
		<div class="span-24 last">
			<div class="pagetitle2" style="margin-bottom:20px; margin-left:10px; float:left">Edit User<br>
			</div>	  
		</div>
		
		<div class="container">
		<form action="#" method="post">
			<div class="span-23 last" style="background-color:#f5f5f5; padding:4px;">
				
			<form>
				<div class="span-7 prepend-1 last">
					
					<input id="searchUserName" type="text" name="searchUserName" placeholder="Search User Name...">
						  <button type="submit" style="height:22px; padding-top:3px; ">Submit</button>
					<br><br>
				</div><br>
				<hr noshade width="100%">
				
				<div class="span-22 last" style="background-color:#f5f5f5; padding:4px;">
				
					<br>
					<div class="span-5 prepend-1">
						<p>
							<label for="username">Username</label><br>
							<input id="username" name="username" type="text" style="color:#fff;" readonly>
						</p>
						<p>
							<label for="firstname">First Name</label><br>
							<input id="firstname" name="firstname" type="text">
						</p>
						
						<p>
							<label for="email">Email</label><br>
							<input id="email" name="email" type="text">
						</p>
					</div>
					
					<div class="span-5 prepend-1">
						<p>
							<label for="password">Password</label><br>
							<input id="password" name="password" type="password">
							<span style="color:red;">Enter a password longer than 8 characters</span> 
						</p>
						<p>
							<label for="confirm_password">Confirm Password</label><br>
							<input id="confirm_password" name="confirm_password" type="password">
							<span style="color:red;">Please confirm your password</span> 
						</p>
						
					</div>
					<div class="span-7 prepend-1">
						<p>
							<label for="selectBigFix">Select BigFix User to Map to</label><br>
							<select name="selectBigFix" id="selectBigFix" width="30" required onChange="makeSelection()">
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
							<label for="selectSite" id="labelSite" style="visibility:hidden;">Select the default Site for this user</label><br>
							<select name="selectSite" id="selectSite" width="30" required style="visibility:hidden;">
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
							<label for="selectComputerGroup" id="labelComputerGroup" style="visibility:hidden;">Select the default Computer Group</label><br>
							<select name="selectComputerGroup" id="selectComputerGroup" width="30" required style="visibility:hidden;">
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
							<input type="radio" name="isAdmin" value="yes" checked>No<br>
						</p>
					</div>
					
					
					<script src="http://code.jquery.com/jquery-1.11.3.min.js"></script> 
					<script src="js/app.js" type="text/javascript" charset="utf-8"></script>
					<script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
					<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
					<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>

					<script src="js/app.js"></script>

				</div>
			
			</div>
			<div class="span-24 last">
				
				<input id="submit" type="submit" value="Save Changes">
				<input id="submit" type="submit" value="Clear Form" <!-- onclick="deleteUser()-->">
			</div>
			
			</form>
			<div class="span-24 last">
				<br>
				<div style="text-align:center;">--- placeholder for error messages here. ---</div>
				<br>
				
			</div>
				
			<script>
				// search/typeahead code start
				
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
				
				/*An array containing all the user names in the system:*/
				var usernames = ["bzhou","bwilcoxen","bshekore","bsoule","cgerstmyer","cdodson",
				"cbrenner","ctillman","csoule","ctrue","dwilliamson","dbettino","dkeslar","eroberts","emccosby",
				"fgalbani","fwilson","glittle","gsmith","hmarsh","jking","jallen","jsparkman","fviera",
				"jpoppe","jfrentheway","jkozireski","kpollack","kevans","kangel","kzimmerman","mgreco","mjohnson",
				"mpittinger","miafrate","mpolhaus","mgilbert","mbelluz","nstrand","nherneker","dgerstmyer","pparsa","rhowell",
				"sconn","sbrogan","sfrost","smuchow","scoon","sdrew","sburgess","tleonard","tmaskell",
				"tbauchspies","tfares","trexroth","bbeeson","wzheng","zkontrec","gwarner"];

				/*initiate the autocomplete function on the "myInput" element, and pass along the usernames array as possible 
				autocomplete values:*/
				autocomplete(document.getElementById("searchUserName"), usernames);

				// end search/typeahead code
			
			
			
				// show or hide select boxes until a BigFix user has been selected
				function makeSelection() {
					if (document.getElementById("selectBigFix").selectedIndex != 0) {
						document.getElementById("selectSite").style.visibility = "visible";
						document.getElementById("selectComputerGroup").style.visibility = "visible";
						document.getElementById("labelSite").style.visibility = "visible";
						document.getElementById("labelComputerGroup").style.visibility = "visible";
					} else {
						document.getElementById("selectSite").style.visibility = "hidden";
						document.getElementById("selectComputerGroup").style.visibility = "hidden";
						document.getElementById("labelSite").style.visibility = "hidden";
						document.getElementById("labelComputerGroup").style.visibility = "hidden";
					}
				}
			
			
			
				(function () {
					var input = document.getElementById('my-input');
					
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


				
				
				function deleteUser() {
					confirm("Are you sure you want to permanently delete this user?");
				}
				
			</script>
			
			
			<?php require 'includes/footer.php'; ?>
		</form>
		</div>
		</div>

