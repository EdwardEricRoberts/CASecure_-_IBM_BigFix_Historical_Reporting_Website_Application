<html>
<head>
<title>CASecure >Requests</title>

<?php require 'includes/header.php'; ?>

<?php require 'includes/alert.php'; ?>

<?php require 'includes/navigation.php'; ?>

<script>document.getElementById("infoNav").className = "current-menu-item";</script>

<span class="breadcrumbs"> Information > Requests</span>
<br>

<div class="pagetitle">Requests</div>
<form><table class="requesttable">
	<tr>
		<td>Subject</td>
		<td><input type="text" id="subject" name="username" size="30"></td>
	</tr>
	<tr>
		<td>Request Type</td>
		<td><select name="request_type" id="typeSelect">
		<!--	<option value="none">None</option>
			<option value="sign-in">Sign-in Problem</option>
			<option value="howdoi">How do I...</option>
			<option value="billing">Billing/Payment</option>
			<option value="feature">Feature Request</option>
			<option value="bug">Bug</option>
			<option value="other">Other</option> -->
		</select>
		</td>
	</tr>
	<tr>
		<td>Criticality</td>
		<td><select name="critacality" id="criticalitySelect">
		<!--	<option value="none">None</option>
			<option value="low">Low</option>
			<option value="medium">Medium</option>
			<option value="high">High</option> -->
		</select></td>
	</tr>
	<tr>
		<td>Description</td>
		<td><textarea name="description" id="description" rows="8" cols="50"></textarea></td>
	</tr>
	<tr>
		<td colspan="2"><input type="reset" value="Cancel"><input id="submitRequest" type="submit"></td>
	</tr>
</table></form>

<script>
	var curUser = "APIAdmin";
	var password = "AllieCat7";
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
	
/*  */
	$("#submitRequest").on("click", function() {
		var subject = document.getElementById("subject").value;
		var typeSelection = document.getElementById("typeSelect").value;
		var critSelection = document.getElementById("criticalitySelect").value;
		var description = document.getElementById("description").value;
		
		subject = httpEncode(subject);
		description = httpEncode(description);
		
	/* Function to encode user generated fields to be URL friendly */
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
			input = input.replace(/\*/g, "%2A");
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
		
		//alert("database/RequestsFormLog.php?user=" + curUser + "&subj=" + subject + "&type=" + typeSelection + "&crit=" + critSelection + "&desc=" + description)
		
	/* Sends data to the PHP database proxy */
		var queryPost = new XMLHttpRequest();
		queryPost.open("POST", "database/RequestsFormLog.php?user=" + curUser + "&subj=" + subject + "&type=" + typeSelection + "&crit=" + critSelection + "&desc=" + description, true);
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

</script>

<?php require 'includes/footer.php'; ?>