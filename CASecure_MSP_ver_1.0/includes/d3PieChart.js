function d3PieChart(data, reportType, chartTitle, chartDivId, legendDivId, chartAreaWidth, chartAreaHeight, margin) {
	
	var width = chartAreaWidth - 2 * margin; //960; // 300
	var height = chartAreaHeight - 2 * margin;//500; // 200
	var radius = Math.min(width, height) / 2 - 50
	
	var svgLayout = d3.select(chartDivId)
		.style('text-align', 'center') //center
		.style("font-family", "'Open Sans', sans-serif");
	
	var svgContainer = svgLayout.append('div')//d3.select('#container')
		.attr('id', '#container')
		.style('width', chartAreaWidth)
		.style('height', chartAreaHeight)
		//.style('margin', 'auto')
		.style('background-color', '#EBEDF2');
	
	var svg = svgContainer.append('svg')
		.attr("width", width)
		.attr("height", height)
		.style('width', '100%')
		.style('height', '100%');
	
	var g = svg.append("g")
		.attr("transform", "translate(" + width / 2 + "," + (height + 100) / 2 + ")");
	
	// Generate the pie
	var pie = d3.pie()
		.value(function(d) {
			return d.value;
		})
		.sort(null);
	
	// Generate the arcs
	var arc = d3.arc()
		.innerRadius(0)
		.outerRadius(radius)
		.padAngle(.02)
		.padRadius(10)
		.cornerRadius(6);
	
	//Generate groups
	var arcGroups = g.selectAll(".arc")
		.data(pie(data))
		.enter()
		.append("g")
		.attr("class", "arc")
		.attr("id", (d, i) => "pie" + i)
	
	//Generate Chart Text
	var textCategory = arcGroups.append('svg:text')
		.attr('transform', function(d) { 
			var c = arc.centroid(d), 
				x = c[0], 
				y = c[1], 
				h = Math.sqrt(x*x + y*y);
			return 'translate(' + (x/h * (radius + 30)) + ',' + (y/h * (radius + 20)) + ')';
		})
		.attr('text-anchor', function(d) {
			return (d.endAngle + d.startAngle)/2 > Math.PI ? "end" : "start";
		})
		.attr('fill', function(d, i) { return d.data.color; })
		.text(function(d, i) { return d.data.category; })
		.style('display', 'none')
		.attr('class', 'pieText')
		.attr('id', function(d,i) { return 'text' + i; })
	
	
	//Draw arc paths
	arcGroups
		.append("path")
			.attr("d", function(d,i) {
				return arc(d);
			})
			//.attr("d", arc())
			.style("fill", function(d, i) {
				gradient('#ddd', d.data.color, 'grad' + i, '0%', '0%', '100%', '100%', '0%', '45%', 1, 1);
				return 'url(#grad' + i + ')';
				//return d.data.color;
			})
			//.style('stroke', (d) => 'black';)
			.on( 'mouseenter', function (actual, i) {
				d3.select('#pie' + i) //this
					.style('cursor', 'pointer')
					.transition()
					.duration(300)
					.attr('transform', explode);
				
				d3.select('#text' + i)
					.style('display', 'block')
			})
			.on( 'mouseleave', function (actual, i) {
				d3.select('#pie' + i) //this
					.style('cursor', 'pointer')
					.transition()
					.duration(300)
					.attr('transform', implode);
				
				d3.selectAll('.pieText')
					.style('display', 'none')
			})
			.transition()
			.delay(function(d,i) {
				return i * 500; 
			})
			.duration(500)
			.attrTween('d', function(d) {
				var i = d3.interpolate(d.startAngle+0.1, d.endAngle);
				return function(t) {
					d.endAngle = i(t); 
					return arc(d)
				}
			}) ;
	
	svg.append('text')
		.attr('class', 'title')
		.attr('x', width / 2)
		.attr('y', 40)
		.attr('text-anchor', 'middle')
		.text(chartTitle)
			.style('font-size', '22px')
			.style('font-weight', 600);
	
	svg.append('text')
		.attr('class', 'source')
		.attr('x', width * 0.9 - margin)//width - 160)
		.attr('y', height + margin * 0.75)//height + 68)
		.attr('text-anchor', 'start')
		.text('CAS Severn, 2019')
			.style('font-size', '10px')
	
	function explode(d, index) {
		var offset = 15;
		var angle = (d.startAngle + d.endAngle) / 2;
		var xOff = Math.sin(angle) * offset;
		var yOff = -Math.cos(angle) * offset;
		return 'translate(' + xOff + ',' + yOff + ')';
	}
	
	function implode(d, index) {
		var offset = 0;
		var angle = (d.startAngle + d.endAngle) / 2;
		var xOff = Math.sin(angle) * offset;
		var yOff = -Math.cos(angle) * offset;
		return 'translate(' + xOff + ',' + yOff + ')';
	}
	
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
				
				d3.select('#pie' + i)
					.style('cursor', 'pointer')
					.transition()
					.duration(300)
					.attr('transform', explode);
				
				d3.select('#text' + i)
					.style('display', 'block')
			})
			.on('mouseout', function(d, i) {
				d3. select(this)
					.style('background-color', '#fafafa');
				
				d3.select('#pie' + i)
					.style('cursor', 'pointer')
					.transition()
					.duration(300)
					.attr('transform', implode);
				
				d3.selectAll('.pieText')
					.style('display', 'none')
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
			.text(function(d) {
				if (d.column == "percent")
					return d.value + "%";
				else
					return d.value; 
			})
			//.attr('class', 'dataRow');
			.attr("fill", function(d) { return d.color; });
		
		//cells.selectAll("text")
		//	.attr("fill", function(d) { return d.color; });
			//.style("fill", function(d) { return d.color; });
		//
		return table;
	}
	
	legendTable = tabulate(data, ["category", "value", "percent"]);
	
	if (reportType == 'sourceSeverity') {
		legendTable.selectAll("thead th")
			.text(function(column) {
				if (column == "category")
					return "Severity";
				else if (column == "value")
					return "Patches";
				else if (column == "percent")
					return "Percentage";
			});
	}
	else if (reportType == 'windowsUpdateStatus' || reportType == 'systemFreeSpace') {
		legendTable.selectAll("thead th")
			.text(function(column) {
				if (column == "category")
					return "Category";
				else if (column == "value")
					return "Systems";
				else if (column == "percent")
					return "Compliance";
			});
	}
	else if (reportType == 'severeVulnerability') {
		legendTable.selectAll("thead th")
			.text(function(column) {
				if (column == "category")
					return "Category";
				else if (column == "value")
					return "Patches";
				else if (column == "percent")
					return "Compliance";
			});
	}
	else if (reportType == 'microsoftOfficeVersion') {
		legendTable.selectAll("thead th")
			.text(function(column) {
				if (column == "category")
					return "Software";
				else if (column == "value")
					return "Systems";
				else if (column == "percent")
					return "Percent";
			});
	}
	else if (reportType == 'operatingSystems') {
		legendTable.selectAll("thead th")
			.text(function(column) {
				if (column == "category")
					return "Operating System";
				else if (column == "value")
					return "Systems";
				else if (column == "percent")
					return "Percent";
			});
	}
	else {
		legendTable.selectAll("thead th")
			.text(function(column) {
				return column.charAt(0).toUpperCase() + column.substr(1);
			});
	}
}