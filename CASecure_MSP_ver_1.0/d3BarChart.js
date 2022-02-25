function d3BarChart(chartData, reportType, chartTitle, chartDivId, legendDivId, chartAreaWidth, chartAreaHeight, margin) {
	
	var margins = 
		{
			top: margin, 
			bottom: margin, 
			left: margin + (margin * 0.25), 
			right: margin - (margin * 0.5)
		};
	
	const svgLayout = d3.select(chartDivId)
		.style('text-align', 'center') //center
		.style("font-family", "'Open Sans', sans-serif");
	
	const svgContainer = svgLayout.append('div')
		.attr('id', '#container')
		.style('width', chartAreaWidth + 'px')
		.style('height', chartAreaHeight + 'px')
		//.style('margin', 'auto')
		.style('background-color', '#EBEDF2');
	
	const svg = svgContainer.append('svg')
		.style('width', '100%')
		.style('height', '100%');
	
	const width = chartAreaWidth - (margins.left + margins.right); //2 * margin;
	const height = chartAreaHeight - (margins.top + margins.bottom); //2 * margin;
	
	const chart = svg.append('g')
		.attr('transform', `translate(${margins.left}, ${margins.top})`); //`translate(${margin}, ${margin})`);
	
	d3.selectAll('path')
		.style('stroke', 'gray')
	
	d3.selectAll('line')
		.style('stroke', 'gray')
	
	const xScale = d3.scaleBand()
		.range([0, width])
		.domain(chartData.map((s) => s.category))
		.padding(0.4)
	
	const yScale = d3.scaleLinear()
		.range([height, 0])
		.domain([0, d3.max(chartData.map((s) => s.value))]) 
		.nice();
		//.domain([0, 500 * Math.ceil(d3.max(chartData.map((s) => s.value))/500)]);
		//.domain([0, 100]);
	
	// vertical grid lines
	// const makeXLines = () => d3.axisBottom()
	//   .scale(xScale)
	
	const makeYLines = () => d3.axisLeft()
		.scale(yScale)
	
	var xAxis = chart.append('g')
		.attr('transform', `translate(0, ${height})`)
		.call(d3.axisBottom(xScale))
		.attr('class', 'axis x-axis')
	
	var yAxis = chart.append('g')
		.call(d3.axisLeft(yScale))
		.attr('class', 'axis y-axis')
	
	d3.selectAll('.x-axis text')
		.each(function (d, i) {
			var tick = d3.select(this);
			tick.style('stroke', chartData[i].color);
		})
	//alert(xScale.bandwidth());
	
	//d3.selectAll('.y-axis text')
	//	.each(function (d, i) {
	//		var tick = d3.select(this);
	//		tick.text(tick.text() + '%');
	//	})
	
	d3.selectAll('.axis')
		.style('font-size', 'calc(' + xScale.bandwidth()/170 + 'vw)')
		//.style('font-size', '10px')
		.style('font-weight', 'bold');
	
	// vertical grid lines
	// chart.append('g')
	//   .attr('class', 'grid')
	//   .attr('transform', `translate(0, ${height})`)
	//   .call(makeXLines()
	//     .tickSize(-height, 0, 0)
	//     .tickFormat('')
	//   )
	
	chart.append('g')
		.attr('class', 'grid')
		.call(makeYLines()
			.tickSize(-width, 0, 0)
			.tickFormat('')
		)
	
	d3.selectAll('.grid path')
		.style('stroke-width', 0);
	
	d3.selectAll('.grid .tick line')
		.style('stroke', '#9FAAAE')
		.style('stroke-opacity', 0.3);
	
	function gradient (colour1, colour2, id, x1, y1, x2, y2, offset1, offset2, opacity1, opacity2) {
		svg.append('defs')
			.append('linearGradient')
				.attr("id", id)
				.attr("x1", x1)
				.attr("y1", y1)
				.attr("x2", x2)
				.attr("y2", y2);
		
		var idTag = '#' + id;
		
		d3.select(idTag)
			.append('stop')
				.attr('stop-color', colour1)
				.attr('class', 'begin')
				.attr('offset', offset1)
				.attr('stop-opacity', opacity1);
		
		d3.select(idTag)
			.append('stop')
				.attr('class', 'end')
				.attr('stop-color', colour2)
				.attr('offset', offset2)
				.attr('stop-opacity', opacity2)
	}
	
	const barGroups = chart.selectAll()
		.data(chartData)
		.enter()
		.append('g')
	
	var bars = barGroups
		.append('rect')
			.attr('class', 'bar')
			.attr('id', (g, i) => "bar" + i)
			.attr('x', (g) => xScale(g.category))
			.attr('y', (g) => height)
			.attr('height', 0)
			.attr('width', xScale.bandwidth())
			//.style('fill', (g) => g.color)
			.style('fill', function(g, i) {
				gradient('#ddd', g.color, 'grad' + i, '0%', '0%', '100%', '100%', '0%', '65%', 1, 1);
				return 'url(#grad' + i + ')';
			});
			
	var startAnimation = bars
		.transition()
			//.ease('elastic')
			.duration(500)
			.delay(function (d, i) {
				return i * 50;
			})
			.attr('y', (g) => yScale(g.value))
			//.attr('y', (g) => yScale(g.percent))
			.attr('height', (g) => height - yScale(g.value));
			//.attr('height', (g) => height - yScale(g.percent))
		
			
	// Sets text for the Percentages inside the bars
	barGroups 
		.append('text')
		.attr('class', 'value')
		.attr('x', (a) => xScale(a.category) + xScale.bandwidth() / 2)
		.attr('y', (a) => yScale(a.value) - 5) // + 30
		//.attr('y', (a) => yScale(a.percent) + 30)
		.attr('text-anchor', 'middle')
		.text((a) => `${a.percent}%`)
		.style('font-size', 'calc(' + xScale.bandwidth()/170 + 'vw)')
	
	// Size of percentages in the middle of the bars
	d3.selectAll('.value')
		.style('font-size', 'calc(' + xScale.bandwidth()/100 + 'vw)') // '14px'
		.style('font-weight', 'bold')
		.each(function (d, i) {
			var percent = d3.select(this);
			percent.style('stroke', chartData[i].color);
		})
	
	// Axes Category Labels
	// Y - Axis
	svg.append('text')
		.attr('class', 'label')
		.attr('x', -(height / 2) - margins.top)// .attr('x', -(height / 2) - margin)
		.attr('y', margins.left / 2.4)  // .attr('y', margin / 2.4)
		.attr('transform', 'rotate(-90)')
		.attr('text-anchor', 'middle')
		.text(function() {
			if (reportType == "microsoftPatchCompliance") {
				return 'Number of Patches';
			} 
			else if (reportType == "outstandingComplianceByContent") {
				return 'Number of Systems';
			}
			else {
				return 'Count';
			}
		}); //'Percent Compliance'
	
	// X - Axis
	svg.append('text')
		.attr('class', 'label')
		.attr('x', width / 2 + margins.left) // .attr('x', width / 2 + margin)
		.attr('y', height + margins.bottom * 1.7) // .attr('y', height + margin * 1.7)
		.attr('text-anchor', 'middle')
		.text('Categories')
		.style('font-size', (width / 2 * 0.01) + "em") // '10px'
	
	//
	d3.selectAll('.label')
		.style('font-size', ((width + height) / 2 * 0.0024) + "em") //'14px' 0.0038
		.style('font-weight', 'bold'); //400
	
	// Text for the title of the chart
	svg.append('text')
		.attr('class', 'title')
		.attr('x', chartAreaWidth / 2) // .attr('x', width / 2 + margin)
		 .attr('y', margins.top / 2 ) //.attr('y', chartAreaHeight/ 15) // .attr('y', margin / 2 )  .attr('y', margins.top / 2 )
		.attr('text-anchor', 'middle')
		.text(chartTitle)
		.style('font-size', 'calc(' + chartAreaWidth/400 + 'vw)')  // .style('font-size', (chartAreaWidth / 2 * 0.008) + "em") 
		//.style('font-size', ((width + height) / 2 * 0.004) + "em") // '22px'
		.style('font-weight', 600)
	
	// Text for the company watermark
	svg.append('text')
		.attr('class', 'source')
		.attr('x', chartAreaWidth - margins.right / 3) //  - margin / 2
		.attr('y', height + margins.bottom * 1.7)
		.attr('text-anchor', 'end')
		.text('CAS Severn, ' + (new Date()).getFullYear())
		.style('font-size', (chartAreaWidth / 2 * 0.002) + "em")
		//.style('font-size', ((width + height) / 2 * 0.0013) + "em") // '10px'
	
	// Prep the tooltip bits, initial display is hidden
	var tooltip = svg.append("g")
		.attr("class", "tooltip")
		.style("display", "none");
	
	tooltip.append("rect")
		.attr("width", 50)
		.attr("height", 20)
		.attr("fill", "white")
		.style("opacity", 0.8); //0.7
	
	tooltip.append("text")
		.attr("x", 25)
		.attr("dy", "1.2em")
		.style("text-anchor", "middle")
		.attr("font-size", "12px")
		.attr("font-weight", "bold");
	
	/* !!!!!!!!!!!!! Creation of Legend Tabel !!!!!!!!!!!!!!!!!!!!!!*/
	
	function tabulate(data, columns) {
		
		var table = d3.select(legendDivId).append("table").attr('id','legendTable'),
			thead = table.append("thead"),
			tbody = table.append("tbody");
		
		// append the header row
		thead.append("tr")
			.selectAll("th")
			.data(columns)
			.enter()
			.append("th")
			.text(function(column) { return column; });
		
		// create a row for each object in the data
		var rows = tbody.selectAll("tr")
			.data(data)
			.enter()
			.append("tr")
			.attr('id', (d, i) => 'row' + i )
			
			
			rows
			.on('mouseover', function(d, i) {
				d3.select('#row' + i)
					.style('background-color', '#f2f2f2')
					.style('cursor', 'pointer');
				
				d3.selectAll('.value')
					.attr('opacity', 0)
				
				d3.select('#bar' + i)
					.transition()
					.duration(300)
					//.attr('opacity', 0.6)
					.attr('x', (a) => xScale(a.category) - 10)
					.attr('width', xScale.bandwidth() + 20)
				
				chart.append('rect')
					.attr('id', 'outline')
					.attr('x', xScale(d.category))
					.attr('y', yScale(d.value))
					.attr('width', xScale.bandwidth())
					.attr('height', height - yScale(d.value))
					.transition()
					.duration(300)
						.attr('x', xScale(d.category) - 10)
						.attr('width', xScale.bandwidth() + 20)
						.style('stroke-width', 2)
						.style('stroke', '#FED966')
						.style('fill', 'none')
				
				const y = yScale(d.value)
				//const y = yScale(d.percent)
				
				line = chart.append('line')
					.attr('id', 'limit')
					.attr('x1', 0)
					.attr('y1', y)
					.attr('x2', width)
					.attr('y2', y) 
					.style('stroke', '#FED966')
					.style('stroke-width', 3)
					.style('stroke-dasharray', '3 6')
				
				barGroups.append('text')
					.attr('class', 'divergence')
					.attr('x', (a) => xScale(a.category) + xScale.bandwidth() / 2)
					.attr('y', (a) => yScale(a.value) - 5) // + 30
					//.attr('y', (a) => yScale(a.percent) + 30)
					.attr('fill', 'white')
					.attr('text-anchor', 'middle')
					.text((a, idx) => {
						const divergence = (a.percent - d.percent).toFixed(2)
						
						let text = ''
						if (divergence > 0) 
							text += '+'
						text += `${divergence}%`
						
						return idx !== i ? text : '';
					})
				
				d3.selectAll('.divergence')
					.style('font-size', 'calc(' + xScale.bandwidth()/100 + 'vw)') //'14px'
					.style('fill', '#2F4A6D')
					.style('font-weight', 'bold')
					//.each(function (d, i) {
					//	var percent = d3.select(this);
					//	percent.style('stroke', chartData[i].color);
					//});
				
			})
			.on('mouseout', function(d, i) {
				d3. select('#row' + i)
					.style('background-color', '#fafafa');
				
				d3.selectAll('.value')
					.attr('opacity', 1)
				
				d3.select('#bar' + i)
					.transition()
					.duration(300)
						.attr('opacity', 1)
						.attr('x', (a) => xScale(a.category))
						.attr('width', xScale.bandwidth())
				
				chart.selectAll('#outline').remove()
				chart.selectAll('#limit').remove()
				chart.selectAll('.divergence').remove()
				
			})
			//.on('', function(d, i) {
				
			//})
			
		// create a cell in each row for each column
		var cells = rows.selectAll("td")
			.data(function(row) {
				var rowColor = row.color;
				return columns.map(function(column) {
					return {column: column, value: row[column], color: rowColor};
				});
			})
			.enter()
			.append("td")
			.style("text-align", function(d, i) {
				if (d.column == "value" || d.column == "percent") {
					return "right";
				}
				else {
					return "left";
				}
			});
			/*
			if (d.column == "category") {
				cells.append("span")
				.style('display', 'inline-block')
				.style('width', '13px')
				.style('margin-bottom', '3px')
				.style('background-color', d.color);
			}
			*/
			cells.append("b")
			.append("span")
			.style("color", function(d, i) { return d.color; })
			.append('text')
			.append("tspan")
			.attr("y", "0em")
			.text(function(d) {
				if (d.column == "value") { 
					return (d.value).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
				}
				else if (d.column == "percent")
					return d.value + "%";
				//	return '<span style="display:inline-block; width:13px; margin-bottom:3px; background-color:' + d.color + ';">&nbsp;</span>&nbsp;&nbsp;' + d.value
				else
					return d.value; 
			})
			
			//.attr('class', 'dataRow');
			//.attr("fill", function(d) { return d.color; });
		
		//cells.selectAll("text")
		//	.attr("fill", function(d) { return d.color; });
			//.style("fill", function(d) { return d.color; });
		
		return table;
	}
			
	legendTable = tabulate(chartData, ["category", "value", "percent"]);
	
	if (reportType == "microsoftPatchCompliance") {
		legendTable.selectAll("thead th")
			.text(function(column) {
				if (column == "category")
					return "Category";
				else if (column == "value")
					return "Patches";
				else if (column == "percent")
					return "Compliance";
				//return column.charAt(0).toUpperCase() + column.substr(1);
			});
	}
	else if (reportType == "outstandingComplianceByContent") {
		legendTable.selectAll("thead th")
			.text(function(column) {
				if (column == "category")
					return "Category";
				else if (column == "value")
					return "Systems";
				else if (column == "percent")
					return "Compliance";
				//return column.charAt(0).toUpperCase() + column.substr(1);
			});
	}
	else {
		legendTable.selectAll("thead th")
			.text(function(column) {
				return column.charAt(0).toUpperCase() + column.substr(1);
			});
	}
	
	startAnimation.on('end', function () {
		bars
			.on('mouseenter', function (actual, i) {
				d3.selectAll('.value')
					.attr('opacity', 0)
				
				d3.select(this)
					.style('cursor', 'pointer')
					.transition()
					.duration(300)
						//.attr('opacity', 0.6)
						.attr('x', (a) => xScale(a.category) - 10)
						.attr('width', xScale.bandwidth() + 20)
				
				chart.append('rect')
					.attr('id', 'outline')
					.attr('x', xScale(actual.category))
					.attr('y', yScale(actual.value))
					.attr('width', xScale.bandwidth())
					.attr('height', height - yScale(actual.value))
					.style('stroke-width', 2)
					.style('stroke', '#FED966')
					.style('fill', 'none')
					.transition()
					.duration(300)
					.attr('x', xScale(actual.category) - 10)
					.attr('width', xScale.bandwidth() + 20)
				
				const y = yScale(actual.value)
				//const y = yScale(actual.percent)
				
				line = chart.append('line')
					.attr('id', 'limit')
					.attr('x1', 0)
					.attr('y1', y)
					.attr('x2', width)
					.attr('y2', y) 
					.style('stroke', '#FED966')
					.style('stroke-width', 3)
					.style('stroke-dasharray', '3 6')
				
				barGroups.append('text')
					.attr('class', 'divergence')
					.attr('x', (a) => xScale(a.category) + xScale.bandwidth() / 2)
					.attr('y', (a) => yScale(a.value) - 5) // + 30
					//.attr('y', (a) => yScale(a.percent) + 30)
					.attr('fill', 'white')
					.attr('text-anchor', 'middle')
					.text((a, idx) => {
						const divergence = (a.percent - actual.percent).toFixed(2)
						
						let text = ''
						if (divergence > 0) 
							text += '+'
						text += `${divergence}%`
						
						return idx !== i ? text : '';
					})
					
				d3.selectAll('.divergence')
					.style('font-size', 'calc(' + xScale.bandwidth()/100 + 'vw)') // '14px'
					.style('fill', '#2F4A6D')
					.style('font-weight', 'bold')
					
				d3.select('#row' + i)
					.style('background-color', '#f2f2f2')
					.style('cursor', 'pointer');
				
				tooltip.style("display", null);
			})
			.on('mouseleave', function () {
				d3.selectAll('.value')
					.attr('opacity', 1)
				
				d3.select(this)
					.transition()
					.duration(300)
						.attr('opacity', 1)
						.attr('x', (a) => xScale(a.category))
						.attr('width', xScale.bandwidth())
				
				chart.selectAll('#outline').remove()
				chart.selectAll('#limit').remove()
				chart.selectAll('.divergence').remove()
				
				tooltip.style("display", "none");
			})
			//.on("mouseover", function() { tooltip.style("display", null); })
			//.on("mouseout", function() { tooltip.style("display", "none"); })
			.on("mousemove", function(d) {
				var xPosition = d3.mouse(this)[0] + 70;
				var yPosition = d3.mouse(this)[1] + 40;
				tooltip.attr("transform", "translate(" + xPosition + "," + yPosition + ")");
				tooltip.select("text").text(d.percent + '%');
				//tooltip.html(
				//	'<b>' + d.percent + '%</b>' +
				//)
			});
			
		
	});		
		
}