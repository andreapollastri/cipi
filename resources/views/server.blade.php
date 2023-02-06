@extends('template')


@section('title')
    {{ __('cipi.titles.server') }}
@endsection



@section('content')
<ol class="breadcrumb mb-4">
    <li class="ml-1 breadcrumb-item active">IP:<b><span class="ml-1" id="serveriptop"></span></b></li>
    <li class="ml-1 breadcrumb-item active">{{ __('cipi.sites') }}:<b><span class="ml-1" id="serversites"></span></b></li>
    <li class="ml-1 breadcrumb-item active">Ping:<b><span class="ml-1" id="serverping"><i class="fas fa-circle-notch fa-spin"></i></span></b></li>
</ol>
<div class="row">
    <div class="col-xl-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-microchip fs-fw mr-1"></i>
                {{ __('cipi.server_cpu_realtime_load') }}
            </div>
            <div class="card-body">
                <canvas id="cpuChart" width="100%" height="40"></canvas>
                <div class="space"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-memory fs-fw mr-1"></i>
                {{ __('cipi.server_ram_realtime_load') }}
            </div>
            <div class="card-body">
                <canvas id="ramChart" width="100%" height="40"></canvas>
                <div class="space"></div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle fs-fw mr-1"></i>
                {{ __('cipi.server_information') }}
            </div>
            <div class="card-body">
                <p>{{ __('cipi.server_name') }}:</p>
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="e.g. Production" id="servername" autocomplete="off" />
                </div>
                <div class="space"></div>
                <p>{{ __('cipi.server_ip') }}:</p>
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="e.g. 123.123.123.123" id="serverip" autocomplete="off" />
                </div>
                <div class="space"></div>
                <p>{{ __('cipi.server_provider') }}:</p>
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="e.g. Digital Ocean" id="serverprovider" autocomplete="off" />
                </div>
                <div class="space"></div>
                <p>{{ __('cipi.server_location') }}:</p>
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="e.g. Amsterdam" id="serverlocation" autocomplete="off" />
                </div>
                <div class="space"></div>
                <div class="text-center">
                    <button class="btn btn-primary" type="button" id="updateServer">{{ __('cipi.update') }}</button>
                </div>
                <div class="space"></div>
                <div class="space"></div>
                <div class="space"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-power-off fs-fw mr-1"></i>
                {{ __('cipi.system_services') }}
            </div>
            <div class="card-body">
                <p>nginx</p>
                <div class="text-center">
                    <button class="btn btn-warning" type="button" id="restartnginx">{{ __('cipi.restart') }} <i class="fas fa-circle-notch fa-spin d-none" id="loadingnginx"></i></button>
                </div>
                <div class="space"></div>
                <p>PHP-FPM</p>
                <div class="text-center">
                    <button class="btn btn-warning" type="button" id="restartphp">{{ __('cipi.restart') }} <i class="fas fa-circle-notch fa-spin d-none" id="loadingphp"></i></button>
                </div>
                <div class="space"></div>
                <p>MySql</p>
                <div class="text-center">
                    <button class="btn btn-warning" type="button" id="restartmysql">{{ __('cipi.restart') }} <i class="fas fa-circle-notch fa-spin d-none" id="loadingmysql"></i></button>
                </div>
                <div class="space"></div>
                <p>Redis</p>
                <div class="text-center">
                    <button class="btn btn-warning" type="button" id="restartredis">{{ __('cipi.restart') }} <i class="fas fa-circle-notch fa-spin d-none" id="loadingredis"></i></button>
                </div>
                <div class="space"></div>
                <p>Supervisor</p>
                <div class="text-center">
                    <button class="btn btn-warning" type="button" id="restartsupervisor">{{ __('cipi.restart') }} <i class="fas fa-circle-notch fa-spin d-none" id="loadingsupervisor"></i></button>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-tools fs-fw mr-1"></i>
                {{ __('cipi.tools') }}
            </div>
            <div class="card-body">
                <p>{{ __('cipi.php_cli_version') }}:</p>
                <div class="input-group">
                    <select class="form-control" id="phpver">
                        <option value="8.2" id="php82">8.2</option>
                        <option value="8.1" id="php81">8.1</option>
                        <option value="8.0" id="php80">8.0</option>
                        <option value="7.4" id="php74">7.4</option>
                    </select>
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button" id="changephp"><i class="fas fa-edit"></i></button>
                    </div>
                </div>
                <div class="space"></div>
                <p>{{ __('cipi.manage_cron_jobs') }}:</p>
                <div>
                    <button class="btn btn-primary" type="button" id="editcrontab">{{ __('cipi.edit_crontab') }}</button>
                </div>
                <div class="space"></div>
                <p>{{ __('cipi.reset_cipi_password') }}:</p>
                <div>
                    <button class="btn btn-danger" type="button" id="rootreset">{{ __('cipi.require_reset_cipi_password') }}</button>
                </div>
                <div class="space"></div>
                <p>{{ __('cipi.hd_memory_usage') }}:</p>
                <div>
                    <span class="btn" id="hd"><i class="fas fa-circle-notch fa-spin" title="{{ __('cipi.loading_data') }}"></i></span>
                </div>
                <div class="space"></div>
                <p>{{ __('cipi.cipi_build_version') }}:</p>
                <div>
                    <span class="btn btn-secondary" id="serverbuild"><i class="fas fa-circle-notch fa-spin"></i></span>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
