/**
 * jUpgrade
 *
 * @author      Matias Aguirre
 * @email       maguirre@matware.com.ar
 * @url         http://www.matware.com.ar
 * @license     GNU/GPL
 */

var mtwProgressBar = new Class({

	options: {
		container: $('pb'),
		boxID:'',
		percentageID:'',
		displayID:'',
		startPercentage: 0,
		displayText: false,
		speed:10
	},

	initialize: function(options) {
		//set options
		//this.setOptions(options);
		//create elements
		this.createElements();
	},

	//creates the box and percentage elements
	createElements: function() {

    var container = document.getElementById('pb1');


//alert(div);

		var box = new Element('div', { id:'box' });
		var perc = new Element('div', { id:'perc', 'style':'width:50px;' });
		perc.injectInside(box);

		box.injectInside(container);

		//this.set(50);
	},

	//calculates width in pixels from percentage

	calculate: function(percentage) {
    //var width = $('box').getStyle('width');
    //alert(width);
		return ($('box').getStyle('width').replace('px','') * (percentage / 100)).toInt();
	},

	//animates the change in percentage
	animate: function(to) {
		//$('perc').set('morph', { duration: 100, link:'cancel' }).morph({width:this.calculate(to.toInt())});
    //$('perc').setStyle('clip','rect(0,'+to+'px, 150px,0)');
    //this.calculate(to.toInt());
    $('perc').setStyle('width', this.calculate(to.toInt()));
	},

	//sets the percentage from its current state to desired percentage
	set: function(to) {
    //alert(to);
    //$('perc').setStyle('clip','rect(0,'+to+'px, 100px,0)');
		this.animate(to);
	}



});

