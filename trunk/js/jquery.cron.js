/**
* ####### Released freely, please contribute and fork back into the project if you want to. ######
* This is jCron, a sort of crontab thing for javascript, built from jquery
* To use:
* To register a function, you must use the $.cron.register function, pass it a timing,
* either a pre-prepared one, e.g onSecond and the function (second parameter) will be called every time jCron meets the
* timing you specified. The callback function registered must have at least one parameter, this is a date object, that provides you with standard javascript functionality
* and extra helpers to allow for extensive use of dates and times/
* ________________________________________
* | Extras:
* | The Number object has been extended to give your function helpers such
* | as daysAgo, weeksAgo, dayTime etc a la Rails functionality
* | So far that's it.
*/
(function($){
//The actual extension to jQuery, all it does is set init function to check every fifth of
// a second to see if it has changed, if it has start cron.
$.cron = function(){
if($.cron.on == false){
$.cron.wait = setInterval('$.cron.init()', 200);
}else{
$.cron.init();
}
};
//Version
$.cron.version = '0.1';
//if the clock has been turned on by the calling script, false by default
$.cron.on = false;
//if the clock is running or not.
$.cron.running = false;
//if the event dispatcher has not finished yet, this will skip the next
//this prevents multiple never ending loops building up as they are repeatedly called
//hopefully stops some crashes and memory leaks
$.cron.busy = false;
//This is the tab, where all the normal event handlers are called from
$.cron.tab = { onSecond : [],
onMinute : [],
onHour : [],
onDay : []
};
$.cron.others = {};
//Main function for dispatching events to the functions
$.cron.main = function(){
//if clock has been turned off
if($.cron.on == false){
window.clearInterval($.cron.running);
//start waiting again
$.cron();
}else{
//Start dispatching to callback functions if previous function has finished
if($.cron.busy == false){
$.cron.busy = true;
//Dispatch to all event handlers, the decision making is in the function
$.each($.cron.tab, function(i,v){
//dispatch to each general handler
$.cron.dispatch(i);
});
$.cron.dispatchToOthers();
//Set busy to false to enable next iteration to continue
$.cron.busy = false;
}
}
};
$.cron.init = function(){
if($.cron.on == true){
//making sure it's at the right part of the second
d = $.cron.d().getMilliseconds();
//if it is the correct part of the second
if(d == 0){
$.cron.running = setInterval('$.cron.main();', 1000);
window.clearInterval($.cron.wait);
$.cron.main();
}else{
//otherwise set init to be called again, at the right part of the second
setTimeout('$.cron.init()', (1000-d));
}
}
};
//actually call the functions to be called on this timing.
$.cron.dispatch = function(timing){
//check the timing
d = new Date();
general = null;
switch(timing){
case 'onMinute':
general = d.getSeconds();
break;
case 'onHour':
general = d.getMinutes();
break;
case 'onDay':
general= d.getHours();
break;
}
//Always call the onSecond handler
if($.cron.tab[timing].length > 0){
if(timing == 'onSecond'){
$.each($.cron.tab.onSecond, function(i, func){
try{
if(typeof(func) == 'function'){
func($.cron.d());
func = null;
}
}catch(e){
console.log(e);
//removing from future function calls to safeguard overall functionality
$.cron.tab.onSecond[i] = null;
}
});
//if it is the write part of the second
//this is to stop inaccurate timings.
}else if(general == 0){
$.each($.cron.tab[timing], function(i,func){
if(timing != 'onSecond'){
try{
func($.cron.d());
func = null;
}catch(e){
//log the error created
console.log(e);
//if function created a catchable error it will be removed from future calls
$.cron.tab[timing][i] = null;
}
}
});
}
}
};
//dispatch to all non standard timings
$.cron.dispatchToOthers = function(){
$.each($.cron.others, function(i,v){
//if it has only just been added
if(v.next == null){
v.next = v.timing;
}
$('#tester').html(v.next);
if(v.next == 1000){
v.next = v.timing;
v.caller();
}else{
v.next = v.next-1000;
}
});
};
$.cron.register = function(timing, func, label){
switch(timing){
case 'onSecond':
$.cron.tab.onSecond.push(func);
break;
case 'onMinute':
$.cron.tab.onMinute.push(func);
break;
case 'onHour':
$.cron.tab.onHour.push(func);
break;
case 'onDay':
$.cron.tab.onDay.push(func);
break;
default:
d= $.parseTime(timing);
if(!label){
label = Math.random()*d;
}
$.cron.others[label] = {'caller': func,
'next':null,
'timing': d,
'label': label
};
break;
}
if (!label){
return {'event':timing, 'index' : $.cron.tab[timing].length -1};
}else{
return label;
}
};
$.cron.deRegister = function(id){
if(typeof(id) == 'string'){
if(typeof($.cron.tab[id]) != 'undefined'){
$.cron.tab[id] = [];
}else{
$.each($.cron.others, function(i, v){
if(v.label == id){
$.cron.others[i] = null;
}
});
}
}else if(!id.index && !id.event){
throw('You must provide the object supplied to you by the $.cron.register() function');
}else{
$.cron.tab[id.event][id.index] = null;
}
};
/*
This is a wrapper for setTimeout, all it does is provide intelligent parsing for the
timing, it takes strings such as 2s, 10m and translates them into millisecond timings.
this does not use the tab functionality that the rest of the plugin uses for convenience
*/
$.cron.once = function(timing, func){
t = $.parseTime(timing);
id = setTimeout(func, t);
return id;
};
/*
Provides a modified version of the date object to the functions registered with the tab,
provides methods such as daysAgo and hoursAgo to make it easier to work with dates and times
###### TO BE EXTENDED ######
*/
$.cron.d = function(){
Date.prototype.daysAgo = function(t){
return this.setTime((this.getTime()-(t*86400))*10);
};
Date.prototype.minutesAgo = function(t){
return this.setTime((this.getTime()-(t*60))*10);
};
Date.prototype.hoursAgo = function(t){
return this.setTime((this.getTime()-(t*3600))*10);
};
Date.prototype.daysTime = function(t){
return this.setTime((this.getTime()+(t*86400))*10);
};
Date.prototype.minutesTime = function(t){
return this.setTime((this.getTime()+(t*60))*10);
};
Date.prototype.hoursTime = function(t){
return this.setTime((this.getTime()+(t*3600))*10);
};
return new Date();
};
/**
Actually parses the times, needs to be reimplemented, tried looping,
but some browsers don't like not having the opening bracket around the
regex
*/
$.parseTime = function(str){
if(str.match(/\d{1,3}[s]/)){
return Number(str.replace(/(\w){1,3}[s]/, '$1'))*1000;
}
if(str.match(/\d{1,3}[m]/)){
return (Number(str.replace(/(\d){1,3}[m]/, '$1'))*60)*1000;
}
if(str.match(/\d{1,3}[h]/)){
return ((Number(str.replace(/(\d){1,3}[h]/, '$1'))*60)*60)*1000;
}
};
//Stop everything
$.cron.unLoad = function(){
window.clearInterval($.cron.running);
window.clearInterval($.cron.wait);
};
})(jQuery);
window.onUnload = $.cron.unLoad();
