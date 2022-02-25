<?php
require_once './includes/authenticate.php';

$currentUserID = $_SESSION['userid'];

if (isset($_POST["submit"])) {
	require_once './includes/db_connect.php';
	
	
	$output = "";
	foreach($_POST as $formKey => $formData) {
		$output .= "[".$formKey."] => ".$formData.", ";
	}
	echo '<script language="javascript">';
	echo 'alert("ssd'.$output.'")';
	echo '</script>';
	
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
      <title>CASecure > Messages</title>
	  
	  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.js"></script>
      <script src="bootstrap.js"></script>
      <link rel="stylesheet" href="bootstrap.css">
      <link rel="stylesheet" type="text/css" href="TABLE_FORMAT_1.css">
	  <?php require 'includes/headerRevised_2.php'; ?>
      <?php require 'includes/navigationRevised.php'; ?>
<!-- start breadcrumbs -->                
	  <div class="span-24">
         <div style="background-color:#cacaca; width:100%; margin-top:2px; height:23px; line-height:23px;"> 
            <span style="text-align:center;">
               <img src="includes/breadcrumbs_start.jpg" style="height:23px;">
               <span style="z-index:0; position:absolute;">Information &nbsp;&raquo;&nbsp; Alerts</span>
            </span>
         </div>
      </div>
<!-- end breadcrumbs -->

      <br>
      <div class="span-24 last">
	     <div class="pagetitle2" style="margin-bottom:20px; margin-left:10px; float:left">Alerts<br>
		 </div>	  
	  </div>
<script>document.getElementById("infoNav").className = "current-menu-item";</script>




<div class="container">
	<form id="deactivateUserAlerts" >
		<table id="alertsList" class="dashboxlong" style="border:1px solid lightgrey;">
		<!--	
			<tr>
				<td><img src="SampleImages/Messages/yes_lock.jpg">
				</td>
				<td class="alertMessage">Vulnerability Alert: Microsoft release patch for Intel meltdown
				</td>
				<td>9:00am 1/3/2018
				</td>
				<td><img src="SampleImages/Messages/close.jpg">
				</td>
			</tr>
			<tr>
				<td><img src="SampleImages/Messages/yes_lock.jpg">
				</td>
				<td class="alertMessage">CAS Admin: New custom report is now available under your "Reports" tag
				</td>
				<td>12:34am 12/23/2017
				</td>
				<td><img src="SampleImages/Messages/close.jpg">
				</td>
			</tr>
			<tr>
				<td><img src="SampleImages/Messages/yes_lock.jpg">
				</td>
				<td class="alertMessage">CAS Admin: Servers decommissioned
				</td>
				<td>10:14am 11/3/2017
				</td>
				<td><img src="SampleImages/Messages/close.jpg">
				</td>
			</tr>


			</tr>
		-->
		</table>
	</form>

<script>
	var currentUserID = "<?php echo $_SESSION['userid']; ?>";
	
	var alertsListFetchURL = "http://localhost/CASecure_MSP_ver_1.0/database/fetch/FetchActiveAlerts.php?cid=" + currentUserID;
	
	var alertsListRequest = new XMLHttpRequest();
	alertsListRequest.open("GET", alertsListFetchURL, true);
	alertsListRequest.send();
	alertsListRequest.onreadystatechange = function() {
		if ((this.readyState === 4) && (this.status === 200)) {
			var alertsListXML = this.responseXML;
			
			if (alertsListXML.getElementsByTagName("Error").length == 0) {
				var alertsListCount = alertsListXML.getElementsByTagName("alert").length;
				//alert(alertsListCount);
				var alertsListRowHTML = '', alertTitle = "", alertMessage = "", userGroup = "", timestamp = "", expiration = "", priorityColor = "", priorityName = "";
				for (var i = 0; i < alertsListCount; i++) {
					priorityColor = alertsListXML.getElementsByTagName("html_color_hex_code")[i].childNodes[0].nodeValue;
					alertId = alertsListXML.getElementsByTagName("alert_id")[i].childNodes[0].nodeValue;
					alertTitle = alertsListXML.getElementsByTagName("title")[i].childNodes[0].nodeValue;
					alertMessage = (alertsListXML.getElementsByTagName("message")[i].childNodes[0].nodeValue == "NULL")?(""):alertsListXML.getElementsByTagName("message")[i].childNodes[0].nodeValue;
					timestamp = alertsListXML.getElementsByTagName("timestamp")[i].childNodes[0].nodeValue;
					dateArr = timestamp.split("-");
					time = (dateArr[2].split(" "))[1];
					timestamp = dateArr[1] + "/" + (dateArr[2].split(" "))[0] + "/" + dateArr[0] + " " + time;
					expiration = alertsListXML.getElementsByTagName("expiration")[i].childNodes[0].nodeValue;
					if(expiration != "None") {
						expArr = expiration.split("-");
						expiration = expArr[1] + "/" + expArr[2] + "/" + expArr[0];
					}
					priorityName = alertsListXML.getElementsByTagName("priority_name")[i].childNodes[0].nodeValue;
					
					alertsListRowHTML = '<tr>';
					//alertsListRowHTML += '<form>';
					alertsListRowHTML += '<td style="color: ' + priorityColor + ';"><img src="SampleImages/Messages/yes_lock.jpg"></td>';
					alertsListRowHTML += '<td style="display:none;">' + alertId + '</td>';
					alertsListRowHTML += '<td class="alertMessage" style="color: ' + priorityColor + ';"><span style="font-weight: bold;">' + alertTitle + '</span>: ' + alertMessage + '</td>';
					alertsListRowHTML += '<td style="color: ' + priorityColor + ';">' + timestamp + '</td>';
					alertsListRowHTML += '<td style="color: ' + priorityColor + ';">Expiration: ' + expiration + '</td>';
					alertsListRowHTML += '<td style="color: ' + priorityColor + ';">' + priorityName + '</td>';
					alertsListRowHTML += '<td style="color: ' + priorityColor + ';">' + 
											//'<div data-tip="Remove Alert">' + 
												//'<form action="#" method="get">' + //'<form onsubmit="return confirm(Do you wish to deactivate alert ' + alertTitle + ')">' + 
												//	'<input type="image" src="SampleImages/Messages/close.jpg" border="0" name="removeAlert' + i + '" onclick="alert(\"Delete Alert?\")" alt="Delete Alert" value="Submit">' + 
												'<input class="deactivate" type="submit" value="Deactivate">' +
												//'</button>' + 
												//'</form>' + 
											//'</div>' + 
										'</td>'; //<a class="removeAlert" type="submit" href="#"><img src="SampleImages/Messages/close.jpg"></a>
					//alertsListRowHTML += '</form>';
					alertsListRowHTML += '</tr>';
					
					$('#alertsList').append(alertsListRowHTML);
				}
				$('#alertsList').on('click', '.deactivate', function() {
					var alertIdNum = $(this).closest('tr').find('td:eq(1)').text();
					var alertText = $(this).closest('tr').find('td:eq(2)').text();
					$('#deactivateUserAlerts').submit(function() {
						if(confirm('Remove Alert "' + alertText + '"?')) {
							//$(this).closest('tr').remove();
							return true;
						}
						else {
							return false;
						}
					});
				});
				
				//function remove_alert() {
				//	confirm("Are you sure you want to remove Alert?");
				//}
			}
			else {
				
			}
			
			
		}
	}
	
	//$(function() {
		$('.removeAlert').click(function(e) {
			e.preventDefault();
			if (window.confirm("Are you sure you want to remove Alert?")) {
				
			}
		});
	//});
</script>

<?php require 'includes/footer.php'; ?>
</div>
<script>document.getElementById("infoNav").className = "current-menu-item";</script>
