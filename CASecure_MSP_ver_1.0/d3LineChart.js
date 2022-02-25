function d3LineChart(chartData, reportType, chartTitle, chartDivId, legendDivId, chartAreaWidth, chartAreaHeight, marginBase) {
		// This is the format our dates are in, e.g 23/05/2014
	var timeFormat = d3.timeFormat('%m/%d/%Y %H:%M:%S'); //time.format

	var dates = [],
	    dateStrings = [],
	    temps = [];

	chartData.forEach(function(d) {

		// Keep array of original date strings
		dateStrings.push(
			(((d.timestamp.getMonth() + 1) < 10) ? ("0" + (d.timestamp.getMonth() + 1).toString()) : (d.timestamp.getMonth() + 1).toString()) + 
			"/" + 
			(((d.timestamp.getDate()) < 10) ? ("0" + (d.timestamp.getDate()).toString()) : (d.timestamp.getDate()).toString()) + 
			"/" + 
			(d.timestamp.getFullYear()).toString() + 
			" " + 
			(((d.timestamp.getHours()) < 10) ? ("0" + (d.timestamp.getHours()).toString()) : (d.timestamp.getHours()).toString()) + 
			":" + 
			(((d.timestamp.getMinutes()) < 10) ? ("0" + (d.timestamp.getMinutes()).toString()) : (d.timestamp.getMinutes()).toString()) + 
			":" + 
			(((d.timestamp.getSeconds()) < 10) ? ("0" + (d.timestamp.getSeconds()).toString()) : (d.timestamp.getSeconds()).toString())
		);

		// Convert date string into JS date, add it to dates array
		dates.push(d.timestamp);

		// Add high temperature to temps array
		temps.push(d.percent_1);

	});
	
	var chartWidth = chartAreaWidth,
	    chartHeight = chartAreaHeight,
	    margin = {
	    	top: marginBase, 
	    	right: marginBase - (marginBase * 0.25), 
	    	bottom: marginBase + 15, 
	    	left: marginBase + (marginBase * 0.25)
	    };
	
	var width = chartAreaWidth - (margin.left + margin.right); 
	var height = chartAreaHeight - (margin.top + margin.bottom);
	
	var svgLayout = d3.select(chartDivId)
		.style('text-align', 'center') //center
		.style("font-family", "'Open Sans', sans-serif");
	
	//var container = d3.select(chartDivId);
	
	var container = svgLayout.append('div')
		.attr('id', '#container')
		.style('width', chartAreaWidth + 'px')
		.style('height', chartAreaHeight + 'px')
		//.style('margin', 'auto')
		.style('background-color', '#EBEDF2'); //'grey');//
	
	// Create SVG area
	var svg = container.append('svg')
		.style('width', '100%')
		.style('height', '100%');
		//.attr('width', chartWidth)
		//.attr('height', chartHeight);
	
	var defs = svg.append("defs");
	
	// clipping area
	defs.append("clipPath")	
		.attr("id", "clip")
		.append("rect")
			.attr('x', margin.left)
			.attr('y', margin.top)
			.attr("width", width)
			.attr("height", height);
	
	// Invisible background rect to capture all zoom events
	var backRect = svg.append('rect')
		.style('stroke', 'none')
		.style('fill', '#FFF')
		.style('fill-opacity', 0)
		.attr('x', margin.left)
		.attr('y', margin.top)
		.attr("width", width)
		.attr("height", height);
	
	// Create the area that will contain the Path Line
	var chart = svg.append('g')
		.attr('class', 'plot-area')
		.attr('pointer-events', 'none')
		.style("clip-path", "url(#clip)");
	
	// Create the area that will contain the Axes
	var axes = svg.append('g')
		.attr('pointer-events', 'none')
		.style('font-size', '11px');
	
	// Dynamic Time Formats
	var formatMillisecond = d3.timeFormat(".%L"),
		formatSecond = d3.timeFormat("%_S sec"),
		formatMinute = d3.timeFormat("%_I:%M %p"),
		formatHour = d3.timeFormat("%_I %p"),
		formatDay = d3.timeFormat("%a-%b %e"),
		formatWeek = d3.timeFormat("%B %e-%Y"),
		formatMonth = d3.timeFormat("%B-%Y"),
		formatYear = d3.timeFormat("%Y");
	
	// Function to apply Dynamic Time Formats at certain zooms
	function multiTimeFormat(date) {
		return (d3.timeSecond(date) < date ? formatMillisecond
			: d3.timeMinute(date) < date ? formatSecond
			: d3.timeHour(date) < date ? formatMinute
			: d3.timeDay(date) < date ? formatHour
			: d3.timeMonth(date) < date ? (d3.timeWeek(date) < date ? formatDay : formatWeek)
			: d3.timeYear(date) < date ? formatMonth
			: formatYear)(date);
	}
	
	// Function to format output text of Dynamic Time Formats
	function xTicks(t) {
		t.each(function(d){
			var self = d3.select(this);
				var s = self.text().split('-');
				self.text('');
				self.append("tspan")
					.attr("x", 0)
					.attr("dy", ".8em")
					.text(s[0]);
				self.append("tspan")
					.attr("x", 0)
					.attr("dy", "1em")
					.text(s[1]);
		})
	}
	
	// set the X Scale
	var xScale = d3.scaleTime()
		.range([margin.left, chartWidth - margin.right])
		.domain(d3.extent(dates))
		.nice();

	// set the Y Scale
	var yScale = d3.scaleLinear()
		.range([chartHeight - margin.bottom, margin.top])
		.domain([0,100]);
//
	// X Axis Format
	var xAxis = d3.axisBottom(xScale)
		.tickFormat(multiTimeFormat);
	
	// Y Axis Formats
	var yAxis = d3.axisLeft(yScale);
	var yAxis2 = d3.axisRight(yScale);

	// Add the x axis to the chart
	var xAxisEl = axes.append('g')
		.attr('class', 'x-axis axis')
		.attr('transform', 'translate(' + 0 + ',' + (chartHeight - margin.bottom) + ')')
		.call(xAxis);
	
	// Set the dynamic style fo the X Axis Ticks
	axes.selectAll('.x-axis .tick text')
		.call(xTicks);
	
	// Add the Left Y Axis to the chart
	var yAxisEl = axes.append('g')
		.attr('class', 'y-axis axis')
		.attr('transform', 'translate(' + margin.left + ',' + 0 + ')')
		.call(yAxis);
	
	// Add the Right Y Axis to the chart
	var yAxisEl2 = axes.append('g')
		.attr('class', 'y-axis right axis')
		.attr('transform', 'translate(' + (chartWidth - margin.right) + ',' + 0 + ')')
		.call(yAxis2);
	
	// Add '%' to the end of the Y Axis numbers
	d3.selectAll('.y-axis text')
		.each(function (d,i) {
			var tick = d3.select(this);
			tick.text(tick.text() + '%');
		})
	
	// vertical grid lines
	axes.append('g')
		.attr('class', 'grid vertical-grid')
		.attr('transform', 'translate(0, ' + (chartHeight - margin.bottom) + ')')
		.call(
			d3.axisBottom()
				.scale(xScale)
				.tickSize(-height, 0, 0)
				.tickFormat('')
		)
	
	// horizontal grid lines
	axes.append('g')
		.attr('class', 'grid horizontal-grid')
		.attr('transform', 'translate(' + margin.left + ', 0)')
		.call(
			d3.axisLeft()//svg.axis()
				.scale(yScale)//.orient('left')
				.tickSize(-width, 0, 0)
				.tickFormat('')
		)
	
	// Styles for the Grid Lines
	d3.selectAll('.grid path')
		.style('stroke-width', 0);
	
	d3.selectAll('.grid .tick line')
		.style('stroke', '#9FAAAE')
		.style('stroke-opacity', 0.3);
	
	d3.selectAll('.axis')
		//.style('font-size', 'calc(' + xScale.bandwidth()/170 + 'vw)')
		//.style("font-family", "'Open Sans', sans-serif");
		.style('font-size', '10px')
		.style('font-weight', 'bold');
	
/*	
	// Format y-axis gridlines
	yAxisEl.selectAll('line')
		.style('stroke', '#BBB')
		.style('stroke-width', '1px')
		.style('shape-rendering', 'crispEdges');
	
	xAxisEl.selectAll('line')
		.style('stroke', '#BBB')
		.style('stroke-width', '1px')
		.style('shape-rendering', 'crispEdges');
	yAxisEl2.selectAll('line')
		.style('stroke', '#BBB')
		.style('stroke-width', '1px')
		.style('shape-rendering', 'crispEdges');
*/

	//Set the line Gradient
	svg
		.append('linearGradient')
			.attr('id', 'complianceGradient')
			.attr("gradientUnits", "userSpaceOnUse")
			.attr('x1', 0)
			.attr('y1', margin.top)
			.attr('x2', 0)
			.attr('y2', (chartAreaHeight - margin.bottom))
		.selectAll('stop')
			.data([
			//
				{offset: '0%',   color: "#10FF00"}, 
				{offset: '1%',   color: "#10FF00"}, 
				{offset: '2%',   color: "#20FF00"}, 
				{offset: '3.5%',   color: "#30FF00"}, 
				{offset: '5%',   color: "#40FF00"}, 
				{offset: '6.5%',   color: "#50FF00"}, 
				{offset: '8%',   color: "#60FF00"}, 
				{offset: '9.5%',   color: "#70FF00"}, 
				{offset: '11%',   color: "#80FF00"}, 
				{offset: '12.5%',   color: "#90FF00"}, 
				{offset: '14%',   color: "#A0FF00"}, 
				{offset: '15.5%',   color: "#B0FF00"}, 
				{offset: '17%',   color: "#C0FF00"}, 
				{offset: '18.5%',   color: "#D0FF00"}, 
				{offset: '20%',   color: "#E0FF00"}, //
				{offset: '22.5%',   color: "#F0FF00"}, 
				{offset: '25%',   color: "#FFFF00"}, 
				{offset: '27.5%',   color: "#FFF000"}, 
				{offset: '30%',   color: "#FFE000"}, 
				{offset: '32.5%',   color: "#FFD000"}, 
				{offset: '35%',   color: "#FFC000"}, 
				{offset: '47.5%',   color: "#FFB000"}, 
				{offset: '50%',   color: "#FFA000"}, 
				{offset: '52.5%',   color: "#FF9000"}, 
				{offset: '55%',  color: "#FF8000"}, 
				{offset: '57.5%',  color: "#FF7000"}, 
				{offset: '60%',  color: "#FF6000"}, 
				{offset: '62.5%',  color: "#FF5000"}, 
				{offset: '65%',  color: "#FF4000"}, 
				{offset: '67.5%',  color: "#FF3000"}, 
				{offset: '70%',  color: "#FF2000"}, 
				{offset: '77.5%',  color: "#FF1000"}, 
				{offset: '80%',  color: "#FF0000"},
				{offset: '100%', color: "#FF0000"}
			//
			/*
				{offset: '0%',   color: "#00FF00"}, 
				{offset: '5%',   color: "#33ff00"}, 
				{offset: '10%',  color: "#66ff00"}, 
				{offset: '15%',  color: "#99ff00"}, 
				{offset: '20%',  color: "#ccff00"}, 
				{offset: '25%',  color: "#FFFF00"}, 
				{offset: '30%',  color: "#FFCC00"}, 
				{offset: '35%',  color: "#ff9900"}, 
				{offset: '40%',  color: "#ff6600"}, 
				{offset: '45%',  color: "#FF3300"}, 
				{offset: '50%',  color: "#FF0000"},
				{offset: '100%', color: "#FF0000"}
			*/
			])
		.enter().append('stop')
			.attr('offset', d => d.offset)
			.attr('stop-color', d => d.color);

	// Path generator function for our data
	var pathGenerator = d3.line()
		.curve(d3.curveMonotoneX)
		.x(function(d, i) { return xScale(dates[i]); })
		.y(function(d, i) { return yScale(temps[i]); });
	
	var areaGenerator = d3.area()
		.x(function(d, i) { return xScale(dates[i]); })
		.y1(function(d, i) { return (yScale(temps[i]) + 5); })
		.y0(function(d, i) { return (yScale(temps[i]) - 5); })

	// Series container element
	var series = chart.append('g');
	

	// Add the temperature series path to the chart
	
	series.append('path')
		.attr('class', 'linePath')
		.attr('d', pathGenerator(dates))
		.attr('vector-effect', 'non-scaling-stroke')
		.style('paint-order', 'stroke fill')
		.style('fill', 'none')
		.style('stroke', 'url(#complianceGradient)')
		.style('stroke-width', '4px')
	/*
	series.append('path')
		.attr('class', 'linePath')
		.attr('d', areaGenerator(dates))//pathGenerator(dates))
		.attr('vector-effect', 'non-scaling-stroke')
		.style('fill', 'url(#complianceGradient)')
		.style('stroke', 'black')
		.style('stroke-width', '1px')
	*/
	
	
	// Add zooming and panning functionality, only along the x axis
	var zoom = d3.zoom()//d3.behavior.zoom()
		.scaleExtent([1, 100])
		.translateExtent([[margin.left, margin.top], [(width + margin.left), (height + margin.bottom)]])
		.extent([[margin.left, margin.top], [(width + margin.left), (height + margin.bottom)]])
		.on('zoom', function zoomHandler() {
			
			// Set the new transformed scales
			const currentTransform = d3.event.transform;
			var newXScale = currentTransform.rescaleX(xScale); //d3.event.transform.rescaleX(xScale)
			
			// Transform X Axis when Zooming
			axes.select('.x-axis')
				.call(xAxis.scale(newXScale));
			
			// Change X Axis Tick Text
			axes.selectAll('.x-axis .tick text')
				.call(xTicks);
			
			// Transform Vertical Grid Lines when Zooming
			axes.select(".vertical-grid")
				.call(
					d3.axisBottom().scale(newXScale)
						.tickSize(-height, 0, 0)
						.tickFormat('')
				)
				.style('stroke', '#9FAAAE')
				.style('stroke-opacity', 0.3);
			
			
			series.attr('transform', 'translate(' + currentTransform.x + ',0) scale(' + currentTransform.k + ',1)');
			
			//zoom.attr("transform", currentTransform);
			slider.property("value", currentTransform.k);
			
			backRect.on('mousemove', function() {
				// Coords of mousemove event relative to the container div
				var coords = d3.mouse(container.node());
				
				// Value on the x scale corresponding to this location
				var xValMouse = newXScale.invert(coords[0]);
				
				var xVal = closestMatch(xValMouse, dates);
				
				// Date object corresponding the this x value. Add 12 hours to
				// the value, so that each point occurs at midday on the given date.
				// This means date changes occur exactly halfway between points.
				// This is what we want - we want our tooltip to display data for the
				// closest point to the mouse cursor.
				var d = new Date(xVal.getTime());// + 3600000 * 12);
				
				// Format the date object as a date string matching our original data
				var date = timeFormat(d);
				
				// Find the index of this date in the array of original date strings
				var i = dateStrings.indexOf(date.toString());
				
				// Does this date exist in the original data?
				var dateExists = i > -1;
				
				// If not, hide the tooltip and return from this function
				if (!dateExists) {
					hideTooltip();
					return;
				}
				
				// If we are here, the date was found in the original data.
				// Proceed with displaying tooltip for of the i-th data point.
				
				// Get the i-th date value and temperature value.
				var _date = dates[i],
					_temp = temps[i];
				
				// Update the position of the activePoint element
				activePoint
					.attr('cx', newXScale(_date))
					.attr('cy', yScale(_temp))
				
				// Update tooltip content
				tt.html(tooltipFormatter(_date, _temp, chartData[i].applicable_1, chartData[i].installed_1, chartData[i].outstanding_1));
				
				// Get dimensions of tooltip element
				var dim = tt.node().getBoundingClientRect();
				
				// Update the position of the tooltip. By default, above and to the right
				// of the mouse cursor.
				var tt_top = yScale(_temp) - dim.height - 10, //coords[1] - dim.height - 10,
					tt_left = newXScale(_date) + 10; //coords[0] + 10;
				
				// If right edge of tooltip goes beyond chart container, force it to move
				// to the left of the mouse cursor.
				if (tt_left + dim.width > chartWidth)
					tt_left = newXScale(_date) - dim.width - 10;//coords[0] - dim.width - 10;
				
				if (tt_top < 0)
					tt_top = yScale(_temp) + 10;
				
				tt
					.style('top', tt_top + 'px')
					.style('left', tt_left + 'px')
		
				// Show tooltip if it is not already visible
				if (tt.style('visibility') != 'visible')
					showTooltip();
				
			});
			
			// Add mouseout event handler
			backRect.on('mouseout', hideTooltip);
			
			backRect.on('mousedown', hideTooltip);
				
		});

	// The backRect captures zoom/pan events
	backRect.call(zoom);
/*

	// Function for resetting any scaling and translation applied
	// during zooming and panning. Returns chart to original state.
	function resetZoom() {

		zoom.scale(1);
		zoom.translate([0, 0]);
		
		// Set x scale domain to the full data range
		xScale.domain(d3.extent(dates));

		// Update the x axis elements to match
		axes.select('.x-axis')
			.transition()
			.call(xAxis);

		// Remove any transformations applied to series elements
		series.transition()
			.attr('transform', "translate(0,0) scale(1,1)");

	};

	// Call resetZoom function when the button is clicked
	d3.select("#reset-zoom").on("click", resetZoom);
*/
	
	// Active point element
	var activePoint = svg.append('circle')
		.attr('cx', 0)
		.attr('cy', 0)
		.attr('r', 5)
		.attr('pointer-events', 'none')
		.style('stroke', 'none')
		.style('fill', 'blue')//'red')
		.style('fill-opacity', 0);
	
	// Set container to have relative positioning. This allows us to easily
	// position the tooltip element with absolute positioning.
	container.style('position', 'relative');
	
	// Create the tooltip element. Hidden initially.
	var tt = container.append('div')
		.style('padding', '5px')
		.style('border', '1px solid #AAA')
		.style('color', 'black')
		.style('position', 'absolute')
		.style('visibility', 'hidden')
		.style('background-color', '#F5F5F5')
	
	// Function for hiding the tooltip
	function hideTooltip() {
		tt.style('visibility', 'hidden');
		activePoint.style('fill-opacity', 0);
	}
	
	// Function for showing the tooltip
	function showTooltip() {
		tt.style('visibility', 'visible');
		activePoint.style('fill-opacity', 1);
	}
	
	// Tooltip content formatting function
	function tooltipFormatter(date, temp, applicable, installed, outstanding) {
		var dayFormat = d3.timeFormat('%a');
		//var dayFormat = d3.time.format('%a');
		var dateFormat = d3.timeFormat('%B %d, %Y');
		//var dateFormat = d3.time.format('%B %d, %Y');
		var timeFormat = d3.timeFormat('%_I:%M:%S %p');
		//var timeFormat = d3.time.format('%_I:%M:%S %p');
		return dayFormat(date) + ', ' + 
				dateFormat(date) + '<br>' + 
				timeFormat(date) + ', ' + date.toLocaleTimeString('en-us',{timeZoneName:'short'}).split(' ')[2] + '<br>' + 
				'Applicable: <b>' + numberWithCommas(applicable) + ' patches</b><br>' + 
				'Installed: <b>' + numberWithCommas(installed) + ' patches</b><br>' + 
				'Outstanding: <b>' + numberWithCommas(outstanding) + ' patches</b><br>' + 
				'Compliance: <b>' + temp + '%</b>';//.toFixed(1)
	}
	
	function closestMatch(number, array) {
		var midIndex;
		var lowIndex = 0;
		var highIndex = array.length - 1;
		
		while ((highIndex - lowIndex) > 1) {
			midIndex = Math.floor((lowIndex + highIndex) / 2);
			if (array[midIndex].getTime() < number.getTime()) {
				lowIndex = midIndex;
			}
			else {
				highIndex = midIndex;
			}
		}
		if ((number.getTime() - array[lowIndex].getTime()) < (array[highIndex].getTime() - number.getTime())) {
			return array[lowIndex];
		}
		return array[highIndex];
	}
	
	backRect.on('mousemove', function() {
		// Coords of mousemove event relative to the container div
		var coords = d3.mouse(container.node());
		
		// Value on the x scale corresponding to this location
		var xValMouse = xScale.invert(coords[0]);
		
		var xVal = closestMatch(xValMouse, dates);
		
		// Date object corresponding the this x value. Add 12 hours to
		// the value, so that each point occurs at midday on the given date.
		// This means date changes occur exactly halfway between points.
		// This is what we want - we want our tooltip to display data for the
		// closest point to the mouse cursor.
		var d = new Date(xVal.getTime());// + 3600000 * 12);
		
		// Format the date object as a date string matching our original data
		var date = timeFormat(d);
		
		// Find the index of this date in the array of original date strings
		var i = dateStrings.indexOf(date.toString());
		
		// Does this date exist in the original data?
		var dateExists = i > -1;

		// If not, hide the tooltip and return from this function
		if (!dateExists) {
			hideTooltip();
			return;
		}

		// If we are here, the date was found in the original data.
		// Proceed with displaying tooltip for of the i-th data point.

		// Get the i-th date value and temperature value.
		var _date = dates[i],
		    _temp = temps[i];

		// Update the position of the activePoint element
		activePoint
			.attr('cx', xScale(_date))
			.attr('cy', yScale(_temp))

		// Update tooltip content
		tt.html(tooltipFormatter(_date, _temp, chartData[i].applicable_1, chartData[i].installed_1, chartData[i].outstanding_1));

		// Get dimensions of tooltip element
		var dim = tt.node().getBoundingClientRect();

		// Update the position of the tooltip. By default, above and to the right
		// of the mouse cursor.
		var tt_top = yScale(_temp) - dim.height - 10, //coords[1] - dim.height - 10,
		    tt_left = xScale(_date) + 10; //coords[0] + 10;

		// If right edge of tooltip goes beyond chart container, force it to move
		// to the left of the mouse cursor.
		if (tt_left + dim.width > chartWidth)
			tt_left = xScale(_date) - dim.width - 10;//coords[0] - dim.width - 10;
		
		if (tt_top < 0)
			tt_top = yScale(_temp) + 10;
		
		tt
			.style('top', tt_top + 'px')
			.style('left', tt_left + 'px')
		
		// Show tooltip if it is not already visible
		if (tt.style('visibility') != 'visible')
			showTooltip();

	});
	
	// Add mouseout event handler
	backRect.on('mouseout', hideTooltip);
	
	// Append Zoom Slide Bar to the page
	var slider = d3.select(legendDivId).append("input")
		.datum({})
		.attr("type", "range")
		.attr("value", zoom.scaleExtent()[0])
		.attr("min", zoom.scaleExtent()[0])
		.attr("max", (zoom.scaleExtent()[1])) // - 50
		.attr("step", (zoom.scaleExtent()[1] - zoom.scaleExtent()[0]) / 10000)
		.on("input", function slided(d) {
			zoom.scaleTo(svg, d3.select(this).property("value"));
			/*
			svg.select('.zoom')
				.call(
					zoom.transform, d3.zoomIdentity
						.scale(width / (s[1] - s[0]))
						.translate(-s[0], 0)
				)
			*/
		})
	
	
		
	// Text for the title of the chart
	svg.append('text')
		.attr('class', 'title')
		.attr('x', chartAreaWidth / 2) // .attr('x', width / 2 + margin)
		 .attr('y', margin.top * (3/4) ) //.attr('y', chartAreaHeight/ 15) // .attr('y', margin / 2 )  .attr('y', margins.top / 2 )
		.attr('text-anchor', 'middle')
		.text(chartTitle)
		.style('font-size', 'calc(' + chartAreaWidth/400 + 'vw)')  // .style('font-size', (chartAreaWidth / 2 * 0.008) + "em") 
		//.style('font-size', ((width + height) / 2 * 0.004) + "em") // '22px'
		.style('font-weight', 600)
	
	// Text for the company watermark
	svg.append('text')
		.attr('class', 'source')
		.attr('x', chartAreaWidth - margin.right / 3) //  - margin / 2
		.attr('y', height + margin.top + margin.bottom * 0.70)
		.attr('text-anchor', 'end')
		.text('CAS Severn, ' + (new Date()).getFullYear())
		.style('font-size', (chartAreaWidth / 2 * 0.0018) + "em")
	
	// Axes Category Labels
	// Y - Axis
	svg.append('text')
		.attr('class', 'label')
		.attr('x', -(height / 2) - margin.top)// .attr('x', -(height / 2) - margin)
		.attr('y', margin.left / 2.4)  // .attr('y', margin / 2.4)
		.attr('transform', 'rotate(-90)')
		.attr('text-anchor', 'middle')
		.text(function() {
			return "Percent Compliance";
		//	if (reportType == "patchCompliance") {
		//		return 'Number of Patches';
		//	} 
		//	else if (reportType == "outstandingComplianceByContent") {
		//		return 'Number of Systems';
		//	}
		//	else {
		//		return 'Count';
		//	}
		}); //'Percent Compliance'
	
	// X - Axis
	svg.append('text')
		.attr('class', 'label')
		.attr('x', width / 2 + margin.left) // .attr('x', width / 2 + margin)
		.attr('y', height + margin.top + margin.bottom * 0.70) // .attr('y', height + margin * 1.7)
		.attr('text-anchor', 'middle')
		.text('Time')
		//.style('font-size', (width / 2 * 0.007) + "em") // '10px'
	
	d3.selectAll('.label')
		.style('font-size', ((width + height) / 2 * 0.0028) + "em") //'14px' 0.0038
		.style('font-weight', 'bold'); //400
	
	function numberWithCommas(x) {
		return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}	
}