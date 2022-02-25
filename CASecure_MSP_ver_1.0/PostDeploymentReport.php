<html>
<head>
<title>CASecure >Post-Deployment Report</title>

<script src="sorttable.js"></script>

<!-- <script type="text/javascript" src="/path/to/jquery.tablesorter.js"></script> -->

<!-- <script src="BarGraph.js"></script> -->

<link rel="stylesheet" type="text/css" href="TABLE_FORMAT_1.css">

<?php require 'includes/header.php'; ?>

<?php require 'includes/alert.php'; ?>

<?php require 'includes/navigation.php'; ?>

<style>

	
	/*
	table.sortable tbody {
		counter-reset: sortabletablescope;}
	table.sortable thead tr::before {
		content: "";
		display: table-cell; }
	table.sortable tbody tr::before {
		content: counter(sortabletablescope);
		counter-increment: sortabletablescope;
		display: table-cell; }
	*/
</style>

<span class="breadcrumbs"> <a href="Reporting.php">Reporting</a> > Post-Deployment Report</span>
<br>
<div class="pagetitle">Post-Deployment Report</div>

<script>
	document.getElementById("reportingNav").className = "current-menu-item";
	
	var chartHeight = 250;
	var chartWidth = 400;
	
	var curUser = "APIAdmin";
	var password = "AllieCat7";
	var server = "ibmendpoint.internal.cassevern.com";
	// HTTP encode periods so as to not ruin the URL for the AJAX call
	server = server.replace(/\./g, "%2E");
	
	// AJAX call for the Post Deployment Report
	var postDeploymentProxy = "proxies/PostDeploymentReport.php?user=" + curUser + "&pass=" + password + "&serv=" + server;
	xmlTableParser(postDeploymentProxy, "#postDeploymentTable");
	
	// Function performs an AJAX call to a designated API proxy and collects the data into an HTML table
	function xmlTableParser(proxyURL, tableID) {
		var request = new XMLHttpRequest();
		request.open("GET", proxyURL, true);
		request.send();
		request.onreadystatechange = function() {
			if ((this.readyState === 4) && (this.status === 200)) {
				// Convert the XML document to a string
				xmlString = this.responseText.toString();
				// Eliminate the Meta-Query from the <Query> tag in the XML results as it can potentially cause issues when reading the XML code
				var badQuery = xmlString.substring((parseInt(xmlString.search("Resource"))-1), (parseInt(xmlString.search('Result'))-5));
				xmlString = xmlString.replace(badQuery, '');
				// Convert string of XML code back into an XML document
				var parser = new DOMParser();
				var xmlDoc = parser.parseFromString(xmlString,"text/xml");
				// Collect the number of rows (<Tuple> tags) and columns (<Answer> tags per <Tuple> tag) of data from the XML document
				var tupleCount = xmlDoc.getElementsByTagName("Tuple").length;
				var columnCount = xmlDoc.getElementsByTagName("Answer").length / tupleCount;
				// Build the HTML Table's contents from the data in the XML document
				var rowHTML = "";
				for (var i = 0; i < tupleCount; i++) {
					rowHTML = '<tr>';
					for (var j = 0; j < columnCount; j++) {
						rowHTML += '<td nowrap>' + xmlDoc.getElementsByTagName("Answer")[((columnCount*i)+j)].childNodes[0].nodeValue + '</td>';
					}
					rowHTML += '</tr>';
					$(tableID).append(rowHTML);
				}
				
				var deploymentArray = [0,0,0];
				var deploymentTable = document.getElementById('postDeploymentTable');
				
				var deploymentRows = deploymentTable.rows, 
					deploymentRowCount = deploymentRows.length,
					deploymentCells;
				
				for (var r = 1; r < deploymentRowCount; r++) {
					deploymentCells = deploymentRows[r].cells;
					deploymentArray[0] += isNaN(deploymentCells[6].innerHTML) ? 0 : parseInt(deploymentCells[6].innerHTML);
					deploymentArray[1] += isNaN(deploymentCells[7].innerHTML) ? 0 : parseInt(deploymentCells[7].innerHTML);
					deploymentArray[2] += isNaN(deploymentCells[8].innerHTML) ? 0 : parseInt(deploymentCells[8].innerHTML);;
				}
				//alert(deploymentArray);
				
				compliancePct = Math.round((deploymentArray[1]/deploymentArray[0])*100); 
				
				var myColor = ["#0000CC", "#006600", "#FF0000"];
				var myData = deploymentArray;
				var myCategories = ["Applicable Patches", "Installed Patches", "Outstanding Patches"];
				
				$('#chart').remove();
				$('#canvasDiv').append('<canvas id="chart" width="580" height="400" style = "border: 1px solid #333; display: none;"></canvas>');
				
				var chartColours = [];            // Chart colours (pulled from the HTML table)
				chartColours = myColor;
				
				var tableLegend;
				var cellid1 = "";
				var cellid2 = "";
				var cellid3 = "";
				var canvas;                       // The canvas element in the page
				
				var maxWidth = 0;
				
				canvas = document.getElementById('chart');
				canvas.style.display = "inline";
				tableLegend = document.getElementById('chartData');
				tableLegend.style.display = "table";
				
		 		var tempContext = canvas.getContext('2d');
				//tempContext.clearRect(0, 0, canvas.width, canvas.height);
				//tempContext.beginPath();
				
				tableLegend.innerHTML = '<tr><th id="header1">Category</th><th id="header2">Patches</th><th id="header3">Percent</th></tr>';
				
		 		for (var i = 0; i < myData.length; i++) {
		 			// Extract and store the cell colour
		 			//chartColours[i] = randomColor();	
		 			
		 			tableLegend.innerHTML += '<tr><td id="row' + i + 'cell0" style = "fontWeight: bolder"><span style="display:inline-block;width:13px;margin-bottom:3px;background-color: ' + chartColours[i] + ';"> &nbsp;</span><b><span> &nbsp;' + myCategories[i] + '</span></b></td><td id="row' + i + 'cell1" style = "fontWeight: bolder;"><b><span style="float:right;">' + myData[i] + '</span></b></td><td id="row' + i + 'cell2"><b><span style="float: right;">' + ((myData[i]/myData[0])*100).toFixed(2).toString() + '%</span></b></td></tr>';
		 			
		 			if (tempContext.measureText(myCategories[i]).width > maxWidth) { 
		 				maxWidth = tempContext.measureText(myCategories[i]).width;
		 			}
		 			
		 			cellid1 = "row" + i + "cell0";
		 			cellid2 = "row" + i + "cell1";
		 			cellid3 = "row" + i + "cell2";
		 			
		 			document.getElementById(cellid1).style.color = chartColours[i];
		 			document.getElementById(cellid2).style.color = chartColours[i];
		 			document.getElementById(cellid3).style.color = chartColours[i];
		 		}
				
				tableLegend.width = maxWidth + 280;
				
				eventWindowLoaded();
				
				$("#pLoad").remove();
				
				function eventWindowLoaded() {
					var c = document.getElementById('chart');//graph1
					if (c && c.getContext) {
						graph1 = new BarGraph(c);
						graph1.vals = myData; //[6300,200,5520,3760,9,320,819,1308,405,-2101,640,1999];
						graph1.cats = myCategories; //["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
						graph1.title = "Patches per Categtory";
						graph1.bgcol1 = "#eef";
						graph1.bgcol2 = "#77f";
						graph1.effect = "twang";
						graph1.drawGraph();
					}
				}
				
function BarGraph(c) {
	//Private
	var that = this;
	var c2d = c.getContext('2d');
	var cw = c.width;
	var ch = c.height;
	//c2d.clearRect ( 0, 0, cw, ch );
	var elems = 0;
	var pitch = 0;
	var yaxisint = 0;
	var yaxisposticks = 0;
	var yaxisnegticks = 0;
	var yaxisticks = 0;
	var xaxisypos = 0;
	var animphase;
	var barper = 100;
	
	//Public
	this.bw = 0.70; 		//Bars are 70% width of each interval by default
	this.xmargin = 40;	//Applied to left and right
	this.ymargin = 30;	//Applied to top and bottom
	this.cats = [];
	this.vals = [];
	this.title = "";
	this.frames = 10;
	this.scalefactor = 1;
	this.scalesuffix = "";
	this.bgcol1 = "#fff";
	this.bgcol2 = "#fff";
	this.fgcol = "#000";
	this.barcol1 = "#4040C0";
	this.barcol2 = "#7070C0";
	this.effect = "none";
	
	this.drawGraph = function() {
		var tw;
		var maxval = 0;
		var minval = 0;
		//Setup
		elems = this.cats.length;
		pitch = (cw-(2*this.xmargin)) / elems;	//horizontal interval
		for (i = 0; i < elems; i++) {
			if (this.vals[i] > maxval) maxval = Math.round(this.vals[i]);	//Track maximum value
			if (this.vals[i] < minval) minval = Math.round(this.vals[i]);	//Track minimum value
		}
		//Decide on Y-axis scale
		var range = maxval - minval;
		var absmax = maxval;
		if (Math.abs(minval) > maxval) absmax = Math.abs(minval);
		var a = Math.ceil(absmax/3);
		var b = a.toString().length;   //Length of interval value if split into 3
		//If estimated interval has a string length of more than 1 (i.e. decimal 10 or greater) then apply rounding to next lower power of 10
		if (b>1) a = parseInt(a / Math.pow(10,b-1))*Math.pow(10,b-1);
		var posticks = Math.ceil(maxval / a);
		var negticks = Math.ceil(-minval / a);
		this.yaxisint = a;
		this.yaxisposticks = posticks;
		this.yaxisnegticks = negticks;
		this.yaxisticks = posticks + negticks;
		//Should we abbreviate using thousands or millions?
		if (absmax > 10000000) {
			this.scalefactor = 1000000;
			this.scalesuffix = "M";
		} else if (absmax > 10000) {
			this.scalefactor = 1000;
			this.scalesuffix = "K";
		} else {
			this.scalefactor = 1;
			this.scalesuffix = "";
		}
		switch(this.effect)  {
			//No special effect, just draw complete graph
			case "none":
				this.drawFrame();
				this.drawBars();
				this.drawXAxis();
				break;
			//Grow bars
			case "grow":
				barper = 0;
				this.animBars();
				break;
			//Twang bars
			case "twang":
				barper = 0;
				this.animphase = 1;
				this.animBars();
				break;
		}
	}
	
	this.animBars = function() {
		switch(this.effect)  {
			//Grow the bars in a linear fashion	
			case "grow":
				barper += 10;
				this.drawFrame();
				this.drawBars();
				this.drawXAxis();
				if (barper < 100) setTimeout(function() { that.animBars() }, 50);
				break;
			//Grow the bars past their final value then shrink back below the final value, before finally finishing at the correct value.
			case "twang":
				this.drawFrame();
				switch(this.animphase) {
					//Initial growth
					case 1:
						barper += 15;
						this.drawBars();
						if (barper > 110) 	this.animphase = 2;
						setTimeout(function(){ that.animBars()}, 50);
						break;
					//Shrink back
					case 2:
						barper -= 10;
						this.drawBars();
						if (barper < 100) 	this.animphase = 3;
						setTimeout(function(){ that.animBars()}, 50);
						break;
					//Stretch to final
					case 3:
						barper = 100;
						this.drawBars();
						break;
				}
				this.drawXAxis();
				break;
		}
	}
	
	this.drawFrame = function() {
		//Background
		var gradfill = c2d.createLinearGradient(0, 0, 0, ch);
		gradfill.addColorStop(0, this.bgcol1);
		gradfill.addColorStop(1, this.bgcol2);
		c2d.font="Bold 20px Calibri";
		c2d.textBaseline="middle";
		c2d.fillStyle = "#ebedf2";//gradfill;
		c2d.fillRect(0, 0, cw, ch);	//Background
		//Graph title
		c2d.strokeStyle = this.fgcol;
		c2d.fillStyle = this.fgcol;
		var tw = c2d.measureText(this.title).width;
		c2d.fillText(this.title, (cw/2)-(tw/2), 15);
		//How far down the y-axis should the x-axis reside? Store for later use
		this.xaxisypos = this.ymargin + (ch - 2 * this.ymargin) * (this.yaxisposticks / this.yaxisticks);
		//Vertical axis
		c2d.lineWidth = 2;
		c2d.beginPath();
		c2d.moveTo(this.xmargin, this.ymargin);
		c2d.lineTo(this.xmargin, ch - this.ymargin);
		c2d.stroke();
		//Draw y-axis labels
		c2d.font="Bold 11px Calibri";
		var vint = (ch - (2 * this.ymargin)) / this.yaxisticks;		//vertical interval
		var vindex = -this.yaxisnegticks;
		for (i = 0; i <= this.yaxisticks; i++) {
			var y = ch - this.ymargin - (i * vint);
			var ylabel = vindex * this.yaxisint / this.scalefactor;
			ylabel = ylabel.toString()+this.scalesuffix;
			tw = c2d.measureText(ylabel).width;
			//tick marks
			c2d.lineWidth = 2;
			c2d.strokeStyle = this.fgcol;
			c2d.beginPath();
			c2d.moveTo(this.xmargin+1, y);
			c2d.lineTo(this.xmargin-3, y);
			c2d.stroke();
			//horizontal guide lines
			c2d.lineWidth = 0.5;
			c2d.strokeStyle = "#888";
			c2d.beginPath();
			c2d.moveTo(this.xmargin+1, y);
			c2d.lineTo(cw - this.xmargin, y);
			c2d.stroke();
			c2d.fillText(ylabel, this.xmargin - tw - 5, y);
			vindex++;
		}
	}
	
	this.drawBars = function() {
		var barw = pitch * this.bw;
		var graphrange = this.yaxisticks * this.yaxisint;
		//Draw value bars
		c2d.lineWidth = 1;
		c2d.strokeStyle = this.barcol1;
		var sp = this.xmargin - (pitch*(0.5+(0.5*this.bw)));	//left-edge starting position of first bar. N.B. Equals half an interval plus half of the bar width percentage.
		for (i = 0; i < elems; i++) {
			var barx = sp + ((i+1) * pitch);
			var br = this.vals[i] / graphrange; 								//Bar ratio versus total graph value range
			var barh = br * (ch - 2 * this.ymargin);						//Scale up to usable graph area
			barh = barh * (barper/100);										//Scale by percentage (supports animation)
			var bary = this.xaxisypos - barh;					//Position of top of bar to be drawn
			//Create a gradient fill appropriate to
			//the location and dimensions of the bar
		    var gradfill = c2d.createLinearGradient(0, 0, bary+barh, bary+barh); // 0, bary, 0, bary+barh
			gradfill.addColorStop(0,  "#ddd"); //this.barcol1
			gradfill.addColorStop(1, myColor[i]); // this.barcol2
	 				//var sliceGradient = context.createLinearGradient( 0, 0, canvasWidth*.75, canvasHeight*.75 );
	 				//sliceGradient.addColorStop( 0, sliceGradientColour );
	 				//sliceGradient.addColorStop( 1, myColor[slice] ); //'rgb(' + myColor[slice].join(',') + ')'
			c2d.fillStyle = gradfill;
			//Draw this bar
			c2d.fillRect(barx, bary, barw, barh);
			c2d.strokeRect(barx, bary, barw, barh);
		}
	}
	
	this.drawXAxis = function() {
		c2d.strokeStyle = this.fgcol;
		c2d.fillStyle = this.fgcol;
		c2d.lineWidth = 2;
		c2d.beginPath();
		c2d.moveTo(this.xmargin, this.xaxisypos);
		c2d.lineTo(cw-this.xmargin, this.xaxisypos);
		c2d.stroke();
		//X-axis labels
		c2d.font="Bold 11px Calibri";
		var barw = pitch * this.bw;
		for (i = 0; i < elems; i++) {
			var x = this.xmargin- (pitch / 2) + ((i+1) * pitch);
			c2d.beginPath();
			c2d.moveTo(x, this.xaxisypos);
			c2d.lineTo(x, this.xaxisypos +3);
			c2d.stroke();
			tw = c2d.measureText(this.cats[i]).width;
			if (tw > barw) {
				while (tw > barw) {
					this.cats[i] = this.cats[i].substring(0, (this.cats[i].length -1));
					tw = c2d.measureText(this.cats[i]).width;
				}
				this.cats[i] += "...";
				tw = c2d.measureText(this.cats[i]).width;
			}
			c2d.fillStyle = myColor[i];
			c2d.fillText(this.cats[i], x-(tw/2), this.xaxisypos +12);
		}
	}
	
	this.randomiseValues = function() {
		var pow = Math.floor(Math.random()*7)+1;
		var max = Math.pow(10,pow);
		for (var i = 0; i < elems; i++) {
			this.vals[i] = Math.floor(Math.random()*max)-(max/2);
		}
		this.drawGraph();
		return false;
	}
}
				/*
				CanvasJS.addColorSet("deploymentShades",
					[//colorSet Array
						"#0000CC",
						"#006600",
						"#FF0000"              
					]
				);
				var chart = new CanvasJS.Chart("postDeploymentChart", {
					colorSet: "deploymentShades",
					title:{
						text: "Post-Deployment",
						padding: 5
					},
					height: chartHeight,
					width: chartWidth,
					backgroundColor: "#ebedf2",
					data: [              
						{
							// Change type to "line", "doughnut", "splineArea", etc.
							type: "column",
							indexLabelPlacement: "outside", 
							//showInLegend: true,
							dataPoints: [
								{ label: "Applicable Patches",  y: deploymentArray[0], name: "Applicable"  },
								{ label: "Installed Patches",   y: deploymentArray[1], name: "Installed"  },
								{ label: "Outstanding Patches",  y: deploymentArray[2], name: "Outstanding"  },
							]
						}
					]
				});
				chart.render();
				$("#pLoad").remove();
				*/
				
				function sortTable(n, tableName) {
					var table, rows, switching, i, x, y, shouldSwitch;
					table = document.getElementById(tableName);
					switching = true;
					while (switching) {
						switching = false;
						rows = table.getElementsByTagName("TR");
						for (i = 1; i < (rows.length - 1); i++) {
							shouldSwitch = false;
							x = rows[i].getElementsByTagName("TD")[n];
							y = rows[i + 1].getElementsByTagName("TD")[n];
							if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
								shouldSwitch= true;
								break;
							}
						}
						if (shouldSwitch) {
							rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
							switching = true;
						}
					}
				}
				
				var newTableObject = document.getElementById("postDeploymentTable");
				
				sorttable.makeSortable(newTableObject);
			/*
				$(document).ready(function() 
					{ 
						$("#postDeploymentTable").tablesorter(); 
					}
				);
			*/
			/*	
				var prevID = "";
				var currentID = "";
				var destTable = document.getElementById("postDeploymentTable");
				var downTable = document.getElementById("postDeploymentTableFinal");
				var origTable = document.getElementById("compliance"),
					origRows = origTable.rows, 
					origRowcount = origRows.length, r,
					origCells, origCellcount, c, origCell;
				var destRowNum = 0;
				for (r = 0; r < origRowcount; r++) {
					origCells = origRows[r].cells;
					currentID = origCells[1].innerHTML;
					origCellcount = origCells.length;
					if (currentID == prevID) {
						destTable.rows[destTable.rows.length-1].cells[6].innerHTML = parseInt(destTable.rows[destTable.rows.length-1].cells[6].innerHTML) + parseInt(origTable.rows[r].cells[6].innerHTML);
						destTable.rows[destTable.rows.length-1].cells[7].innerHTML = parseInt(destTable.rows[destTable.rows.length-1].cells[7].innerHTML) + parseInt(origTable.rows[r].cells[7].innerHTML);
						destTable.rows[destTable.rows.length-1].cells[8].innerHTML = parseInt(destTable.rows[destTable.rows.length-1].cells[8].innerHTML) + parseInt(origTable.rows[r].cells[8].innerHTML);
						destTable.rows[destTable.rows.length-1].cells[9].innerHTML =  complianceColor(Math.round((parseInt(destTable.rows[destTable.rows.length-1].cells[7].innerHTML) / parseInt(destTable.rows[destTable.rows.length-1].cells[6].innerHTML)) * 100)); 

					} else {
						var destRow = destTable.insertRow(destTable.rows.length);
						for (c = 0; c < origCellcount; c++) {
							origCell = origCells[c];
							var destCell = destRow.insertCell(c);
							switch (c){
								case 0: // Column with Fixlet ID and Computer ID, this is hidden
										destCell.innerHTML = origCell.innerHTML;
										destCell.className = "cellHidden";
										break;
								case 1: // Column with Computer ID, this is hidden
										destCell.innerHTML = origCell.innerHTML;
										destCell.className = "cellHidden";
										break;
								case 2: // Column Computer
										destCell.innerHTML = origCell.innerHTML;	
										alert(destCell.innerHTML);
										break;
								case 3: // Column Operating System
										destCell.innerHTML = origCell.innerHTML;				
										break;
								case 4: // Column IP Address
										destCell.innerHTML = origCell.innerHTML;				
										break;
								case 5: // Column Last Report Time
										destCell.innerHTML = origCell.innerHTML;				
										break;
								case 6: // 6 is Applicable Fixlets, it is right justified
								case 7: // 7 is Installed Fixlets
								case 8: // 8 is Outstanding Fixlets
								case 9: // 9 is Compliance percentage
										destCell.innerHTML = origCell.innerHTML;
										destCell.align = "right";
										destCell.className = "count" + c;
										break;
								default:
							}
						}
						var destCell = destRow.insertCell(origCellcount);
						destCell.align = "right";
						destCell.innerHTML = complianceColor(parseInt(destRow.cells[7].innerHTML)*100);

						destRowNum++;
					}
					prevID = origCells[1].innerHTML;
				}
				
				function complianceColor(val) {
					if (val >= 90) {
						return '<span style="color:#008800;">' + val + '%</span>';
					} else if (val >= 70 && val < 90) {
						return '<span style="color:#15428b;">' + val + '%</span>';
					} else if (val <= 20 && val >= 0) {
						return '<span style="color:#cc3333;">' + val + '%</span>';
					} else if (val == -99) {
						return '<span style="color:#15428b;">--</b></span>';
					} else {
						return '<span style="color:orange;">' + val + '%</span>';
					}
				}
		*/
					// Adding a little dynamic element to the report by
	// animating the compliance percentage number
				function animateValue(id, start, end, duration) {
					var obj = document.getElementById(id);
					var range = end - start;
					var minTimer = 50;
					var stepTime = Math.abs(Math.floor(duration / range));
					stepTime = Math.max(stepTime, minTimer);
					var startTime = new Date().getTime();
					var endTime = startTime + duration;
					var timer;
					
					function run() {
						var now = new Date().getTime();
						var remaining = Math.max((endTime - now) / duration, 0);
						var value = Math.round(end - (remaining * range));
						obj.innerHTML = value + "&#37;";
						if (value == end) {
							clearInterval(timer);
						}
					}
	
					timer = setInterval(run, stepTime);
					run();
				}
				animateValue("complianceValue", 0, compliancePct, 2000);
			}
		}
	}
