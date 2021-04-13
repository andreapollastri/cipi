 //Get DT Data 
 getData('/api/sites');

 //Datatable
 function dtRender() {
     $('#dt').DataTable( {
         'processing': true,
         'data': JSON.parse(localStorage.getItem('dtdata')),
         'columns': [
             { data: 'domain' },
             { data: 'aliases' },
             { data: 'server_name' },
             { data: 'server_ip' },
             { data: {
                 'site_id': 'site_id',
                 'domain': 'domain',
             }}
         ],
         'columnDefs': [
             {
                 'targets': 1,
                 'className': 'd-none d-md-table-cell text-center',
             },
             {
                 'targets': 2,
                 'className': 'text-center d-none d-lg-table-cell',
             },
             {
                 'targets': 3,
                 'className': 'text-center d-none d-xl-table-cell',
             },
             {
                 'targets': 4,
                 'className': 'text-center',
                 'render': function ( data, type, row, meta ) {
                     return '<button data-id="'+data['site_id']+'" class="btmanage btn btn-sm btn-primary mr-3"><i class="fas fa-cog fa-fw"></i> <b class="d-none d-sm-inline">Manage</b></button><button data-id="'+data['site_id']+'" data-name="'+data['domain']+'" class="btdelete btn btn-sm btn-danger"><i class="fas fa-times fa-fw"></i> <b class="d-none d-sm-inline">Delete</b></button>';
                 }
             }
         ],
         'bLengthChange': false,
         'bAutoWidth': true,
         'responsive': true,
         'drawCallback': function(settings) {
             //Manage Site
             $(".btmanage").click(function() {
                 window.location.href = '/sites/'+$(this).attr('data-id');
             });
             //Delete Site
             $(".btdelete").click(function() {
                 siteDelete($(this).attr('data-id'),$(this).attr('data-domain'));
             });
         }
     });
 }

 //Delete Site
 function siteDelete(site_id,domain) {
     $('#deletesiteid').val(site_id);
     $('#deletesitedomain').html(domain);
     $('#deleteSiteModal').modal();
     $('#delete').click(function() {
         $.ajax({
             url: '/api/sites/'+$('#deletesiteid').val(),
             type: 'DELETE',
             contentType: 'application/json',
             dataType: 'json',
             beforeSend: function() {
                 $('#loadingdelete').removeClass('d-none');
             },
             complete: function(data) {
                 setTimeout(function() {
                     $('#dt').DataTable().clear().destroy();
                 }, 4500);
                 setTimeout(function() {
                     getData('/api/sites',false);
                 }, 6000);
                 setTimeout(function() {
                     $('#deleteSiteModal').modal('toggle');
                     $('#deletesitedomain').html('');
                     $('#deletesiteid').val('');
                     $('#loadingdelete').addClass('d-none');
                 }, 6500);
             },
         });
     });
 }

 //Auto Update List
 setInterval(function() {
     $('#dt').DataTable().clear().destroy();
     getData('/api/sites',false);
 }, 45000);

 //Get server domains
 $('#newsiteserver').change(function() {
     getDataNoDT('/api/servers/'+$('#newsiteserver').val()+'/domains');
 });

 //Check Domain Conflict
 function domainConflict(domain) {
     conflict = 0;
     JSON.parse(localStorage.otherdata).forEach(item => {
         if(item == domain) {
             conflict = conflict + 1;
         }
     });
     return conflict;
 }

 //Server list
 function getServers() {
     $('#newsiteserver').empty();
     $.ajax({
         type: 'GET',
         url: '/api/servers',
         success: function(data) {
             data.forEach(server => {
                 if(server.status) {
                     if(server.default) {
                         $('#newsiteserver').append('<option value="'+server.server_id+'" selected>'+server.name+' ('+server.ip+')</option>');
                         getDataNoDT('/api/servers/'+server.server_id+'/domains');
                     } else {
                         $('#newsiteserver').append('<option value="'+server.server_id+'">'+server.name+' ('+server.ip+')</option>');
                     }
                 }
             });
         }
     });
 }
 getServers();

 //New Site
 $('#newSite').click(function() {
     $('#loading').addClass('d-none');
     $('#newsiteform').removeClass('d-none');
     $('#newsiteok').addClass('d-none');
     $('#newsiteip').html();
     $('#newsiteusername').html();
     $('#newsitepassword').html();
     $('#newsitedbname').html();
     $('#newsitedbusername').html();
     $('#newsitedbpassword').html();
     $('#newsitebasepathuser').html();
     $('#newsitebasepath').html();
     $('#newsitedomainok').html();
     $('#newsitepdf').attr('href','#');
     $('#newSiteModal').modal();
 });

 //New Site Validation
 $('#newsitedomain').keyup(function() {
     $('#newsitedomain').removeClass('is-invalid');
     $('#submit').removeClass('disabled');
 });

 //New Site Submit
 $('#submit').click(function() {
     validation = true;
     if(!$('#newsitedomain').val() || $('#newsitedomain').val().length < 5 || domainConflict($('#newsitedomain').val()) > 0) {
         $('#newsitedomain').addClass('is-invalid');
         $('#submit').addClass('disabled');
         validation = false;
     }
     if(validation) {
         $.ajax({
             url: '/api/sites',
             type: 'POST',
             contentType: 'application/json',
             dataType: 'json',
             data: JSON.stringify({
                 'domain':   $('#newsitedomain').val(),
                 'server_id':$('#newsiteserver').val(),
                 'php':      $('#newsitephp').val(),
                 'basepath': $('#newsitebasepath').val()
             }),
             beforeSend: function() {
                 $('#loading').removeClass('d-none');
             },
             success: function(data) {
                 $('#dt').DataTable().clear().destroy();
                 getData('/api/sites',false);
                 $('#loading').addClass('d-none');
                 $('#newsiteip').html(data.server_ip);
                 $('#newsiteusername').html(data.username);
                 $('#newsitepassword').html(data.password);
                 $('#newsitedbname').html(data.database);
                 $('#newsitedbusername').html(data.database_username);
                 $('#newsitedbpassword').html(data.database_password);
                 $('#newsitebasepathuser').html(data.username);
                 $('#newsitebasepath').html(data.basepath);
                 $('#newsitedomainok').html(data.domain);
                 $('#newsitepdf').attr('href',data.pdf);
                 $('#newsiteform').addClass('d-none');
                 $('#newsiteok').removeClass('d-none');
                 $('#newsitedomain').val('');
                 $('#newsitephp').val('8.0');
                 $('#newsitebasepath').val('');
                 getServers();
             },
         });
     }
 });