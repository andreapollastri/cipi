@extends('template')


@section('title')
Manage Site
@endsection



@section('content')
<ol class="breadcrumb mb-4">
    <li class="ml-1 breadcrumb-item active">IP:<b><span class="ml-1" id="siteip"></span></b></li>
    <li class="ml-1 breadcrumb-item active">ALIASES:<b><span class="ml-1" id="sitealiases"></span></b></li>
    <li class="ml-1 breadcrumb-item active">PHP:<b><span class="ml-1" id="sitephp"></span></b></li>
    <li class="ml-1 breadcrumb-item active">DIR:<b><span class="ml-1">/home/</span><span id="siteuserinfo"></span>/web/<span id="sitebasepathinfo"></span></b></li>
</ol>
<div class="row">
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle fs-fw mr-1"></i>
                Basic information
            </div>
            <div class="card-body">
                <p>Domain:</p>
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="e.g. domain.ltd" id="sitedomain" autocomplete="off" />
                </div>
                <div class="space"></div>
                <p>Basepath:</p>
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="e.g. public" id="sitebasepath" autocomplete="off" />
                </div>
                <div class="space"></div>
                <div class="text-center">
                    <button class="btn btn-primary" type="button" id="updateSite">Update <i class="fas fa-circle-notch fa-spin d-none" id="updateSiteloading"></i></button>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-globe fs-fw mr-1"></i>
                Manage aliases
            </div>
            <div class="card-body">
                <p>Add alias:</p>
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="e.g. www.domain.ltd" id="siteaddalias" autocomplete="off" />
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button" id="siteaddaliassubmit"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
                <div class="space"></div>
                <div style="min-height:135px">
                    <p>Aliases:</p>
                    <div id="sitealiaseslist"></div>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-lock fs-fw mr-1"></i>
                SSLs and Security
            </div>
            <div class="card-body">
                <p>Require and generate free Let's Encrypt certificate for site domain and aliases:</p>
                <button class="btn btn-success btn" type="button" id="sitessl">Generate SSLs <i class="fas fa-circle-notch fa-spin d-none" id="sitesslloading"></i></button>
                <div class="space"></div>
                <div class="space"></div>
                <p>Passwords reset:</p>
                <button class="btn btn-warning btn mr-3" type="button" id="sitesshreset">SSH</button>
                <button class="btn btn-warning btn mr-3" type="button" id="sitemysqlreset">MySql</button>
                <div class="space" style="min-height:38px"></div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-rocket fs-fw mr-1"></i>
                Application installer
            </div>
            <div class="card-body text-center">
                <div class="space"></div>
                <div class="space"></div>
                <div class="space"></div>
                <div class="space"></div>
                <div class="space"></div>
                <h5>Coming soon...</h5>
                <div class="space"></div>
                <div class="space"></div>
                <div class="space"></div>
                <div class="space"></div>
                <div class="space"></div>
                <div class="space"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fab fa-github fs-fw mr-1"></i>
                Github repository
            </div>
            <div class="card-body">
                <p>Configura un repository Github</p>
                <div class="text-center">
                    <button class="btn btn-warning" type="button" style="min-width:200px" id="sitesetrepo">Repo configuration</button>
                    <div class="space"></div>
                </div>
                <div class="text-center">
                    <button class="btn btn-warning" type="button" style="min-width:200px" id="editdeploy">Edit deploy scripts</button>
                    <div class="space"></div>
                </div>
                <p>
                    To run deploy:
                    <ul style="font-size:14px;">
                        <li>ssh <span id="repodeployinfouser1"></span>@<span id="repodeployinfoip"></span></li>
                        <li>sh /home/<span id="repodeployinfouser2"></span>/git/deploy.sh</li>
                    </ul>
                </p>
            </div>
        </div>
    </div>  
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-tools fs-fw mr-1"></i>
                Tools
            </div>
            <div class="card-body">
                <p>PHP-FPM version:</p>
                <div class="input-group">
                    <select class="form-control" id="sitephpver">
                        <option value="8.0" id="php80">8.0</option>
                        <option value="7.4" id="php74">7.4</option>
                        <option value="7.3" id="php73">7.3</option>
                    </select>
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button" id="sitephpversubmit"><i class="fas fa-edit"></i></button>
                    </div>
                </div>
                <div class="space"></div>
                <p>Supervisor script:</p>
                <div class="input-group">
                    <input class="form-control" type="text" id="sitesupervisor" autocomplete="off" />
                </div>
                <div class="space"></div>
                <div class="text-center">
                    <button class="btn btn-primary" type="button" id="sitesupervisorupdate">Update <i class="fas fa-circle-notch fa-spin d-none" id="sitesupervisorupdateloading"></i></button>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
</div>
@endsection



@section('extra')
<input type="hidden" id="currentdomain">
<input type="hidden" id="server_id">
<div class="modal fade" id="repositoryModal" tabindex="-1" role="dialog" aria-labelledby="repositoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" id="repositorydialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="repositoryModalLabel">Github repository</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <label for="repositoryproject">Project</label>
                <div class="input-group">
                    <input class="form-control" type="text" id="repositoryproject" placeholder="e.g. johndoe/helloworld" autocomplete="off" />
                </div>
                <div class="space"></div>
                <label for="repositorybranch">Branch</label>
                <div class="input-group">
                    <input class="form-control" type="text" id="repositorybranch" placeholder="e.g. develop" autocomplete="off" />
                </div>
                <div class="space"></div>
                <label for="deploykey">Deploy Key (<a href="#" id="copykey">Copy</a> and add it <a href="https://github.com/settings/ssh/new" target="blank">here</a>)</label>
                <div class="input-group">
                    <textarea id="deploykey" readonly style="width:100%;height:150px;font-size:10px;"></textarea>
                </div>
                <div class="space"></div>
                <div class="text-center">
                    <button class="btn btn-primary" type="button" id="repositorysubmit">Confirm <i class="fas fa-circle-notch fa-spin d-none" id="repositoryloading"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="deployModal" tabindex="-1" role="dialog" aria-labelledby="deployModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deployModalLabel">Site deploy scripts</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Edit site deploy scripts:</p>
                <div id="deploy" style="height:250px;width:100%;"></div>
                <div class="space"></div>
                <div class="text-center">
                    <button class="btn btn-primary" type="button" id="deploysubmit">Save <i class="fas fa-circle-notch fa-spin d-none" id="deployloading"></i></button>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="sshresetModal" tabindex="-1" role="dialog" aria-labelledby="sshresetModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sshresetModalLabel">Request password reset</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure to reset site SSH password?</p>
                <div class="space"></div>
                <div class="text-center">
                    <button class="btn btn-danger" type="button" id="sshresetsubmit">Confirm <i class="fas fa-circle-notch fa-spin d-none" id="sshresetloading"></i></button>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="mysqlresetModal" tabindex="-1" role="dialog" aria-labelledby="mysqlresetModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mysqlresetModalLabel">Request password reset</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure to reset site MySql password?</p>
                <div class="space"></div>
                <div class="text-center">
                    <button class="btn btn-danger" type="button" id="mysqlresetsubmit">Confirm <i class="fas fa-circle-notch fa-spin d-none" id="mysqlresetloading"></i></button>
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
<script src="/assets/js/site.js?v=20210413"></script>
@endsection