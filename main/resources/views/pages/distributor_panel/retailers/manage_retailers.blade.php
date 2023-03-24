@extends('layouts.app')
@section('title','Manage Members')
@section('content')
<div class="container-fluid">
  <div class="card shadow mb-4" id="ub-card">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-white1">
          <i class="fas fa-user pr-2"></i>Manage Members</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table display responsive nowrap" style="width:100%" id="datatable_ajax">
                  <thead>
                      <tr role="row" class="heading">
                          <th>#</th>
                          <th>Membership ID</th>
                          <th>Name</th>
                          <th>Phone</th>
                          
                          <th>Email</th>
                          <th>Postcode</th>
                          
                          <th>Gender</th>
                          <th>Registration Date</th>
                          <th>Renewal Date</th>
                          <th>Fee</th>
                          <th>Membership</th>
                          <th>Membership Duration</th>
                          <th>Payment Check</th>
                          <th>View Details</th>
                          <th></th>
                          <th>Delete</th>
                      </tr>
                  </thead>
                  <tbody></tbody>
              </table>
          </div>
    </div>
  </div>
</div>
@include('pages.load_wallet_model')
@include('pages.view_member')
@endsection
@section('customjs')
<script>
$(document).ready(function() {
    oTable = $('#datatable_ajax').DataTable({
        "processing": false,
        "serverside": true,
        "responsive": true,
        "dom": 'Blfrtip',
        "buttons": [{
            extend: 'collection',
            text: 'Export',
            buttons: [ 'pdfHtml5', 'csvHtml5', 'copyHtml5', 'excelHtml5' ]
        }],
        "ajax": {
            "url": "{{ route('get:manage_retailer_data')}}",
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
            {"data": "member_id"},  
            {"data": "name"},           
            {"data": "mobile"},
            
            {"data": "email"},
            {"data": 'postcode', name: 'postcode' ,"render": function (data, type, full, meta) {
                    return data.toUpperCase();
                }
            },
            {"data": "gender"},
            {"data": 'created_at', name: 'created_at' ,"render": function (data, type, full, meta) {
                    return moment(data).format('DD/MM/YYYY');
                }
            },
            {"data": 'membership_date', name: 'membership_date' ,"render": function (data, type, full, meta) {
                    return moment(data).format('DD/MM/YYYY');
                }
            },
            {"data": 'membership_year', name: 'membership_year' ,"render": function (data, type, full, meta) {
                    var fee = 0;
                    if(full.membership == 'Family'){
                        fee = data * 15;
                    }else{
                        fee = data * 7;
                    }
                    return '£'+fee;
                }
            },
            {"data": "membership"},
            {"data": "membership_year"},
            
            
            
            {"data": 'payment_status', name: 'payment_status' ,"render": function (data, type, full, meta) {
                var action = "";
                if(data==1)
                    action += '<button title="Click to change status" type="button" class="btn btn-success btn-sm change-status payment_status'+full.id+'" service="payment_status" status="'+data+'"  user_id="'+full.id+'" id="payment_status'+full.id+'">Paid</button>';
                else
                    action += '<button title="Click to change status" type="button" class="btn btn-danger btn-sm change-status payment_status'+full.id+'" service="payment_status" status="'+data+'" user_id="'+full.id+'" id="payment_status'+full.id+'">UnPaid</button>';
                return action;

            } },
            
            {"data": 'membership', name: 'membership' ,"render": function (data, type, full, meta) {
                var action = "";
                if(data == "Family"){
                   action += '<button title="View Member" type="button" class="btn btn-success btn-sm member-view member_list'+full.id+'" status="'+data+'"  user_id="'+full.id+'" id="member_list'+full.id+'"><i class="fas fa-users"></i></button>';
                 
                }
                return action;

            } },
            { "data": 'mobile', "name": 'mobile', "sClass": "" , "render": function (data, type, full, meta) {
                var action = "";
                action += '<a href={{url("retailers/edit-retailer")}}/'+data+'  class="btn btn-success btn-sm" tooltip="Edit" title="Edit"><i class="fas fa-edit"></i></a>&nbsp';
                return action;    }
            },
            
            {"data": 'status', name: 'status' ,"render": function (data, type, full, meta) {
                var action = "";
                if(data==1)
                    action += '<button title="Click to change status" type="button" class="btn btn-success btn-sm change-status-2 status'+full.id+'" service="status" status="'+data+'"  user_id="'+full.id+'" id="status'+full.id+'">Delete</button>';
                else
                    action += '';
                return action;

            } },
            
            
            
        ],
    });
});

function loadMembers(user_id){
    $.ajax({
            type: 'post',
            dataType: "html",
            url: '{{ route("post:view_member") }}',
            data: {"user_id" : user_id,"_token":"{{ csrf_token() }}"},
            success: function (result) {
                
                $('#member_show_list').html(result);
                $('#ViewMemberModal').modal('show');
            }
        })
}

$(document).on('click', '.member-view', function () {
  var user_id = $(this).attr('user_id');
  $('#ViewMemberModal').modal('show');
  loadMembers(user_id);
  

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
                        $('.'+btnid).html('Paid');
                        showMyToast('success', 'Payment Status paid');
                    }
                    else {
                        $('.'+btnid).attr('status', result.status);
                        $('.'+btnid).removeClass('btn-success');
                        $('.'+btnid).addClass('btn-danger');
                        $('.'+btnid).html('UnPaid');
                        showMyToast('success', 'Payment Status unpaid');
                    }
                    $('#datatable_ajax').DataTable().ajax.reload();
                }
                else {
                    showMyToast('error', result.message);
                }
            }
        })
    }
});

$(document).on('click', '.change-status-2', function () {
    let text = "Are you sure you want to delete this user?";
    if (confirm(text) == true) {
        
    } else {
        return false;
    }
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
                        $('.'+btnid).html('Deleted');
                    }
                    $('#datatable_ajax').DataTable().ajax.reload();
                    showMyToast('success', 'User Delete.');
                }
                else {
                    showMyToast('error', result.message);
                }
            }
        })
    }
});


$(document).on('click', '.wallet-load', function () {
    var user_id = $(this).attr('user_id');
    $('#wallet_user_id').val(user_id);
    $('#loadWalletModal').modal('show');
});

$(document).on('click', '.change-status-member', function () {
    
    var user_id = $(this).attr('user_id');
    if(user_id){
        $.ajax({
            type: 'post',
            dataType: "json",
            url: '{{ route("post:delete_member") }}',
            data: {"user_id" : user_id,"_token":"{{ csrf_token() }}"},
            success: function (result) {
                if(result.success) {
                    
                    showMyToast('success', result.message);
                    loadMembers(user_id);
                }
                else {
                    
                    showMyToast('error', result.message);
                    loadMembers(user_id);
                }
            }
        })
    }
});
</script>
@endsection