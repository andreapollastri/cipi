<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Cloud Control Panel">
    <meta name="author" content="Andrea Pollastri - andrea@pollastri.dev">
    <link rel="icon" type="image/png" href="/favicon.png">

    <title>Cipi</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://allyoucan.cloud/cdn/bootstrap/core/3.3.7/css/bootstrap.css">
    <link rel='stylesheet' href='https://allyoucan.cloud/fonts/css/?family=OpenSans-Regular'>

    <style>
    html {
        font-family: OpenSans-Regular;
        position: relative;
        min-height: 100%;
    }
    body {
        margin-bottom: 60px;
    }
    .space {
        min-height: 25px;
    }
    .loginbox {
        width: 95%;
        max-width: 400px;
        border: 1px #555 solid;
        min-height: 375px;
        margin: 0 auto;
        margin-top: 90px;
    }
    </style>
    <!-- CSS -->

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>




    <!-- MAIN -->
    <div class="container">
        <div class="row">
            <div class="col-xs-12">

                <div class="loginbox">
                    <div class="row">
                        <div class="col-xs-12 text-center">
                            <div class="space"></div>
                            <div class="space"></div>
                            <img src="/logo.png" class="center-block">
                            <h5>Cloud Control Panel</h5>
                            <div class="space"></div>
                            <form action="#" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-xs-4" style="background: red">
                                        username
                                    </div>
                                    <div class="col-xs-8">
                                        form
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-4">
                                        password
                                    </div>
                                    <div class="col-xs-8">
                                        form
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- MAIN -->



    <!-- JS -->
    <script src="https://allyoucan.cloud/cdn/jquery/core/3.4.1/jquery.js"></script>
    <script src="https://allyoucan.cloud/cdn/bootstrap/core/3.3.7/js/bootstrap.js"></script>
    <!-- JS -->


</body>

</html>