</script>
<div id="container" style="min-width:1000px; max-width:1000px; margin-bottom: 30px; position: relative; height:300">
	<div id="canvasDiv" style="width: 60%; position: absolute; top: 0px; left: 0px">
		<canvas id="chart" width="900" height="600" style = "display: none;"></canvas>
	</div>
	<div style="width: 40%; position: absolute; top: 0px; right: 0px">
		<div style = "position: relative">
			<div align="right" style="margin-top: 10px; width: 25%; position: absolute; top:0px; left: 90px">
				<div><p style="font-family: Arial, Helvetica, sans-serif; font-size: 40px;">Compliance</p><br></div>
				<div align="right"><p style="font-family: Arial, Helvetica, sans-serif; font-size: 80px;" id="complianceValue">0</p></div>
				<div align="right"><p style="font-family: Arial, Helvetica, sans-serif; font-size: 20px; white-space: nowrap; margin-top: 10px;" id="computerCount">161 Systems</p><br></div>
			</div>
		</div>
		<br>
		<table id="chartData" style = "display: none; position: absolute; top: 230px; left: 10px;">
			<tr>
				<th>Category</th><th>Patches</th><th>Percent</th>
			</tr>
		</table>
	</div>

<br>
<div>&nbsp;</div>
<!--
<div id="postDeploymentChart" style="width:60%; height: 600;">
	<canvas id="chart" width="900" height="600" style = "display: none;"></canvas>
	<h4 id="pLoad">Loading...</h4>
</div>
-->

<div style=" min-width:1000px; max-width:1000px; margin-top: 200px; position: absolute; top: 490" id="mainDiv">
	<table id="postDeploymentTable" class="sortable">
		<th>Computer Fixlet ID</th><th>Computer ID</th><th>Computer Name</th><th>Operating System</th><th>IP Address</th><th>Last Report Time</th><th>Applicable Patches</th><th>Installed Patches</th><th>Outstanding Patches</th>
	</table>
	
	<table id="postDeploymentTableFinal">
		<th>Computer Name</th><th>Operating System</th><th>IP Address</th><th>Last Report Time</th><th>Applicable Patches</th><th>Installed Patches</th><th>Outstanding Patches</th><th>Compliance</th>
	</table>
</div>
</div>
<?php require 'includes/footer.php'; ?>