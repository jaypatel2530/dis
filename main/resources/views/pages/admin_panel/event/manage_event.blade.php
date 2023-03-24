@extends('layouts.app')
@section('title','Manage Event')
@section('content')
<div class="container-fluid">
  <div class="card shadow mb-4" id="ub-card">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-white1">
          <i class="fas fa-user pr-2"></i>Manage Event</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table display responsive nowrap" style="width:100%" id="datatable_ajax">
                  <thead>
                      <tr role="row" class="heading">
                          <th>#</th>
                          <th>Name</th>
                          <th>Venue</th>
                          <th>Date</th>
                          
                          <th>Action</th>
                          
                      </tr>
                  </thead>
                  <tbody></tbody>
              </table>
          </div>
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
        "dom": 'Blfrtip',
        "buttons": [{
            extend: 'collection',
            text: 'Export',
            buttons: [ 'pdfHtml5', 'csvHtml5', 'copyHtml5', 'excelHtml5' ]
        }],
        "ajax": {
            "url": "{{ route('get:manage_event_data')}}",
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
            {"data": "venue"},
            
            {"data": "event_date"},
            
            { "data": 'id', "name": 'id', "sClass": "" , "render": function (data, type, full, meta) {
                var action = "";
                action += '<button style="white-space: nowrap;" title="Delete" type="button" class="btn btn-danger btn-sm change-status" user_id="'+full.id+'">Delete</button>&nbsp;';
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
            url: '{{ route("post:delete_event") }}',
            data: {"event_id" : user_id,"_token":"{{ csrf_token() }}"},
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
$(document).on('click', '.wallet-load', function () {
    var user_id = $(this).attr('user_id');
    $('#wallet_user_id').val(user_id);
    $('#loadWalletModal').modal('show');
});
</script>
@endsection