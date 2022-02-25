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
						'<span class="alertdate" style="position:absolute; right:30px;">' + entry['date'] + '&nbsp;&nbsp;&nbsp; <button onclick="closeDIV()">X</button></span>' + 
					'</span>'
				);
			});
		};
		getAlert();
		setInterval(function() {
			getAlert();
		}, 5000);
	});
	function closeDIV() {
		var x=document.getElementById("alertDIV");
		if (x.style.display === "none") {
            x.style.display = "block";
        } else {
            x.style.display = "none";
        }
    }
		
</script>
 <div style="position:relative;"><img src="includes/spacer.gif" id="alertDIV"></div> 
<div class="alertbox" style="position:relative; background-color:#ffa4a4;" id="alertDIV">
	<img src="includes/lock3.jpg" class="alertlock">
	<span class="alerttitle">Vulnerability Alert:
		<span class="alertnotice"> &nbsp;Microsoft release patch for Intel meltdown</span>
		<span class="alertdate"> 9am 1/3/2018&nbsp;&nbsp;&nbsp;</span>
	</span>
</div>

<script>
	function closeAlert() {
		$('.alertbox').remove();
	}
</script>

