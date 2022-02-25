<script>
	$(document).ready(function() {
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
						'<span class="alertdate" style="position:absolute; right:30px;">' + entry['date'] + '&nbsp;&nbsp;&nbsp; <a onclick="closeAlert()"><img src="includes/close2.jpg" onclick="closeAlert()" onmouseover="" style="cursor:pointer;"></a></span>' + 
					'</span>'
				);
			});
		};
		getAlert();
		setInterval(function() {
			getAlert();
		}, 5000);
	});
	
</script>
 <div style="position:relative;"><img src="includes/spacer.gif"></div> 
<div class="alertbox" style="position:relative; background-color:#ffa4a4;">
	<img src="includes/lock3.jpg" class="alertlock">
	<!--<div style="font-size:12pt;">&nbsp; -->
	<span class="alerttitle">Vulnerability Alert:
		<span class="alertnotice"> &nbsp;Microsoft release patch for Intel meltdown</span>
		<span class="alertdate"> 9am 1/3/2018&nbsp;&nbsp;&nbsp; <!-- <a onclick="closeAlert()"><img src="includes/close2.jpg" onclick="closeAlert()" onmouseover="" style="cursor:pointer;"></a> --></span>
	</span>
	<!-- </div> -->
</div>

<script>
	function closeAlert() {
		$('.alertbox').remove();
	}
</script>

