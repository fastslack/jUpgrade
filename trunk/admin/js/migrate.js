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


var jUpgrade = new Class({

  Implements: [Options, Events],

  options: {
    mode: 1,
    directory: 'jupgrade',
    prefix_old: 'jos_',
    prefix_new: 'j17_',
    skip_checks: 0,
    skip_download: 0,
    skip_decompress: 0,
    positions: 0,
    debug: 0
  },

	initialize: function(options) {
		var self = this;

		this.setOptions(options);

		$('checks').setStyle('display', 'none');
		$('download').setStyle('display', 'none');
		$('decompress').setStyle('display', 'none');
		$('install').setStyle('display', 'none');
		$('migration').setStyle('display', 'none');
		$('templates').setStyle('display', 'none');
		$('extensions').setStyle('display', 'none');
		$('done').setStyle('display', 'none');

		$('update').addEvent('click', function(e) {
				self.checks(e);
		});

	},

	/**
	 * Fix needed!! Internal function to get jUpgrade settings
	 *
	 * @return	bool
	 * @since	1.2.0
	 */
	updateSettings: function(e) {
		var request = new Request({
			url: 'index.php?option=com_jupgrade&format=raw&controller=ajax&task=getParams',
			method: 'get',
			noCache: true,
			onComplete: function(response) {
				var object = JSON.decode(response);
				this.options.directory = object.directory;
			}
		}).send();
	},

	/**
	 * Run the checks
	 *
	 * @return	bool
	 * @since	1.2.0
	 */
	checks: function(e) {
		var self = this;

		// this/e.target is current target element.
		if (e.stopPropagation) {
			e.stopPropagation(); // Stops some browsers from redirecting.
		}

		var mySlideUpdate = new Fx.Slide('update');
		mySlideUpdate.toggle();

		// Check skip from settings
		if (self.options.skip_checks != 1) {

			var mySlideChecks = new Fx.Slide('checks');
			mySlideChecks.hide();
			$('checks').setStyle('display', 'block');
			mySlideChecks.toggle();

			var pb0 = new dwProgressBar({
				container: $('pb0'),
				startPercentage: 50,
				speed: 1000,
				boxID: 'pb0-box',
				percentageID: 'pb0-perc',
				displayID: 'text',
				displayText: false
			});

			text = document.getElementById('checkstatus');
			text.innerHTML = 'Checking...';

			var c = new Ajax( 'index.php?option=com_jupgrade&format=raw&controller=ajax&task=checks', {
				method: 'get',
				onComplete: function( response ) {
					//alert('>>'+response+'<<');

					if (response != 'OK') {
						pb0.set(100);
						pb0.finish();
						text.innerHTML = '<span id="checktext">'+response+'</span>';
					}else{
						pb0.set(100);
						pb0.finish();
						text.innerHTML = 'Checking DONE';
						self.download(e);
					}
				}
			}).request();
		}else{
			self.download(e);
		}

	}, // end function


	/**
	 * Run the checks
	 *
	 * @return	bool
	 * @since	1.2.0
	 */
	progress: function(e) {

		var a = new Ajax( 'index.php?option=com_jupgrade&format=raw&controller=ajax&task=getfilesize', {
		  method: 'get',
			noCache: true,
		  onComplete: function( msg ) {
					var self = this;

					var ex = self.__explode(',', msg);

		      var currBytes = document.getElementById('currBytes');
		      var totalBytes = document.getElementById('totalBytes');
		      currBytes.innerHTML = ex[1];
		      totalBytes.innerHTML = ex[2];

					if(ex[1] < ex[2]){
		        pb1.set(ex[0].toInt());
					}else if(ex[1] == ex[2]){
		        pb1.set(ex[0].toInt());
		        //$clear(progressID);
						return false;
					}
		  }
		}).request();

	}, // end function

	/**
	 * Run the checks
	 *
	 * @return	bool
	 * @since	1.2.0
	 */
	download: function(e) {
		var self = this;
	
		// this/e.target is current target element.
		if (e.stopPropagation) {
			e.stopPropagation(); // Stops some browsers from redirecting.
		}

		var mySlideDownload = new Fx.Slide('download');
		mySlideDownload.hide();
		$('download').setStyle('display', 'block');
		mySlideDownload.toggle();

		pb1 = new dwProgressBar({
			container: $('pb1'),
			startPercentage: 1,
			speed: 1000,
			boxID: 'pb1-box',
			percentageID: 'pb1-perc',
			displayID: 'text',
			displayText: false
		});

		text = document.getElementById('downloadstatus');

		if (self.options.skip_download == 1) {
			if (self.options.skip_decompress == 1) {
				self.install();
			}else{
				self.decompress();
			}
		}else{
			var a = new Ajax( 'index.php?option=com_jupgrade&format=raw&controller=ajax&task=download', {
				method: 'get',
				onRequest: function( response ) {
					//alert(response);
				  progressID = self.progress.periodical(100);
				},
				onComplete: function( response ) {
					//alert(response);
					pb1.finish();

					// Shutdown periodical
					$clear(progressID);

					if (response == 1) {

						if (self.options.skip_decompress == 1) {
							self.install();
						}else{
							self.decompress();
						}
					}else if (response == 0){
						text.innerHTML = '<span id="checktext">Error: zip file was not successfully downloaded</span>';
					}

				}
			}).request();
		}

	}, // end function


	/**
	 * Run the decompress
	 *
	 * @return	bool
	 * @since	1.2.0
	 */
	decompress: function(e) {
		var self = this;

		var mySlideDecompress = new Fx.Slide('decompress');
		mySlideDecompress.hide();
		$('decompress').setStyle('display', 'block');
		mySlideDecompress.toggle();

		var pb2 = new dwProgressBar({
			container: $('pb2'),
			startPercentage: 50,
			speed: 1000,
			boxID: 'pb2-box',
			percentageID: 'pb2-perc',
			displayID: 'text',
			displayText: false
		});

		text = document.getElementById('decompressstatus');

		if (self.options.skip_decompress == 1) {
			pb2.set(100);
			pb2.finish();
			self.install();
		}else{
			var d = new Ajax( 'index.php?option=com_jupgrade&format=raw&controller=ajax&task=decompress', {
				method: 'get',
				onComplete: function( response ) {

					if (self.options.debug == 1) {
						text = document.getElementById('debug');
						text.innerHTML = text.innerHTML + '<br><br>==========<br><b>[decompress]</b><br><br>' +response;
					}

					pb2.set(100);
					pb2.finish();

					if (response == 1) {
						self.install();
					}else if (response == 0){
						text.innerHTML = '<span id="checktext">Error: zip file not found</span>';
					}

				}
			}).request();
		}

	}, // end function


	/**
	 * Run the install
	 *
	 * @return	bool
	 * @since	1.2.0
	 */
	install: function(e) {
		var self = this;

		var mySlideInstall = new Fx.Slide('install');
		mySlideInstall.hide();
		$('install').setStyle('display', 'block');
		mySlideInstall.toggle();

		var pb3 = new dwProgressBar({
			container: $('pb3'),
			startPercentage: 2,
			speed: 1000,
			boxID: 'pb3-box',
			percentageID: 'pb3-perc',
			displayID: 'text',
			displayText: false
		});

		var d = new Ajax( 'components/com_jupgrade/includes/install_config.php', {
		  method: 'get',
			noCache: true,
		  onComplete: function( response ) {
				pb3.set(33);

				if (self.options.debug == 1) {
					text = document.getElementById('debug');
					text.innerHTML = text.innerHTML + '<br><br>==========<br><b>[install_config]</b><br><br>' +response;
				}

				var d2 = new Ajax( 'index.php?option=com_jupgrade&format=raw&controller=ajax&task=cleanup', {
					method: 'get',
					noCache: true,
					onComplete: function( response ) {
						pb3.set(66);

						if (self.options.debug == 1) {
							text = document.getElementById('debug');
							text.innerHTML = text.innerHTML + '<br><br>==========<br><b>[cleanup]</b><br><br>' +response;
						}

						var d = new Ajax( 'components/com_jupgrade/includes/install_db.php', {
							method: 'get',
							noCache: true,
							onComplete: function( response ) {
								pb3.set(100);
								pb3.finish();

								if (self.options.debug == 1) {
									text = document.getElementById('debug');
									text.innerHTML = text.innerHTML + '<br><br>==========<br><b>[install_db]</b><br><br>' +response;
								}

								self.migrate();
							}
						}).request();

					}
				}).request();

		  }
		}).request();

	}, // end function

	/**
	 * Run the migration
	 *
	 * @return	bool
	 * @since	1.2.0
	 */
	migrate: function(e) {
		var self = this;

		var request = new Request({
			url: 'components/com_jupgrade/includes/controller.php',
			method: 'get',
			noCache: true,
			onComplete: function(response) {

				var ex = self.__explode(';|;', response);
				var msg = ex[0];
				var id = ex[1];
				var file = ex[2];

				pb4.set(id*11);
				text = document.getElementById('status');
				text.innerHTML = 'Migrating ' + file;

				if (self.options.debug == 1) {
					text = document.getElementById('debug');
					text.innerHTML = text.innerHTML + '<br><br>==========<br><b>['+id+'] ['+file+']</b><br><br>' +msg;
				}

				if (id >= 9 || id == '') {
					pb4.finish();

					// Shutdown periodical
					$clear(migration_periodical);

					// Run templates step
					self.templates();
				}
			}
		});

		var runMigration = function() {
			request.send();
		};

		var mySlideMigrate = new Fx.Slide('migration');
		mySlideMigrate.hide();
		$('migration').setStyle('display', 'block');
		mySlideMigrate.toggle();

		pb4 = new dwProgressBar({
			container: $('pb4'),
			startPercentage: 5,
			speed: 1000,
			boxID: 'pb4-box',
			percentageID: 'pb4-perc',
			displayID: 'text',
			displayText: false
		});

		migration_periodical = runMigration.periodical(2500);

	}, // end function

	/**
	 * Run the templates
	 *
	 * @return	bool
	 * @since	1.2.0
	 */
	templates: function(e) {
		var self = this;

		var mySlideTem = new Fx.Slide('templates');
		mySlideTem.hide();
		$('templates').setStyle('display', 'block');
		mySlideTem.toggle();

		var pb5 = new dwProgressBar({
			container: $('pb5'),
			startPercentage: 10,
			speed: 1000,
			boxID: 'pb5-box',
			percentageID: 'pb5-perc',
			displayID: 'text',
			displayText: false
		});

		var d = new Ajax( 'components/com_jupgrade/includes/templates_db.php', {
		  method: 'get',
		  onComplete: function( response ) {
		    //alert(response);
				pb5.set(50);

				var d2 = new Ajax( 'components/com_jupgrade/includes/templates_files.php', {
					method: 'get',
					onComplete: function( response ) {
						//alert(response);
						pb5.set(100);
						pb5.finish();
						self.extensions();
					}
				}).request();

		  }
		}).request();

	}, // end function

	/**
	 * Run the extensions
	 *
	 * @return	bool
	 * @since	1.2.0
	 */
	extensions: function(e) {
		var self = this;

		var ext_request = new Request({
			url: 'components/com_jupgrade/includes/extensions_controller.php',
			method: 'get',
			noCache: true,
			onComplete: function(response) {
				var ex = self.__explode(';|;', response);
				var msg = ex[0];
				var id = ex[1];
				var file = ex[2];
				var lastid = ex[3];

				pb6.set(100);
				text = document.getElementById('status_ext');
				text.innerHTML = 'Migrating ' + file;

				if (self.options.debug == 1) {
					text = document.getElementById('debug');
					text.innerHTML = text.innerHTML + '<br><br>==========<br><b>['+id+'] ['+file+']</b><br><br>';
				}

				if (id == lastid) {
					pb6.finish();

					// Shutdown periodical
					$clear(extension_periodical);

					// Run templates step
					self.done();
				}
			}
		});

		var runExtensionsMigration = function() {
			ext_request.send();
		};

		var mySlideExt = new Fx.Slide('extensions');
		mySlideExt.hide();
		$('extensions').setStyle('display', 'block');
		mySlideExt.toggle();

		pb6 = new dwProgressBar({
			container: $('pb6'),
			startPercentage: 50,
			speed: 1000,
			boxID: 'pb6-box',
			percentageID: 'pb6-perc',
			displayID: 'text',
			displayText: false
		});

		extension_periodical = runExtensionsMigration.periodical(5500);

	}, // end function

	/**
	 * Run the done
	 *
	 * @return	bool
	 * @since	1.2.0
	 */
	done: function(e) {
		var self = this;

		var d = new Ajax( 'index.php?option=com_jupgrade&format=raw&controller=ajax&task=done', {
		  method: 'get',
		  onComplete: function( response ) {
				var mySlideDone = new Fx.Slide('done');
				mySlideDone.hide();
				$('done').setStyle('display', 'block');
				mySlideDone.toggle();
		  }
		}).request();

	}, // end function

	/**
	 * Internal function to do the explode
	 *
	 * @return	bool
	 * @since	1.2.0
	 */
	__explode: function (delimiter, string, limit) {

   var emptyArray = { 0: '' };
     
    // third argument is not required
    if ( arguments.length < 2 ||
        typeof arguments[0] == 'undefined' ||
        typeof arguments[1] == 'undefined' )
    {
        return null;
    }
  
    if ( delimiter === '' ||
        delimiter === false ||
        delimiter === null )
    {
        return false;
    }
  
    if ( typeof delimiter == 'function' ||
        typeof delimiter == 'object' ||
        typeof string == 'function' ||
        typeof string == 'object' )
    {
        return emptyArray;
    }
  
    if ( delimiter === true ) {
        delimiter = '1';
    }
     
    if (!limit) {
        return string.toString().split(delimiter.toString());
    } else {
        // support for limit argument
        var splitted = string.toString().split(delimiter.toString());
        var partA = splitted.splice(0, limit - 1);
        var partB = splitted.join(delimiter.toString());
        partA.push(partB);
        return partA;
    }

	} // end function


});










