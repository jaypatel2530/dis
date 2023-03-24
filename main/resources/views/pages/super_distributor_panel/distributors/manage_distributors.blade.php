@extends('layouts.app')
@section('title','Manage Distributors')
@section('content')
<div class="container-fluid">
  <div class="card shadow mb-4" id="ub-card">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold">
          <i class="fas fa-user pr-2"></i>Manage Distributors</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table display responsive nowrap" style="width:100%" id="datatable_ajax">
                  <thead>
                      <tr role="row" class="heading">
                          <th>#</th>
                          <th>Name</th>
                          <th>Phone</th>
                          <th>Wallet</th>
                          <th>City</th>
                          <th>State</th>
                          <th>Status</th>
                          <th>KYC Status</th>
                          <th>Passbook</th>
                          @if(Auth::User()->user_type == 1) 
                          <th>UPI Request Service</th>
                          @endif
                      </tr>
                  </thead>
                  <tbody></tbody>
              </table>
          </div>
    </div>
  </div>
</div>
@endsection
@section('customjs')
<script>
$(document).ready(function() {
    oTable = $('#datatable_ajax').DataTable({
        "processing": false,
        "serverside": true,
        "dom": 'lfrtip',
        "ajax": {
            "url": "{{ route('get:sd_manage_distributors_data')}}",
              "type": 'GET',
              data: function (d) {
              }
        },
        "columns": [
            { "data": "id",   
                render: function (data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;    
                }
            },
            
            {"data": "name"},           
            {"data": "mobile"},
            {"data": "wallet"},
            {"data": "cityname"},
            {"data": "statename"},
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
            {"data": 'kyc_status', name: 'kyc_status' ,"render": function (data, type, full, meta) {
                    var action = "";
                    
                    switch (data)
                    {
                        case (null):
                                action = '<h6><span class="badge badge-primary">Not uploaded</span></h6>';
                                break;
                        case ("0"):
                                action = '<h6><span class="badge badge-warning">Under process</span></h6>';
                                break;
                        case ("1"):
                                action = '<h6><span class="badge badge-success">Active</span></h6>';
                                break;
                        case ("2"):
                                action = '<h6><span class="badge badge-danger">Rejected</span></h6>';
                                break;
                        default:
                                action = data
                                break;
                    }
                    return action;
                } 
            },
            { "data": 'id', "name": 'id', "sClass": "" , "render": function (data, type, full, meta) {
                var action = "";
                action += '<a href={{url("passbook/distributor-passbook")}}/' + full.mobile + '  class="btn btn-success btn-sm" title="Passbook">Passbook</a>&nbsp;';
                return action;    }
            },
            
            @if(Auth::User()->user_type == 1) 
            {"data": 'upi_request_service', name: 'upi_request_service' ,"render": function (data, type, full, meta) {
                var action = "";
                if(data==1)
                    action += '<button title="Click to change status" type="button" class="btn btn-success btn-sm change-status upirequestbtn'+full.id+'" service="upi_request_service" status="'+data+'"  user_id="'+full.id+'" id="upirequestbtn'+full.id+'">Active</button>';
                else
                    action += '<button title="Click to change status" type="button" class="btn btn-danger btn-sm change-status upirequestbtn'+full.id+'" service="upi_request_service" status="'+data+'" user_id="'+full.id+'" id="upirequestbtn'+full.id+'">Deactive</button>';
                
                return action;
            } },
            @endif
        ],
         
    });
});

$(document).on('click', '.change-status', function () {
    var status = $(this).attr('status');
    var user_id = $(this).attr('user_id');
    var btnid = $(this).attr('id');
    var service = $(this).attr('service');
    if(status){
        $.ajax({
            type: 'post',
            dataType: "json",
            url: '{{ route("post:change_service_status") }}',
            data: {"user_id" : user_id, "status" : status ,"service" : service ,"_token":"{{ csrf_token() }}"},
            success: function (result) {
                if(result.success) {
                    if(result.status==1) {
                        $('.'+btnid).attr('status', result.status);
                        $('.'+btnid).removeClass('btn-danger');
                        $('.'+btnid).addClass('btn-success');
                        $('.'+btnid).html('Active');
                    }
                    else {
                        $('.'+btnid).attr('status', result.status);
                        $('.'+btnid).removeClass('btn-success');
                        $('.'+btnid).addClass('btn-danger');
                        $('.'+btnid).html('Deactive');
                    }
                    showMyToast('success', result.message);
                }
                else {
                    showMyToast('error', result.message);
                }
            }
        })
    }
});
</script>
@endsection