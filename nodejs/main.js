
// PROTOTYPE MODIFICATION

Date.prototype.getDayOfYear = function() {
  var onejan = new Date(this.getFullYear(),0,1);
  return Math.ceil((this - onejan) / 86400000);
}


// Node Modules

var async = require('async');

var hiveLib = require('thrift-hive');
var mysqlLib = require('mysql');


var mysql = mysqlLib.createConnection({
  host     : 'localhost',
  user     : 'root',
  password : 'root',
  port     : '3306',
  database : 'bigdata'
});

var hive = hiveLib.createClient({
  version: '0.7.1-cdh3u2',
  server: '130.206.80.46',
  port: 10000,
  timeout: 1000
});

var query = function (query, callback){
  
  var rows = [];

  hive.query(query).on('row', function(row){
  
    rows.push(row);

  })
  .on('error', function(err){
    
    return callback(err);
  
  })
  .on('end', function(){
  
    return callback(null, rows);
  
  });


};


/*query("select c1,c3,c5 from (select entity_name as c1 from santander_smart group by entity_name) table1 join (select distinct entity_name as c2,value as c3 from santander_smart where attribute_name like '%Latitud%') table2 join (select distinct entity_name as c4,value as c5 from santander_smart where attribute_name like '%Longitud%') table3 on table1.c1 = table2.c2 and table1.c1 = table3.c4", function (err, rows){

  async.map(rows, function (row, callback){
    
    mysql.query('UPDATE into traffic_sensors WHERE id='+mysql.escape(row[0])+' SET ?', {
      id: row[0],
      lat: row[1],
      lng: row[2]
    }, function (err, result){
      
      // Check if updated row and if now it will be inserted

      if(result){
        callback(err);
      } else{
        
        mysql.query('INSERT into traffic_sensors SET ?', {
          id: row[0],
          lat: row[1],
          lng: row[2]
        }, function (err){
          callback(err);
        });
      
      }
    });

  }, function (err){
    if(err){
      console.log(err);
    }
  });

});*/

query("SELECT * FROM santander_smart WHERE attribute_name='trafficIntensity' ORDER BY entity_name", function (err, rows){

  if(err){
    console.log(err);
  } else if(rows){
    
    console.log('There are rows');

    async.map(rows, function (row, callback){

      var date = new Date(row[0]);

      var time_hour = date.getHours();

      var day_week = (date.getDay() === 0) ? 7 : date.getDay();

      var day_year = date.getDayOfYear();

      //console.log(time_hour, day_week, day_year);

      // Checks if there is the same value saved for that hour

      mysql.query('SELECT * FROM traffic_history WHERE id='+mysql.escape(row[2])+' AND time_hour='+time_hour+' AND value='+row[6] + ' AND day_year='+day_year, function (err, result){
        
        if(err){
        
          callback(err);
        
        } else if(result.length){
        
          callback(null);
        
        } else{

          mysql.query('INSERT INTO traffic_history SET ? ', {
          
            id: row[2],
            day_week: day_week,
            day_year: day_year,
            time_hour: time_hour,
            value: row[6]
          
          }, function (err, rows){

            if(err){
              callback(err);
            } else{
              callback(null);
            }

          });

        }
      });

    }, function (err){
      
      if(err){
        console.log(err);  
      }
      
    });
  }

});









// SELECT DISTINCT entity_name FROM santander_smart;