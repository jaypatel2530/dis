@extends('layouts.app')
@section('title','Manage Attendance')
@section('content')
<div class="container-fluid">
  <div class="card shadow mb-4" id="ub-card">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-white1">
          <i class="fas fa-users pr-2"></i>Manage Attendance</h6>
    </div>
    <div class="card-body">
        <form class="form" name="date_filter_form">
                        <div class="form-body">
                            <div class="row">

                                <div class="col-md-5">
                                    <div class="form-group">
                                        <small>Start Date</small>
                                        <div class="input-group input-group-alternative">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                            </div>
                                            <input class="form-control datepicker" id="start_date" placeholder="Select date" type="text" data-date-format="dd-mm-yyyy">
                                        </div>
                                    </div>
                                </div>
            
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <small>End Date</small>
                                        <div class="input-group input-group-alternative">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                            </div>
                                            <input class="form-control datepicker" id="end_date" placeholder="Select date" type="text" data-date-format="dd-mm-yyyy">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-2">
                                    <div class="form-group"><br>
                                        <button class="form-control btn btn-primary" type="button" id="btnFiterSubmitSearch">Go</button>
                                    </div>
                                </div>
            
                            </div>
                        </div>
                    </form>
        <div class="table-responsive">
            <table class="table display responsive nowrap" style="width:100%" id="datatable_ajax">
                  <thead>
                      <tr role="row" class="heading">
                          <th>#</th>
                          <th>Event Name</th>
                          <th>Member Name</th>
                          <th>Date</th>
                          
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
$('#btnFiterSubmitSearch').click(function() {
    $('#datatable_ajax').DataTable().ajax.reload();
});


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
            "url": "{{ route('get:event_attendance_data')}}",
              "type": 'GET',
              "data": function (d) {
                d.start_date = $('#start_date').val();
                d.end_date = $('#end_date').val();
            },
        },
        "columns": [
            { "data": "id",   
                render: function (data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;    
                }
            },
            {"data": "event_name"},           
            {"data": "user_name"},
            
            {"data": "entry_date"},
            
            
            
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