var request = require('request');

var async = require('async');

var baseName = "urn_smartsantander_testbed_";
var startId = 3300;
var lastId = 3337;
var currentId = startId;


function getItem (){
	request.get('http://130.206.83.130/learn.php?sensor='+baseName+currentId, function (err, body){

		currentId++;

		if(currentId <= lastId){

			var progress = (currentId - startId) / (lastId - startId) * 100;

			console.log(progress+'%');

			getItem();

		} else{

			console.log('FINISHED');

		}


	});
}


getItem();