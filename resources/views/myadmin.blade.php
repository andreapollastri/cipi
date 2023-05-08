<!DOCTYPE html>
<html>
<head>
    <title>My Secure Admin</title>
    <link rel="stylesheet" href="{{ asset('mysecureadmin/css/phpmyadmin.css') }}">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">PHPMyAdmin</h3>
                    </div>
                    <div class="card-body">
                        <?php
                            // Include the PHPMyAdmin code here
                            include(public_path('mysecureadmin/index.php'));
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
