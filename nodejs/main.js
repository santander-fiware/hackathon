var hive = require('thrift-hive');
// Client connection
var client = hive.createClient({
  version: '0.7.1-cdh3u2',
  server: '130.206.80.46',
  port: 10000,
  timeout: 1000
});
// Execute call
client.execute('use default', function(err){
  // Query call
  client.query('select * from santander_llamas LIMIT 100')
  .on('row', function(database){
    console.log(database);
  })
  .on('error', function(err){
    console.log(err.message);
    client.end();
  })
  .on('end', function(){
    client.end();
  });
});