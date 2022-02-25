<!DOCTYPE html>
<html lang="en">
<head>
   

    <base href="http://crlcu.github.io/multiselect/" />
    
    <title>Testing multi select</title>
    
    
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" />
    
    <link rel="stylesheet" href="css/style2.css" />
</head>
<body>
   
    

    
    <div id="wrap" class="container">            
        
            
            <div class="row">
                <div class="col-xs-5">
                    <select name="from[]" id="customSort" class="form-control" size="8" multiple="multiple">
                        <option value="1" data-position="1">Item 1</option>
                        <option value="5" data-position="2">Item 5</option>
                        <option value="2" data-position="3">Item 2</option>
                        <option value="4" data-position="4">Item 4</option>
                        <option value="3" data-position="5">Item 3</option>
                    </select>
                </div>
                
                <div class="col-xs-2">
                    <button type="button" id="customSort_rightAll" class="btn btn-block"><i class="glyphicon glyphicon-forward"></i></button>
                    <button type="button" id="customSort_rightSelected" class="btn btn-block"><i class="glyphicon glyphicon-chevron-right"></i></button>
                    <button type="button" id="customSort_leftSelected" class="btn btn-block"><i class="glyphicon glyphicon-chevron-left"></i></button>
                    <button type="button" id="customSort_leftAll" class="btn btn-block"><i class="glyphicon glyphicon-backward"></i></button>
                </div>
                
                <div class="col-xs-5">
                    <select name="to[]" id="customSort_to" class="form-control" size="8" multiple="multiple"></select>
                </div>
            </div>
            
            
    </div>

<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>


<script type="text/javascript" src="dist/js/multiselect.min.js"></script>



<script type="text/javascript">
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
</script>
</body>
</html>
