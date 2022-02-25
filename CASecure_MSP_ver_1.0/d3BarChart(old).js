function d3BarChart(chartData, reportType, chartTitle, chartDivId, legendDivId, chartAreaWidth, chartAreaHeight, margin) {
	
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
	
	const width = chartAreaWidth - 2 * margin;
	const height = chartAreaHeight - 2 * margin;
	
	const chart = svg.append('g')
		.attr('transform', `translate(${margin}, ${margin})`);
	
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
		.domain([0, 100]);
	
	// vertical grid lines
	// const makeXLines = () => d3.axisBottom()
	//   .scale(xScale)
	
	const makeYLines = () => d3.axisLeft()
		.scale(yScale)
	
	chart.append('g')
		.attr('transform', `translate(0, ${height})`)
		.call(d3.axisBottom(xScale))
		.attr('class', 'axis')
		//.data(chartData)
		//.enter()
		//.style('color', )
	
	chart.append('g')
		.call(d3.axisLeft(yScale))
		.attr('class', 'axis');
	
	
	d3.selectAll('.axis')
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
	
	barGroups
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
			})
			.on('mouseenter', function (actual, i) {
				d3.selectAll('.value')
					.attr('opacity', 0)
				
				d3.select(this)
					.style('cursor', 'pointer')
					.transition()
					.duration(300)
						.attr('opacity', 0.6)
						.attr('x', (a) => xScale(a.category) - 5)
						.attr('width', xScale.bandwidth() + 10)
				
				const y = yScale(actual.percent)
				
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
					.attr('y', (a) => yScale(a.percent) + 30)
					.attr('fill', 'white')
					.attr('text-anchor', 'middle')
					.text((a, idx) => {
						const divergence = (a.percent - actual.percent).toFixed(1)
						
						let text = ''
						if (divergence > 0) 
							text += '+'
						text += `${divergence}%`
						
						return idx !== i ? text : '';
					})
					
					d3.selectAll('.divergence')
						.style('font-size', '14px')
						.style('fill', '#2F4A6D')
					
					d3.select('#row' + i)
						.style('background-color', '#f2f2f2')
						.style('cursor', 'pointer');
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
				
				chart.selectAll('#limit').remove()
				chart.selectAll('.divergence').remove()
			})
			.on("mouseover", function() { tooltip.style("display", null); })
			.on("mouseout", function() { tooltip.style("display", "none"); })
			.on("mousemove", function(d) {
				var xPosition = d3.mouse(this)[0] + 70;
				var yPosition = d3.mouse(this)[1] + 40;
				tooltip.attr("transform", "translate(" + xPosition + "," + yPosition + ")");
				tooltip.select("text").text(d.percent + '%');
			})
			.transition()
			//.ease('elastic')
			.duration(500)
			.delay(function (d, i) {
				return i * 50;
			})
			.attr('y', (g) => yScale(g.percent))
			.attr('height', (g) => height - yScale(g.percent))
			
	barGroups 
		.append('text')
		.attr('class', 'value')
		.attr('x', (a) => xScale(a.category) + xScale.bandwidth() / 2)
		.attr('y', (a) => yScale(a.percent) + 30)
		.attr('text-anchor', 'middle')
		.text((a) => `${a.percent}%`)
	
	d3.selectAll('.value')
		.style('font-size', '14px')
	
	svg.append('text')
		.attr('class', 'label')
		.attr('x', -(height / 2) - margin)
		.attr('y', margin / 2.4)
		.attr('transform', 'rotate(-90)')
		.attr('text-anchor', 'middle')
		.text('Percent Compliance')
	
	svg.append('text')
		.attr('class', 'label')
		.attr('x', width / 2 + margin)
		.attr('y', height + margin * 1.7)
		.attr('text-anchor', 'middle')
		.text('Categories')
	
	d3.selectAll('.label')
		.style('font-size', '14px')
		.style('font-weight', 400);
	
	svg.append('text')
		.attr('class', 'title')
		.attr('x', width / 2 + margin)
		.attr('y', 40)
		.attr('text-anchor', 'middle')
		.text(chartTitle)
		.style('font-size', '22px')
		.style('font-weight', 600)
	
	svg.append('text')
		.attr('class', 'source')
		.attr('x', width - margin / 2)
		.attr('y', height + margin * 1.7)
		.attr('text-anchor', 'start')
		.text('CAS Severn, 2019')
		.style('font-size', '10px')
	
	// Prep the tooltip bits, initial display is hidden
	var tooltip = svg.append("g")
		.attr("class", "tooltip")
		.style("display", "none");
	
	tooltip.append("rect")
		.attr("width", 50)
		.attr("height", 20)
		.attr("fill", "white")
		.style("opacity", 0.7);
	
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
			.on('mouseover', function(d, i) {
				d3.select(this)
					.style('background-color', '#f2f2f2')
					.style('cursor', 'pointer');
				
				d3.selectAll('.value')
					.attr('opacity', 0)
				
				d3.select('#bar' + i)
					.transition()
					.duration(300)
						.attr('opacity', 0.6)
						.attr('x', (a) => xScale(a.category) - 5)
						.attr('width', xScale.bandwidth() + 10)
				
				const y = yScale(d.percent)
		
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
					.attr('y', (a) => yScale(a.percent) + 30)
					.attr('fill', 'white')
					.attr('text-anchor', 'middle')
					.text((a, idx) => {
						const divergence = (a.percent - d.percent).toFixed(1)
							
						let text = ''
						if (divergence > 0) 
							text += '+'
						text += `${divergence}%`
							
						return idx !== i ? text : '';
					})
				
				d3.selectAll('.divergence')
					.style('font-size', '14px')
					.style('fill', '#2F4A6D')
					
			})
			.on('mouseout', function(d, i) {
				d3. select(this)
					.style('background-color', '#fafafa');
				
				d3.selectAll('.value')
					.attr('opacity', 1)
				
				d3.select('#bar' + i)
					.transition()
					.duration(300)
						.attr('opacity', 1)
						.attr('x', (a) => xScale(a.category))
						.attr('width', xScale.bandwidth())
				
				chart.selectAll('#limit').remove()
				chart.selectAll('.divergence').remove()
				
			})
			.on('', function(d, i) {
				
			})
		
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
			.append("b")
			.append("span")
			.style("color", function(d, i) { return d.color; })
			.append('text')
			.append("tspan")
			.attr("y", "0em")
			.text(function(d) {
				if (d.column == "percent")
					return d.value + "%";
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
		
}