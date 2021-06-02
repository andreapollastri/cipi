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
		<h4>{{ strtoupper(__('cipi.site')) }}</h4>
		<h1>{{ $domain }}</h1>
    </center>
	<br>
    <h3>SSH/SFTP</h3>
	<ul>
		<li><b>{{ __('cipi.host') }}</b> {{$ip}}</li>
		<li><b>{{ __('cipi.port') }}</b> 22</li>
		<li><b>{{ __('cipi.username') }}</b> {{$username}}</li>
        <li><b>{{ __('cipi.password') }}</b> {{$password}}</li>
        <li><b>{{ __('cipi.path') }}</b> /home/{{ $username }}/web/{{ $path }}</li>
	</ul>
	<br>
	<hr>
	<br>
	<h3>{{ __('cipi.database') }}</h3>
	<ul>
		<li><b>{{ __('cipi.host') }}</b> 127.0.0.1</li>
		<li><b>{{ __('cipi.port') }}</b> 3306</li>
		<li><b>{{ __('cipi.username') }}</b> {{$username}}</li>
		<li><b>{{ __('cipi.password') }}</b> {{$dbpass}}</li>
		<li><b>{{ __('cipi.name') }}</b> {{$username}}</li>
    </ul>
    <br>
	<hr>
    <br>
    <center>
        <p>{!! __('pdf_site_php_version', ['domain' => $domain, 'php' => $php]) !!}</p>
    </center>
    <br>
	<center>
		<p>{{ __('cipi.pdf_take_care') }}</p>
	</center>
    <br>
    <br>
	<br>
	<center>
		<h5>{{ config('cipi.name') }}<br>({{ config('cipi.website') }})</h5>
	</center>
</body>
</html>
