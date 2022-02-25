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
	this.colors = [];
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
			gradfill.addColorStop(1, this.colors[i]); // this.barcol2
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
			c2d.fillStyle = this.colors[i];
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