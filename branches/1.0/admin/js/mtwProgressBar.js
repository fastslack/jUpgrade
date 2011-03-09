/**
 * jUpgrade
 *
 * @version			$Id$
 * @package			MatWare
 * @subpackage	com_jupgrade
 * @author      Matias Aguirre <maguirre@matware.com.ar>
 * @link        http://www.matware.com.ar
 * @license			GNU General Public License version 2 or later; see LICENSE.txt
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

	initialize: function(name) {
		//set options
		//this.setOptions(options);
		//create elements
		this.name = name;

		this.createElements();
	},

	//creates the box and percentage elements
	createElements: function() {

    var container = document.getElementById(this.name);
		//alert(div);
		var box = new Element('div', { id: this.name+'-box' });
		var perc = new Element('div', { id: this.name+'-perc', 'style':'width:5px;' });
		perc.injectInside(box);

		box.injectInside(container);
		//this.set(50);
	},

	//calculates width in pixels from percentage

	calculate: function(percentage) {
    //var width = $('box').getStyle('width');
    //alert(width);
		return ($(this.name+'-box').getStyle('width').replace('px','') * (percentage / 100)).toInt();
	},

	//animates the change in percentage
	animate: function(to) {
		//$('perc').set('morph', { duration: 100, link:'cancel' }).morph({width:this.calculate(to.toInt())});
    //$('perc').setStyle('clip','rect(0,'+to+'px, 150px,0)');
    //this.calculate(to.toInt());
    $(this.name+'-perc').setStyle('width', this.calculate(to.toInt()));
	},

	finish: function() {
    $(this.name+'-perc').setStyle('background-image', 'url(components/com_jupgrade/images/progress-bar-finish.png)');
	},

	//sets the percentage from its current state to desired percentage
	set: function(to) {
    //alert(to);
    //$('perc').setStyle('clip','rect(0,'+to+'px, 100px,0)');
		this.animate(to);
	}


});

