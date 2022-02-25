<?php
require_once './includes/authenticateAdmin.php';
//print_r($_SESSION); 
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
		<title>Edit Alert</title>




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
					<span style="z-index:0; position:absolute;"><a href="Administration.php" class="breadcrumblink">Administration</a> &nbsp;&raquo;&nbsp; Edit Alert</span>
				</span>
			</div>
		</div>
<!-- end breadcrumbs -->
		
		<br>
		<div class="span-24 last">
			<div class="pagetitle2" style="margin-bottom:20px; margin-left:10px; float:left">Edit Alert<br>
			</div>	  
		</div>
		
		<div class="container">
		<form action="#" method="post">
			<div class="span-23 last" style="background-color:#f5f5f5; padding:4px;">
				
			

  
<form>
				<div class="span-7 prepend-1 last">
					
<input type="text" placeholder="Search Alerts ..." name="search" style="margin-top:-2; padding:5px;">
      <button type="submit" style="height:30px; padding-top:7px; ">Submit</button>
<br><br>
				</div><br>
				
			
<div class="span-22 last">
<hr noshade width="100&">
</div>

<div class="span-7 prepend-3 last">
	<p>
						<label for="alerttitle">Alert Title</label><br>
						<input id="alerttitle" name="alerttitle" type="text">
					</p>
<p>
						<label for="selectBigFix">Change User Group</label><br>
						<select name="selectBigFix" id="selectBigFix" width="30" required>
							<option value="userBigfixInstructions">Select User Group</option>
							<option value="option">User Group 1</option>
							<option value="option">User Group 2</option>
							<option value="option">User Group 3</option>
							<option value="option">User Group 4</option>
							<option value="option">User Group 5</option>
							<option value="option">User Group 6</option> 
						</select>
					</p>

	<p>
						<label for="alertmessage">Alert Message</label><br>
						<!--<textarea></textarea>
						<script src="http://js.nicedit.com/nicEdit-latest.js" type="text/javascript"></script>
<script type="text/javascript">bkLib.onDomLoaded(nicEditors.allTextAreas);</script>-->

<textarea name="mess" id="alertmessage" class="alert_textarea" style="resize:none; height:75px; width:300px;" maxlength="130">  </textarea>
					</p>
	<p>
						<label for="alertexpiry">Alert Expiration</label><br>
						<input type="text" id="my-input" size="20">
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

				
				<input id="submit" type="submit" value="Create Alert">
				<input id="submit" type="submit" value="Delete" onclick="deleteAlert()">
			</div>
			
			</form>
			<div class="span-24 last">
				<br>
				<div style="text-align:center;">--- placeholder for error messages here. ---</div>
				<br>


			</div>

			<script>
			
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


				function deleteAlert() {
					confirm("Are you sure you want to permanently delete this alert message?");
				}
				
				
				
			</script>
			
			
			<?php require 'includes/footer.php'; ?>
		</form>
		</div>
		</div>

