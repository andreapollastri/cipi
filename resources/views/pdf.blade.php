<!DOCTYPE html>
<html>
<head>
	<title>{{ $domain }}</title>
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
        @switch({{$autoinstall}})
            @case('wordpress')
                <li><b>Path</b> /home/{{ $username }}/web/wordpress/</li>
                @break
            @case('laravel')
                <li><b>Path</b> /home/{{ $username }}/web/laravel/public</li>
                @break
            @case('git')
                <li><b>Path</b> /home/{{ $username }}/web/{{$path}}</li>
                @break
            @case('none')
                <li><b>Path</b> /home/{{ $username }}/web/{{$path}}</li>
                @break
            @default

        @endswitch
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
	<center>
		<p>
			<i>phpmyadmin avaiable on: http://{{$ip}}/phpmyadmin/</i>
		</p>
	</center>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<center>
		<p>Take care about this data :)</p>
	</center>
	<br>
	<br>
	<br>
	<br>
	<center>
		<h6>Cipi Control Panel</h6>
	</center>
</body>
</html>
