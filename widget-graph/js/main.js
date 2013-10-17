MashupPlatform.wiring.registerCallback('sensor_id', function (string){

    console.log(string);

    $.getJSON('http://130.206.83.130/getsensordata.php?id='+string, function (data){

        $('#container').highcharts(data);

    });

});
    
/*$(document).ready(function (){

    (function (string){

        $.getJSON('http://130.206.83.130/getsensordata.php?id='+string, function (data){

            $('#container').highcharts(data);

        })

    })('urn_smartsantander_testbed_3317');

});*/