<html>
<head>
<script>
var baseName = "urn_smartsantander_testbed_";
var startId = 3300;
var lastId = 3337;
var currentId = startId;

updateData();

function updateData()
{
	if (window.XMLHttpRequest)
	{// code for IE7+, Firefox, Chrome, Opera, Safari
	  xmlhttp=new XMLHttpRequest();
	}
	else
	{// code for IE6, IE5
	  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange=function()
	{
		if (xmlhttp.readyState==4 && xmlhttp.status==200)
		{
			var progress = (currentId - startId) / (lastId - startId) * 100;
			document.getElementById("done").innerHTML= progress + "%";
			currentId = currentId + 1;
			var para=document.createElement("p");
			var node=document.createTextNode(xmlhttp.responseText);
			para.appendChild(node);
			document.getElementById("list").appendChild(para);
			if (currentId <= lastId)
				updateData()
			else
				document.getElementById("done").innerHTML= "TASK DONE";
		}
	}
	xmlhttp.open("GET","learn.php?sensor="+baseName+currentId,true);
	xmlhttp.timeout = 0;
    xmlhttp.ontimeout = function () { alert("Timed out!!!"); }
	xmlhttp.send();
}
</script>
</head>
<body>
<div id="list">
<p>Done: <span id="done">0%</span></p>
</div>

</body>
</html>