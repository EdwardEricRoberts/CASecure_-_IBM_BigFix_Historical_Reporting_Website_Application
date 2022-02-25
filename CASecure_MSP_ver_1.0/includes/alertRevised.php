<!-- start alert box -->
<script>
	function closeDIV() {
		var x=document.getElementById("alertDIV");
		if (x.style.display === "none") {
            x.style.display = "block";
        } else {
            x.style.display = "none";
        }
    }
	
	
	$(document).ready(function() {
		
		/*
		function getAlert() {
			$('.alerttitle').remove();
			
			$.getJSON("alertMessages.json", function(data){
				//$.each(data.alertMessage, function(){
				//	$("#testUL").append("<li>Title: "+this['title']+"</li><li>Message: "+this['message']+"</li><li>Date: "+this['date']+"</li><br />");
				//});
				var entry = data.alertMessage[Math.floor(Math.random()*data.alertMessage.length)];
				$('.alertbox').append(
					'<span class="alerttitle">' + entry['title'] + 
						'<span class="alertnotice"> &nbsp;' + entry['message'] + '</span>' + 
						'<span class="alertdate" style="float:right; margin-right:10px;">' + entry['date'] + '&nbsp;&nbsp;&nbsp; </span>' + 
					'</span>'
				);
			});
		};
		getAlert();
		*/
		getAlert();
		setInterval(function() {
			getAlert();
		}, 5000);
		function getAlert() {
			$('.alerttitle').remove();
			
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
						//for (var i = 0; i < alertsListCount; i++) {
							var i = Math.floor(Math.random()*alertsListCount);
							priorityColor = alertsListXML.getElementsByTagName("html_color_hex_code")[i].childNodes[0].nodeValue;
							alertTitle = alertsListXML.getElementsByTagName("title")[i].childNodes[0].nodeValue;
							alertMessage = (alertsListXML.getElementsByTagName("message")[i].childNodes[0].nodeValue == "NULL")?(""):alertsListXML.getElementsByTagName("message")[i].childNodes[0].nodeValue;
							timestamp = alertsListXML.getElementsByTagName("timestamp")[i].childNodes[0].nodeValue;
							dateArr = timestamp.split("-");
							time = (dateArr[2].split(" "))[1];
							var hour24 = time.split(":")[0];
							var minute = time.split(":")[1];
							var cleanTime = (parseInt(hour24) >= 12)?((parseInt(hour24) != 12)?((parseInt(hour24) - 12).toString() + ":" + minute + " PM"):("12:" + minute + " PM")):((parseInt(hour24) == 0)?("12:" + minute + " AM"):((parseInt(hour24)).toString() + ":" + minute + " AM"));
							
							timestamp = dateArr[1] + "/" + (dateArr[2].split(" "))[0] + "/" + dateArr[0] + " " + cleanTime;
							expiration = alertsListXML.getElementsByTagName("expiration")[i].childNodes[0].nodeValue;
							if(expiration != "None") {
								expArr = expiration.split("-");
								expiration = expArr[1] + "/" + expArr[2] + "/" + expArr[0];
							}
							priorityName = alertsListXML.getElementsByTagName("priority_name")[i].childNodes[0].nodeValue;
							
							$('.alertbox').append(
								'<span class="alerttitle">' + alertTitle + ":" + 
									'<span class="alertnotice"> &nbsp;' + alertMessage + '</span>' + 
									'<span class="alertdate" style="float:right; margin-right:10px;">' + timestamp + '&nbsp;&nbsp;&nbsp; </span>' + 
								'</span>'
							);
						//}
					}
				}
			}
		}
	});
</script>

      <div style="background-color:#ffa4a4; padding: 15px 0; margin-top:2px; margin-left:0; display:block;" id="alertDIV" class="span-24">
	     <div class="alertbox" id="alertDIV">
	        <img src="includes/lock3.jpg" class="alertlock">
	        <span class="alerttitle">
		       <span class="alertnotice"> &nbsp;</span>
		       <span class="alertdate" style="float:right; margin-right:10px;"> &nbsp;&nbsp;&nbsp;</span>
	        </span>
         </div>
   
      </div>

<!-- end alert box -->  

