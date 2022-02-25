<html>
<head><title>test</title></head>
<body>


<div id="checkbox-container">
  <div>
    <label for="option1">Option 1</label>
    <input type="checkbox" id="option1">
  </div>

</div>
    

  <script src='https://code.jquery.com/jquery-2.2.2.js'></script>
 
      <script id="rendered-js" >
var checkboxValues = JSON.parse(localStorage.getItem('checkboxValues')) || {},
$checkboxes = $("#checkbox-container :checkbox");

$checkboxes.on("change", function () {
  $checkboxes.each(function () {
    checkboxValues[this.id] = this.checked;
  });

  localStorage.setItem("checkboxValues", JSON.stringify(checkboxValues));
});

// On page load
$.each(checkboxValues, function (key, value) {
  $("#" + key).prop('checked', value);
});
//# sourceURL=pen.js
    </script>	
<!-- END TEST CONTENT  -->
				
</body>
</html>