</div>
@endsection



@section('extra')
<input type="hidden" id="currentip">
<div class="modal fade" id="updateServerModal" tabindex="-1" role="dialog" aria-labelledby="updateServerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" id="updateserverdialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateServerModalLabel">{{ __('cipi.update_server_modal_title') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>{{ __('cipi.update_server_modal_text') }}</p>
                <p class="d-none" id="ipnotice"><b>{!! __('cipi.update_server_modal_ip') !!}</b></p>
                <div class="text-center">
                    <button class="btn btn-primary" type="button" id="submit">{{ __('cipi.confirm') }} <i class="fas fa-circle-notch fa-spin d-none" id="loading"></i></button>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="crontabModal" tabindex="-1" role="dialog" aria-labelledby="crontabModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="crontabModalLabel">{{ __('cipi.server_crontab') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>{{ __('cipi.server_crontab_edit') }}:</p>
                <div id="crontab" style="height:250px;width:100%;"></div>
                <div class="space"></div>
                <div class="text-center">
                    <button class="btn btn-primary" type="button" id="crontabsubmit">{{ __('cipi.save') }} <i class="fas fa-circle-notch fa-spin d-none" id="crontableloading"></i></button>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="rootresetModal" tabindex="-1" role="dialog" aria-labelledby="rootresetModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rootresetModalLabel">{{ __('cipi.require_password_reset_modal_title') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>{{ __('cipi.require_password_reset_modal_text') }}</p>
                <div class="space"></div>
                <div class="text-center">
                    <button class="btn btn-danger" type="button" id="rootresetsubmit">{{ __('cipi.confirm') }} <i class="fas fa-circle-notch fa-spin d-none" id="rootresetloading"></i></button>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
</div>
@endsection



@section('css')

@endsection



@section('js')
<script>
    // Get Server info
    $('#mainloading').removeClass('d-none');

    // Crontab editor
    var crontab = ace.edit("crontab");
    crontab.setTheme("ace/theme/monokai");
    crontab.session.setMode("ace/mode/sh");

    // Crontab edit
    $('#editcrontab').click(function() {
        $('#crontabModal').modal();
    });

    // Crontab Submit
    $('#crontabsubmit').click(function() {
        $.ajax({
            url: '/api/servers/{{ $server_id }}',
            type: 'PATCH',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                'cron': crontab.getSession().getValue(),
            }),
            beforeSend: function() {
                $('#crontableloading').removeClass('d-none');
            },
            success: function(data) {
                $('#crontableloading').addClass('d-none');
                $('#crontabModal').modal('toggle');
                serverInit();
            },
        });
    });

    // Server Init
    function serverInit() {
        getDataNoDT('/api/servers',false);
        $.ajax({
            url: '/api/servers/{{ $server_id }}',
            type: 'GET',
            success: function(data) {
                $('#mainloading').addClass('d-none');
                $('#serveriptop').html(data.ip);
                $('#serversites').html(data.sites);
                $('#maintitle').html('- '+data.name);
                $('#servername').val(data.name);
                $('#serverip').val(data.ip);
                $('#serverprovider').val(data.provider);
                $('#serverlocation').val(data.location);
                $('#currentip').val(data.ip);
                crontab.session.setValue(data.cron);
                $('#serverbuild').empty();
                if(data.build) {
                    $('#serverbuild').html(data.build);
                } else {
                    $('#serverbuild').html('{{ __('cipi.unknown') }}');
                }
                switch (data.php) {
                    case '8.1':
                        $('#php81').attr("selected","selected");
                        break;
                    case '8.0':
                        $('#php80').attr("selected","selected");
                        break;
                    case '7.4':
                        $('#php74').attr("selected","selected");
                        break;
                    case '7.3':
                        // Append legacy php 7.3
                        $('#phpver').append('<option value="7.3" selected>7.3</option>');
                        break;
                    default:
                        break;
                }
            },
        });
    }

    // Init variables
    serverInit();

    // Ping
    function getPing() {
        $.ajax({
            url: '/api/servers/{{ $server_id }}/ping',
            type: 'GET',
            beforeSend: function() {
                $('#serverping').empty();
                $('#serverping').html('<i class="fas fa-circle-notch fa-spin" title="{{ __('cipi.loading_data') }}"></i>');
            },
            success: function(data) {
                $('#serverping').empty();
                $('#serverping').html('<i class="fas fa-check text-success"></i>');
            },
        });
    }
    setInterval(function() {
        getPing();
    }, 10000);
    getPing();

    // Change PHP
    $('#changephp').click(function() {
        $.ajax({
            url: '/api/servers/{{ $server_id }}',
            type: 'PATCH',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                'php': $('#phpver').val(),
            }),
            beforeSend: function() {
                $('#changephp').html('<i class="fas fa-circle-notch fa-spin" title="{{ __('cipi.loading_please_wait') }}"></i>');
            },
            success: function(data) {
                $('#changephp').empty();
                $('#changephp').html('<i class="fas fas fa-edit"></i>');
            },
        });
        serverInit();
    });

    // Restart nginx
    $('#restartnginx').click(function() {
        $.ajax({
            url: '/api/servers/{{ $server_id }}/servicerestart/nginx',
            type: 'POST',
            beforeSend: function() {
                $('#loadingnginx').removeClass('d-none');
            },
            success: function(data) {
                $('#loadingnginx').addClass('d-none');
            },
        });
    });

    // Restart php
    $('#restartphp').click(function() {
        $.ajax({
            url: '/api/servers/{{ $server_id }}/servicerestart/php',
            type: 'POST',
            beforeSend: function() {
                $('#loadingphp').removeClass('d-none');
            },
            success: function(data) {
                $('#loadingphp').addClass('d-none');
            },
        });
    });

    // Restart mysql
    $('#restartmysql').click(function() {
        $.ajax({
            url: '/api/servers/{{ $server_id }}/servicerestart/mysql',
            type: 'POST',
            beforeSend: function() {
                $('#loadingmysql').removeClass('d-none');
            },
            success: function(data) {
                $('#loadingmysql').addClass('d-none');
            },
        });
    });

    // Restart redis
    $('#restartredis').click(function() {
        $.ajax({
            url: '/api/servers/{{ $server_id }}/servicerestart/redis',
            type: 'POST',
            beforeSend: function() {
                $('#loadingredis').removeClass('d-none');
            },
            success: function(data) {
                $('#loadingredis').addClass('d-none');
            },
        });
    });

    // Restart supervisor
    $('#restartsupervisor').click(function() {
        $.ajax({
            url: '/api/servers/{{ $server_id }}/servicerestart/supervisor',
            type: 'POST',
            beforeSend: function() {
                $('#loadingsupervisor').removeClass('d-none');
            },
            success: function(data) {
                $('#loadingsupervisor').addClass('d-none');
            },
        });
    });

    // Root Reset
    $('#rootreset').click(function() {
        $('#rootresetModal').modal();
    });

    // Root Reset Submit
    $('#rootresetsubmit').click(function() {
        $('#rootresetloading').removeClass('d-none');
        $.ajax({
            url: '/api/servers/{{ $server_id }}/rootreset',
            type: 'POST',
            success: function(data) {
                success('{{ __('cipi.new_password_success') }}:<br><b>'+data.password+'</b>');
                $(window).scrollTop(0);
                $('#rootresetModal').modal('toggle');
            },
            complete: function() {
                $('#rootresetloading').addClass('d-none');
            }
        });
    });

    //Check IP conflict (edit)
    function ipConflictEdit(ip,server_id) {
        conflict = 0;
        JSON.parse(localStorage.otherdata).forEach(server => {
            if(ip === server.ip && server.server_id !== server_id) {
                conflict = conflict + 1;
            }
        });
        return conflict;
    }

    // Update Server
    $('#updateServer').click(function() {
        $('#ipnotice').addClass('d-none');
        if($('#serverip').val() != $('#currentip').val()) {
            $('#newip').html($('#serverip').val());
            $('#ipnotice').removeClass('d-none');
        }
        validation = true;
        if(!$('#servername').val() || $('#servername').val().length < 3) {
            $('#servername').addClass('is-invalid');
            $('#submit').addClass('disabled');
            validation = false;
        }
        server_id = '{{ $server_id }}';
        if(!$('#serverip').val() || !ipValidate($('#serverip').val()) || ipConflictEdit($('#serverip').val(),server_id) > 0) {
            $('#serverip').addClass('is-invalid');
            $('#submit').addClass('disabled');
            validation = false;
        }
        if(validation) {
            $('#loading').addClass('d-none');
            $('#updateServerModal').modal();
        }
    });

    // Update Server Validation
    $('#servername').keyup(function() {
        $('#servername').removeClass('is-invalid');
        $('#submit').removeClass('disabled');
    });
    $('#serverip').keyup(function() {
        $('#serverip').removeClass('is-invalid');
        $('#submit').removeClass('disabled');
    });

    // Update Server Submit
    $('#submit').click(function() {
        $.ajax({
            url: '/api/servers/{{ $server_id }}',
            type: 'PATCH',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                'name':     $('#servername').val(),
                'ip':       $('#serverip').val(),
                'provider': $('#serverprovider').val(),
                'location': $('#serverlocation').val()
            }),
            beforeSend: function() {
                $('#loading').removeClass('d-none');
            },
            success: function(data) {
                serverInit();
                $('#loading').addClass('d-none');
            },
            complete: function() {
                $('#ipnotice').addClass('d-none');
                $('#updateServerModal').modal('toggle');
            }
        });
    });

    // Charts style
    Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
    Chart.defaults.global.defaultFontColor = '#292b2c';

    // CPU Chart
    cpu = document.getElementById("cpuChart");
    cpuChart = new Chart(cpu, {
    type: 'line',
        showXLabels: 10,
    data: {
        labels: [],
        datasets: [{
        label: "CPU Load (%)",
        lineTension: 0.3,
        backgroundColor: "rgba(2,117,216,0.2)",
        borderColor: "rgba(2,117,216,1)",
        pointRadius: 5,
        pointBackgroundColor: "rgba(2,117,216,1)",
        pointBorderColor: "rgba(255,255,255,0.8)",
        pointHoverRadius: 5,
        pointHoverBackgroundColor: "rgba(2,117,216,1)",
        pointHitRadius: 50,
        pointBorderWidth: 2,
        data: []
        }],
    },
    options: {
        scales: {
        xAxes: [{
            time: {
            unit: 'date'
            },
            gridLines: {
            display: false
            },
            ticks: {
            maxTicksLimit: 7
            }
        }],
        yAxes: [{
            ticks: {
            min: 0,
            max: 100,
            maxTicksLimit: 5
            },
            gridLines: {
            color: "rgba(0, 0, 0, .125)",
            }
        }]
        },
        legend: {
        display: false
        }
    }
    });

    // RAM Chart
    ram = document.getElementById("ramChart");
    ramChart = new Chart(ram, {
    type: 'line',
        showXLabels: 10,
    data: {
        labels: [],
        datasets: [{
        label: "RAM Usage (%)",
        lineTension: 0.3,
        backgroundColor: "rgba(2,117,216,0.2)",
        borderColor: "rgba(2,117,216,1)",
        pointRadius: 5,
        pointBackgroundColor: "rgba(2,117,216,1)",
        pointBorderColor: "rgba(255,255,255,0.8)",
        pointHoverRadius: 5,
        pointHoverBackgroundColor: "rgba(2,117,216,1)",
        pointHitRadius: 50,
        pointBorderWidth: 2,
        data: []
        }],
    },
    options: {
        scales: {
        xAxes: [{
            time: {
            unit: 'date'
            },
            gridLines: {
            display: false
            },
            ticks: {
            maxTicksLimit: 7
            }
        }],
        yAxes: [{
            ticks: {
            min: 0,
            max: 100,
            maxTicksLimit: 5
            },
            gridLines: {
            color: "rgba(0, 0, 0, .125)",
            }
        }]
        },
        legend: {
        display: false
        }
    }
    });

    //CPU & RAM charts
    function chartsUpdate(cpuChart,ramChart) {
        $.ajax({
            type: 'GET',
            url: '/api/servers/{{ $server_id }}/healthy',
            success: function (result)
            {
                //HD
                $('#hd').empty();
                $('#hd').removeClass('btn-secondary');
                $('#hd').removeClass('btn-success');
                $('#hd').removeClass('btn-warning');
                $('#hd').removeClass('btn-danger');
                $('#hd').html(result.hdd+'%');
                if(result.hdd < 61) {
                    $('#hd').addClass('btn-success');
                }
                if(result.hdd > 60) {
                    $('#hd').addClass('btn-warning');
                }
                if(result.hdd > 85) {
                    $('#hd').addClass('btn-danger');
                }
                //CPU
                labels = cpuChart.data.labels
                data = cpuChart.data.datasets[0].data
                if (labels.length > 10) {
                    labels = labels.shift();
                } else {
                    var d = new Date();
                    if(d.getHours() < 10) {
                        hours = '0'+d.getHours();
                    } else {
                        hours = d.getHours();
                    }
                    if(d.getMinutes() < 10) {
                        minutes = '0'+d.getMinutes();
                    } else {
                        minutes = d.getMinutes();
                    }
                    labels.push(hours+':'+minutes);
                }
                if (data.length > 10) {
                    data = data.shift();
                } else {
                    data.push(result.cpu);
                }
                cpuChart.update();
                //RAM
                labels = ramChart.data.labels
                data = ramChart.data.datasets[0].data
                if (labels.length > 10) {
                    labels = labels.shift();
                } else {
                    var d = new Date();
                    if(d.getHours() < 10) {
                        hours = '0'+d.getHours();
                    } else {
                        hours = d.getHours();
                    }
                    if(d.getMinutes() < 10) {
                        minutes = '0'+d.getMinutes();
                    } else {
                        minutes = d.getMinutes();
                    }
                    labels.push(hours+':'+minutes);
                }
                if (data.length > 10) {
                    data = data.shift();
                } else {
                    data.push(result.ram);
                }
                ramChart.update();
            }
        })
    }

    //First step charts
    setTimeout(function(cpuChart,ramChart){
        chartsUpdate(cpuChart,ramChart);
    }, 500,cpuChart,ramChart);

    //Other steps charts
    setInterval(function(cpuChart,ramChart){
        chartsUpdate(cpuChart,ramChart);
    }, 30000,cpuChart,ramChart);

    //Init charts
    chartsUpdate(cpuChart,ramChart);

</script>
@endsection
