@extends('template')

@section('title')Dashboard @endsection

@section('css')
<link rel="stylesheet" href="https://allyoucan.cloud/cdn/datatable/1.10.13/css/dataTables.css">
<link rel="stylesheet" href="https://allyoucan.cloud/cdn/datatable/1.10.13/css/reponsive.css">

@endsection

@section('content')
<div class="col-xs-12">
    <div class="row">
        <div class="col-xs-10">
            <h4><i class="fas fa-cloud"></i> cloud</h4>
        </div>
        <div class="col-xs-2 text-right">
            <h3><a href="javascript:void(null)"><i class="fas fa-plus-circle" id="create"></i></a></h3>
        </div>
    </div>
    <div class="space"></div>
    <div class="row">
        <div class="col-xs-12">
            <table id="clouds" class="display responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th class="text-center">Server</th>
                        <th class="text-center" data-priority="1">IP</th>
                        <th class="text-center">Apps</th>
                        <th class="text-center">Provider</th>
                        <th class="text-center">Location</th>
                        <th class="text-center" data-priority="2">Manage</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection


@section('js')
<script src="https://allyoucan.cloud/cdn/datatable/1.10.13/js/dataTables.js" defer></script>
<script src="https://allyoucan.cloud/cdn/datatable/1.10.13/js/responsive.js" defer></script>

<script>
$(document).ready(function() {
    if($('#clouds').length ) {
        $('#clouds').DataTable( {
            "responsive": true,
            "ajax": {
                "url": '/cloud/api',
                "dataSrc": ""
            },
            'columns': [
                { data: 'name' },
                { data: 'ip' },
                { data: 'apps' },
                { data: 'provider' },
                { data: 'location' },
                { data: 'code' }
            ],
            'columnDefs': [
                {
                    'targets': 0,
                    'render': function ( data, type, row, meta ) {
                        return '<div class="limitch upper">'+data+'</div>';
                    }
                },
                {
                    'targets': 1,
                    'render': function ( data, type, row, meta ) {
                        return '<div class="text-center">'+data+'</div>';
                    }
                },
                {
                    'targets': 2,
                    'render': function ( data, type, row, meta ) {
                        return '<div class="text-center">'+data+'</div>';
                    }
                },
                {
                    'targets': 3,
                    'render': function ( data, type, row, meta ) {
                        icon = cloudicon(data);
                        return '<div class="text-center">'+icon+'</div>';
                    }
                },
                {
                    'targets': 4,
                    'render': function ( data, type, row, meta ) {
                        return '<div class="text-center upper">'+data+'</div>';
                    }
                },
                {
                    'targets': 5,
                    'render': function ( data, type, row, meta ) {
                        return '<div class="text-center"><a href="/cloud/'+data+'"><i class="fas fa-arrow-right"></i></a></div>';
                    }
                }
            ],
        });
    }
});
</script>
@endsection
