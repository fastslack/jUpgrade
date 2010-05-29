/*
	Class:    	dwProgress bar
	Author:   	David Walsh
	Website:    http://davidwalsh.name
	Version:  	1.0
	Date:     	07/03/2008
	Built For:  MooTools 1.2.0

	SAMPLE USAGE AT BOTTOM OF THIS FILE

*/


//class is in
var dwProgressBar = new Class({

	//implements
	Implements: [Options],

	//options
	options: {
		container: $$('body')[0],
		boxID:'',
		percentageID:'',
		displayID:'',
		startPercentage: 0,
		displayText: false,
		speed:10
	},

	//initialization
	initialize: function(options) {
		//set options
		//this.setOptions(options);
		//create elements
		this.createElements();
	},

	//creates the box and percentage elements
	createElements: function() {
		var box = new Element('div', { id:this.options.boxID });
		var perc = new Element('div', { id:this.options.percentageID, 'style':'width:0px;' });
		perc.inject(box);
		box.inject(this.options.container);
		if(this.options.displayText) {
			var text = new Element('div', { id:this.options.displayID });
			text.inject(this.options.container);
		}
		this.set(this.options.startPercentage);
	},

	//calculates width in pixels from percentage
	calculate: function(percentage) {
		return ($(this.options.boxID).getStyle('width').replace('px','') * (percentage / 100)).toInt();
	},

	//animates the change in percentage
	animate: function(to) {
		$(this.options.percentageID).set('morph', { duration: this.options.speed, link:'cancel' }).morph({width:this.calculate(to.toInt())});
		if(this.options.displayText) {
			$(this.options.displayID).set('text', to.toInt() + '%');
		}
	},

	//sets the percentage from its current state to desired percentage
	set: function(to) {
		this.animate(to);
	}

});

/* sample usage */
/*
	//once the DOM is ready
	window.addEvent('domready', function() {

		//create the progress bar for example 1
		pb = new dwProgressBar({
			container: $('put-bar-here'),
			startPercentage: 25,
			speed:1000,
			boxID: 'box',
			percentageID: 'perc'
		});

		//movers
		$$('.mover').each(function(el) {
			el.addEvent('click',function() {
				pb.set(el.get('rel'));
			});
		});

*/

