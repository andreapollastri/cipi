<!DOCTYPE html>
<html>
<head>
    <title>{{ $domain }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
        }
    </style>
</head>
<body>
	<center>
		<h4>APPLICATION</h4>
		<h1>{{ $domain }}</h1>
    </center>
	<br>
    <h3>SSH/SFTP</h3>
	<ul>
		<li><b>Host</b> {{$ip}}</li>
		<li><b>Port</b> {{$port}}</li>
		<li><b>User</b> {{$username}}</li>
        <li><b>Pass</b> {{$password}}</li>
        <li><b>Path</b> /home/{{ $username }}/web/{{ $path }}</li>
	</ul>
	<br>
	<hr>
	<br>
	<h3>Database</h3>
	<ul>
		<li><b>Host</b> 127.0.0.1</li>
		<li><b>Port</b> 3306</li>
		<li><b>User</b> {{$username}}</li>
		<li><b>Pass</b> {{$dbpass}}</li>
		<li><b>Name</b> {{$username}}</li>
    </ul>
    <br>
	<hr>
    <br>
    <center>
        <p>Your application <i>{{ $domain }}</i> is PHP {{ $php }} based!</p>
    </center>
    <br>
	<center>
		<p>Take care about this data :)</p>
	</center>
    <br>
    <br>
	<br>
	<center>
		<h5>Cipi Control Panel<br>(https://cipi.sh)</h5>
	</center>
</body>
</html>
