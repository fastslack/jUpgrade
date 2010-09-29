/**
 * jUpgrade
 *
 * @author      Matias Aguirre
 * @email       maguirre@matware.com.ar
 * @url         http://www.matware.com.ar
 * @license     GNU/GPL
 */

migrate_global = 0;

steps = new Array();
steps[0] = "users";
steps[1] = "modules";
steps[2] = "categories";
steps[3] = "content";
steps[4] = "menus";
steps[5] = "banners";
steps[6] = "contacts";
steps[7] = "newsfeeds";
steps[8] = "polls";
steps[9] = "weblinks";


var progress = function(event)  {

  var a = new Ajax( 'components/com_jupgrade/includes/getfilesize.php', {
    method: 'get',
    onComplete: function( msg ) {
        //alert(msg);
				var ex = explode(',', msg);

        currBytes = document.getElementById('currBytes');
        totalBytes = document.getElementById('totalBytes');
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

};

function download(event){

  var mySlideUpdate = new Fx.Slide('update');
  mySlideUpdate.toggle();

  var mySlideDownload = new Fx.Slide('download');
  mySlideDownload.hide();
  $('download').setStyle('display', 'block');
  mySlideDownload.toggle();

	pb1 = new mtwProgressBar('pb1');

	//install();

  var a = new Ajax( 'components/com_jupgrade/includes/download.php', {
    method: 'get',
    onRequest: function( response ) {	
			//alert(response);		
      var progressID = progress.periodical(100);
    },
    onComplete: function( response ) {
			//alert(response);
      //alert('finish');
			pb1.finish();
      decompress();
    }
  }).request();

};


function decompress(event){

  var mySlideDecompress = new Fx.Slide('decompress');
  mySlideDecompress.hide();
  $('decompress').setStyle('display', 'block');
  mySlideDecompress.toggle();

	pb2 = new mtwProgressBar('pb2');
	pb2.set(50);

	//install();

  var d = new Ajax( 'components/com_jupgrade/includes/decompress.php', {
    method: 'get',
    onComplete: function( response ) {
      //alert(response);
			pb2.set(100);
			pb2.finish();
      install();
    }
  }).request();

};

function install(event){

  var mySlideInstall = new Fx.Slide('install');
  mySlideInstall.hide();
  $('install').setStyle('display', 'block');
  mySlideInstall.toggle();

	pb3 = new mtwProgressBar('pb3');
	pb3.set(2);

  var d = new Ajax( 'components/com_jupgrade/includes/install_config.php', {
    method: 'get',
    onComplete: function( response ) {
     // alert(response);
			pb3.set(50);

			var d2 = new Ajax( 'components/com_jupgrade/includes/install_db.php', {
				method: 'get',
				onComplete: function( response ) {
				  //alert(response);
					pb3.set(100);
					pb3.finish();
					migrate();
				}
			}).request();

    }
  }).request();

};


function migrate(event){

  var mySlideMigrate = new Fx.Slide('migration');
  mySlideMigrate.hide();
  $('migration').setStyle('display', 'block');
  mySlideMigrate.toggle();

	pb4 = new mtwProgressBar('pb4');

	periodical = __doMigration2.periodical(3000)

};


var changeText = function(msg) {

	pb4.set(migrate_global*11);
	text = document.getElementById('status');
	text.innerHTML = 'Migrating ' + file;
	migrate_global = migrate_global+1;
}


var __doMigration2 = function(event)  {

	file = steps[migrate_global];
	//alert('INIT==> '+migrate_global+' <=> '+file);

	var myXHR = new XHR({  
		method: 'get',
		//async: false,
		onSuccess: changeText
	}).send('components/com_jupgrade/includes/migrate_'+file+'.php', null);  

	if (migrate_global == 9) {
		pb4.finish();

		var mySlideDone = new Fx.Slide('done');
		mySlideDone.hide();
		$('done').setStyle('display', 'block');
		mySlideDone.toggle();

		$clear(periodical);
	}

};



/*
 * OLD !!
 */
function __doMigration_debug(name, percent){

  var d = new Ajax( 'components/com_jupgrade/includes/migrate_'+name+'.php', {
    method: 'get',
		//async: true,
    onComplete: function( response ) {
      alert(response);
			pb4.set(percent);
			text = document.getElementById('status');
			text.innerHTML = 'Migrating '+name+'...';
			
    }
  }).request();

}

function __doMigration(name, percent){

/*
 BUG: cannot set async to false.
*/

	//alert(name);
  var d = new Ajax( 'components/com_jupgrade/includes/migrate_'+name+'.php', {
    method: 'get',
		//async: false,
    onComplete: function( response ) {
      alert(name +' '+ response);
			//pb4.set(percent);
			//text = document.getElementById('status');
			//text.innerHTML = 'Migrating '+name+'...';
			
    }
  }).request();


/*
	var myXHR = new XHR({  
		method: 'get',
		//async: false
	}).send('components/com_jupgrade/includes/migrate_'+name+'.php', null);  

	//alert(percent);
	pb4.set(percent);
	text = document.getElementById('status');
	text.innerHTML = 'Migrating '+name+'...';

	//alert(name);

  var d = new Ajax( 'components/com_jupgrade/includes/migrate_'+file+'.php', {
    method: 'get',
		//async: false,
    onComplete: changeText
  }).request();

  var d = new Ajax( 'components/com_jupgrade/includes/migrate_'+name+'.php', {
    method: 'get',
		//async: false,
    onComplete: function( response ) {
      alert(percent);
			pb4.set(percent);
			text = document.getElementById('status');
			text.innerHTML = 'Migrating '+name+'...';
			
    }
  }).request();

	//alert(d);

	var myRequest = new Request({
		method: 'get', 
		url: 'components/com_jupgrade/includes/migrate_'+name+'.php',
		//async: true,
    onComplete: function( response ) {
      alert(response);
			pb4.set(percent);
			text = document.getElementById('status');
			text.innerHTML = 'Migrating '+name+'...';
    }
	}).send();

	var req = new Request({
		method: 'get',
		url: 'components/com_jupgrade/includes/migrate_'+name+'.php',
		data: { 'do' : '1' },
		onRequest: function() { alert('Request made. Please wait...'); },
		onComplete: function(response) { alert('Response: ' + response); }
	}).send();
*/
};

