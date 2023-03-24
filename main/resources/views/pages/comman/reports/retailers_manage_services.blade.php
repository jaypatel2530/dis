@extends('layouts.app')
@section('title','Retailers Manage Services')
@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4" id="ub-card">
                <div class="card-header py-3 ">
                  <h6 class="m-0 font-weight-bold">
                      <i class="fas fa-book pr-2"></i>Retailers Manage Services</h6>
                </div>
                <div class="card-body">
                    
                    <div class="table-responsive">
                        <table class="table display responsive nowrap" style="width:100%" id="datatable_ajax">
                              <thead>
                                  <tr role="row" class="heading">
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Mobile</th>
                                    <th>Status</th>
                                    <th>UPI Status</th>
                                    <th>QR Status</th>
                                    <th>VA Status</th>
                                    <th>EKO Status</th>
                                    <th>DMT Service</th>
                                    <th>Recharge Service</th>
                                  </tr>
                              </thead>
                              <tbody></tbody>
                          </table>
                      </div>
                </div>
            </div>          
        </div>
    </div>
</div>
@endsection
@section('customjs')
<script>

$('#btnFiterSubmitSearch').click(function() {
    $('#datatable_ajax').DataTable().ajax.reload();
});

$(document).ready(function() {
    oTable = $('#datatable_ajax').DataTable({
        "processing": false,
        "serverside": true,
        "dom": 'Blfrtip',
        "buttons": [{
             extend: 'collection',
             text: 'Export',
             buttons: [ 'pdfHtml5', 'csvHtml5', 'copyHtml5', 'excelHtml5' ]
        }],
        "ajax": {
            "url": "{{ route('get:retailers_manage_services')}}",
            "type": 'GET',
            "data": function (d) {
                d.start_date = $('#start_date').val();
                d.end_date = $('#end_date').val();
                d.json_data = 'json_data';
            },
            "dataSrc": function (json) {
                $("#print_total_success_sum").html('Total Amount : <small><i class="fas fa-fw fa-rupee-sign"></i></small>'+parseFloat(json.total_success_sum).toFixed(2));
                return json.data;
            },
        },
        "columns": [
            { "data": "id",   
                render: function (data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;    
                }
            },
            {"data": "name"}, 
            {"data": "mobile"},
            {"data": 'status', name: 'status' ,"render": function (data, type, full, meta) {
                    var action = "";
                    
                    switch (data)
                    {
                        case (0):
                                action = '<h6><span class="badge badge-danger">Disabled</span></h6>';
                                break;
                        case ('1'):
                                action = '<h6><span class="badge badge-success">Enabled</span></h6>';
                                break;
                        default:
                                action = data
                                break;
                    }
                    return action;
                } 
            },
            
            {"data": 'upi_status', name: 'upi_status' ,"render": function (data, type, full, meta) {
                    var action = "";
                    
                    switch (data)
                    {
                        case (0):
                                action = '<h6><span class="badge badge-danger">Disabled</span></h6>';
                                break;
                        case ('1'):
                                action = '<h6><span class="badge badge-success">Enabled</span></h6>';
                                break;
                        default:
                                action = data
                                break;
                    }
                    return action;
                } 
            },
            
            {"data": 'qr_status', name: 'qr_status' ,"render": function (data, type, full, meta) {
                    var action = "";
                    
                    switch (data)
                    {
                        case (0):
                                action = '<h6><span class="badge badge-danger">Disabled</span></h6>';
                                break;
                        case ('1'):
                                action = '<h6><span class="badge badge-success">Enabled</span></h6>';
                                break;
                        default:
                                action = data
                                break;
                    }
                    return action;
                } 
            },
            
            {"data": 'va_status', name: 'va_status' ,"render": function (data, type, full, meta) {
                    var action = "";
                    
                    switch (data)
                    {
                        case (0):
                                action = '<h6><span class="badge badge-danger">Disabled</span></h6>';
                                break;
                        case ('1'):
                                action = '<h6><span class="badge badge-success">Enabled</span></h6>';
                                break;
                        default:
                                action = data
                                break;
                    }
                    return action;
                } 
            },
            
            {"data": 'eko_status', name: 'eko_status' ,"render": function (data, type, full, meta) {
                    var action = "";
                    
                    
                    
                    switch (data)
                    {
                        case (0):
                                action = '<h6><span class="badge badge-danger">Disabled</span></h6>';
                                break;
                        case ('1'):
                                action = '<h6><span class="badge badge-success">Enabled</span></h6>';
                                break;
                        case ('2'):
                                action = '<h6><span class="badge badge-warning">Processing</span></h6>';
                                break;
                        case ('3'):
                                action = '<h6><span class="badge badge-danger">Rejected</span></h6>';
                                break;
                        case ('4'):
                                action = '<h6><span class="badge badge-info">Service not activated</span></h6>';
                                break;
                        default:
                                action = data
                                break;
                    }
                    return action;
                } 
            },
            
            {"data": 'dmt_service', name: 'dmt_service' ,"render": function (data, type, full, meta) {
                    var action = "";
                    
                    switch (data)
                    {
                        case (0):
                                action = '<h6><span class="badge badge-danger">Disabled</span></h6>';
                                break;
                        case ('1'):
                                action = '<h6><span class="badge badge-success">Enabled</span></h6>';
                                break;
                        default:
                                action = data
                                break;
                    }
                    return action;
                } 
            },
            
            {"data": 'recharge_service', name: 'recharge_service' ,"render": function (data, type, full, meta) {
                    var action = "";
                    
                    switch (data)
                    {
                        case (0):
                                action = '<h6><span class="badge badge-danger">Disabled</span></h6>';
                                break;
                        case ('1'):
                                action = '<h6><span class="badge badge-success">Enabled</span></h6>';
                                break;
                        default:
                                action = data
                                break;
                    }
                    return action;
                } 
            },
        
        ],
         
    });
});
</script>
@endsection