@extends('template')


@section('title')
Sites
@endsection



@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card mb-4">
            <div class="card-header text-right">
                <button class="btn btn-sm btn-secondary" id="newSite">
                    <i class="fas fa-plus mr-1"></i><b>New Site</b>
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dt" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="text-center">Domain</th>
                                <th class="text-center text-center d-none d-md-table-cell">Aliases</th>
                                <th class="text-center d-none d-lg-table-cell">Server</th>
                                <th class="text-center d-none d-xl-table-cell">IP</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



@section('extra')
<div class="modal fade" id="newSiteModal" tabindex="-1" role="dialog" aria-labelledby="newSiteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" id="newsitedialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newSiteModalLabel">Add a new site</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="newsiteform">
                    <label for="newsitedomain">Site domain</label>
                    <div class="input-group">
                        <input class="form-control" type="text" id="newsitedomain" placeholder="e.g. domain.ltd" autocomplete="off" />
                    </div>
                    <div class="space"></div>
                    <label for="newsiteserver">Server</label>
                    <div class="input-group">
                        <select class="form-control" id="newsiteserver"></select>
                    </div>
                    <div class="space"></div>
                    <label for="newsiteprovider">PHP Version</label>
                    <div class="input-group">
                        <select class="form-control" id="newsitephp">
                            <option value="8.0" selected>8.0</option>
                            <option value="7.4">7.4</option>
                            <option value="7.3">7.3</option>
                        </select>
                    </div>
                    <div class="space"></div>
                    <label for="newsitebasepath">Basepath</label>
                    <div class="input-group">
                        <input class="form-control" type="text" id="newsitebasepath" placeholder="e.g. public" autocomplete="off" />
                    </div>
                    <div class="space"></div>
                    <div class="text-center">
                        <button class="btn btn-primary" type="button" id="submit">Confirm <i class="fas fa-circle-notch fa-spin d-none" id="loading"></i></button>
                    </div>
                </div>
                <div id="newsiteok" class="d-none container">
                    <div class="row">
                        <div class="col-xs-12">
                            <p><b>Your site is ready!</b></b>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-12">
                            <p>Domain:<br><b><span id="newsitedomainok"></b></b></p>
                            <p>Server IP:<br><b><span id="newsiteip"></b></b></p>
                            <p>SSH Username:<br><b><span id="newsiteusername"></b></p>
                            <p>SSH Password:<br><b><span id="newsitepassword"></b></p>
                            <p>MySQL database:<br><b><span id="newsitedbname"></b></p>
                            <p>MySQL username:<br><b><span id="newsitedbusername"></b></p>
                            <p>MySQL password:<br><b><span id="newsitedbpassword"></b></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-12">
                            <p>Document root:<br><b>/home/<span id="newsitebasepathuser"></span>/web/<span id="newsitebasepath"></b></p>
                        </div>
                    </div>
                    <div class="space"></div>
                    <div class="row">
                        <div class="col-xl-12 text-center">
                            <a href="" target="_blank" id="newsitepdf">
                                <button class="btn btn-success" type="button"><i class="fas fa-file-pdf"></i> Download (3 minutes link)</button>
                            </a>
                        </div>
                    </div>
                    <div class="space"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="deleteSiteModal" tabindex="-1" role="dialog" aria-labelledby="deleteSiteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSiteModalLabel">Delete site</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure to delete site <b><span id="deletesitename"></span></b> and its database and aliases?</p>
                <div class="space"></div>
                <input type="hidden" id="deletesiteid" value="" />
                <div class="space"></div>
                <div class="text-center">
                    <button class="btn btn-danger" type="button" id="delete">Delete <i class="fas fa-circle-notch fa-spin d-none" id="loadingdelete"></i></button>
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
<script src="/assets/js/sites.js?v=20210413"></script>
@endsection