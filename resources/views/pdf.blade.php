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
        @switch($autoinstall)
            @case('wordpress')
                <li><b>Path</b> /home/{{ $username }}/web/wordpress/</li>
                @break
            @case('laravel')
                <li><b>Path</b> /home/{{ $username }}/web/laravel/public</li>
                @break
            @case('git')
                <li><b>Path</b> /home/{{ $username }}/web/{{ $path }}</li>
                @break
            @case('none')
                <li><b>Path</b> /home/{{ $username }}/web/{{ $path }}</li>
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
    <br>
	<hr>
    <br>
    <center>
        <p>@switch($autoinstall)
            @case('wordpress')
                This is a Wordpress pre-installation.<br>Visit {{ $domain }} the first time to complete the setup!
                @break
            @case('laravel')
                This is a Laravel pre-installation.<br>Happy code!
                @break
            @case('git')
                This is a Github repositoy.<br>Configure deploy.sh script into /home/{{$username}}/git/, copy deploy.pub key into your Github SSH keys<br>and run <i>sh deploy.sh</i> to deploy your repo!
                @break
            @case('none')
                This is a pure PHP/MySql web application!
                @break
            @default

        @endswitch</p>
        <p>Phpmyadmin is avaiable at: http://{{$ip}}/phpmyadmin/.</p>
        <p>You can manage your cronjobs via SSH using <i>crontab -e</i> command.</p>
    </center>
	<br>
    <br>
    <br>
	<center>
		<p>Take care about this data :)</p>
	</center>
    <br>
    <br>
	<br>
	<center>
		<h5>Cipi Control Panel (cipi.sh for more info)</h5>
	</center>
</body>
</html>
