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
            <table class="table display responsive nowrap" style="width:100%" id="datatable_ajax1">
                  <thead>
                      
                      <tr role="row" class="heading">
                          <th>#</th>
                          <th>Name</th>
                          <th>Phone</th>
                          <th>email</th>
                          <th>Relation</th>
                          <th>Profession</th>
                          <th>gender</th>
                          <th></th>
                      </tr>
                  </thead>
                  <tbody>
                      
                      <tr>
                          <td>
                              *
                          </td>
                          <td>{{ Auth::User()->name }}</td>
                          <td>{{ Auth::User()->mobile }}</td>
                          <td>{{ Auth::User()->email }}</td>
                          <td>{{ Auth::User()->relation_with_user }}</td>
                          <td>{{ Auth::User()->profession }}</td>
                          <td>{{ Auth::User()->gender }}</td>
                          <th><a href=' {{url("retailers/edit-retailer")}}/{{ Auth::User()->mobile }}'  class="btn btn-success btn-sm" tooltip="Edit" title="Edit"><i class="fas fa-edit"></i></a>&nbsp;</th>
                      </tr>
                     
                  </tbody>
              </table>
          </div>
          @if(Auth::user()->membership == 'Family')
          <h6 class="m-0 font-weight-bold text-white1">
          <i class="fas fa-users pr-2"></i>Family Members</h6>
        <div class="table-responsive">
            <table class="table display responsive nowrap" style="width:100%" id="datatable_ajax">
                  <thead>
                      <tr role="row" class="heading">
                          <th>#</th>
                          <th>Name</th>
                          <th>Phone</th>
                          <th>Email</th>
                          
                          <th>Profession</th>
                          <th>Gender</th>
                          
                          <th>Relation</th>
                          
                          <th>Action</th>
                          
                      </tr>
                  </thead>
                  <tbody></tbody>
              </table>
          </div>
          @endif
    </div>
  </div>
</div>
@include('pages.load_wallet_model')
@endsection
@section('customjs')
<script>
$(document).ready(function() {
    oTable = $('#datatable_ajax').DataTable({
        "processing": false,
        "serverside": true,
        "responsive": true,
        "searching": false, 
        "paging": false,
        "dom": 'lfrtip',
        "ajax": {
            "url": "{{ route('get:manage_member_data')}}",
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
            
            {"data": "email"},
            
            {"data": "profession"},
            {"data": "gender"},
            {"data": "relation_with_user"},
            { "data": 'id', "name": 'id', "sClass": "" , "render": function (data, type, full, meta) {
                var action = "";
                action += '<button style="white-space: nowrap;" title="Delete" type="button" class="btn btn-danger btn-sm change-status" user_id="'+full.id+'">Delete</button>&nbsp;';
                action += '<a href={{url("member/edit-member")}}/'+data+'{{ date("Ymd") }}  class="btn btn-success btn-sm" tooltip="Edit" title="Edit"><i class="fas fa-edit"></i></a>&nbsp';
                return action;    }
            },
            
            
            
            
            
        ],
    });
});

$(document).on('click', '.change-status', function () {
    
    var user_id = $(this).attr('user_id');
    if(user_id){
        $.ajax({
            type: 'post',
            dataType: "json",
            url: '{{ route("post:delete_member") }}',
            data: {"user_id" : user_id,"_token":"{{ csrf_token() }}"},
            success: function (result) {
                if(result.success) {
                    $('#datatable_ajax').DataTable().ajax.reload();
                    showMyToast('success', result.message);
                }
                else {
                    $('#datatable_ajax').DataTable().ajax.reload();
                    showMyToast('error', result.message);
                }
            }
        })
    }
});

$(document).on('click', '.change-status-2', function () {
  var service = $(this).attr('service');
  var status = $(this).attr('status');
  $('#change_status').val(status);
  $('#change_user_id').val($(this).attr('user_id'));
  $('#change_btnid').val($(this).attr('id'));
  $('#change_service').val(service);

  if(service == 'credit_status' && status == 0){
    htmlCreditLimit = '<small>Credit Wallet Limit</small>';
    htmlCreditLimit += '<div class="input-group input-group-alternative">';
    htmlCreditLimit += '<input class="form-control"  placeholder="Credit Wallet" type="text" name="credit_wallet"';
    htmlCreditLimit += 'autocomplete="off" required></div>';
    $('.creditLimit').html(htmlCreditLimit);
  }else{
    $('.creditLimit').html('');
  }
  $('#authentication_password').modal('show');
});

$(document).on('click', '.wallet-load', function () {
    var user_id = $(this).attr('user_id');
    $('#wallet_user_id').val(user_id);
    $('#loadWalletModal').modal('show');
});
</script>
@endsection