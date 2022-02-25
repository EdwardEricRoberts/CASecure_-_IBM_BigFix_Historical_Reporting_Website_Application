

<!-- core jquery library -->
      <script src="jquery-3.3.1.min.js"></script>
<!-- CSS for jquery datatables -->
	  <link rel="stylesheet" type="text/css" href="DataTables/jquery.dataTables.min.css ">
<!-- javascript for the datatables functionality -->
	  <script type="text/javascript" charset="utf8" src="DataTables/datatables.js"></script>
<!-- javascript for the datatables ui -->
	  <script type="text/javascript" src="DataTables/dataTables.jqueryui.min.js"></script>
<!-- CSS for blueprint page layout -->
      <link rel="stylesheet" href="css/blueprint/screen.css" type="text/css" media="screen" />
      <link rel="stylesheet" href="css/blueprint/print.css" type="text/css" media="print" />
      <link rel="stylesheet" id="uncomp-core-ie" href="css/blueprint/ie.css" type="text/css" media="screen" />
<!-- CSS for local and custom CSS -->	  
      <link rel="stylesheet" id="uncomp-core-ie" href="MSSP_CSS.css" type="text/css" />
<!-- ??? -->	  
      <!--<link rel="stylesheet" id="plugin-debugger" href="debug.css" type="text/css" media="screen" />-->
<!-- jquery for used with various charts -->	
	  <script src="https://canvasjs.com/assets/script/jquery.canvasjs.min.js"></script>
      <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
	
<!-- new header includes from Eric -->
      <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
      <script src="http://d3js.org/d3.v5.min.js"></script>
	  <!--<script src="d3BarChart.js"></script>
	  <script src="d3PieChart.js"></script>-->
	  
	  <!-- Calls to the JS files in real time, Remove and replace with commented versions above in final version -->
      <script src="<?php echo 'd3BarChart.js?v='.filemtime('d3BarChart.js'); ?>"></script>
	  <script src="<?php echo 'd3PieChart.js?v='.filemtime('d3PieChart.js'); ?>"></script>
<!-- end new header includes from Eric -->	
	
	
   </head>

   <body>
      <div class="container">
	    
<!-- top banner graphic -->	
         <div class="span-24"> 		
	        <div style="position:relative;">
               <img src="includes/banner.jpg">			
               <div style="background-color:#185725; position:absolute; right:0; top:0; text-align:right; color:#fff; font-weight:bold;">
                  &nbsp;&nbsp;&nbsp;
				  Welcome Chris
				  &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;
				  <a href="alerts.php" class="toprightmenu">Alerts</a>
				  &nbsp;&nbsp;|&nbsp;&nbsp;
				  <a href="#" class="toprightmenu">SignOut</a>
				  &nbsp;&nbsp;&nbsp;
               </div>
            </div>
         </div>   
<!-- end top banner -->		
   