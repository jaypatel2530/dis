@extends('layouts.app')
@section('title','Disputes Report')
@section('content')
<div class="container-fluid">
  <div class="card shadow mb-4" id="ub-card">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold">
          <i class="fas fa-user pr-2"></i>Disputes Report</h6>
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
                          <th>Name</th>
                          <th>Mobile</th>
                          <th>Transaction Id</th>
                          <!--<th>Transaction Status</th>-->
                          <th>Raised Time</th>
                          <th>Query</th>
                          <th>Admin Remark</th>
                          <th>Remark Time</th>
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
$('#btnFiterSubmitSearch').click(function() {
    $('#datatable_ajax').DataTable().ajax.reload();
});
$(document).ready(function() {
    oTable = $('#datatable_ajax').DataTable({
        "processing": false,
        "serverside": true,
        "dom": 'lfrtip',
        "ajax": {
            "url": "{{ route('get:disputes_report')}}",
              "type": 'GET',
              "data": function (d) {
                  d.json_data = 'json_data';
                  d.start_date = $('#start_date').val();
                  d.end_date = $('#end_date').val();
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
            {"data": "transaction_id"},
            // {"data": 'txn_status', name: 'txn_status' ,"render": function (data, type, full, meta) {
            //         var action = "";
                    
            //         switch (data)
            //         {
            //             case (0):
            //                     action = '<h6><span class="badge badge-warning">Processing</span></h6>';
            //                     break;
            //             case ('3'):
            //                     action = '<h6><span class="badge badge-primary">Refunded</span></h6>';
            //                     break;
            //             case ('2'):
            //                     action = '<h6><span class="badge badge-danger">Failed</span></h6>';
            //                     break;
            //             case ('1'):
            //                     action = '<h6><span class="badge badge-success">Completed</span></h6>';
            //                     break;
            //             default:
            //                     action = data
            //                     break;
            //         }
            //         return action;
            //     } 
            // },
            {"data": "created_at"},
            {"data": "reason"},
            {"data": "admin_remark"},
            {"data": "remark_time"},
            // {"data": 'id', name: 'id' ,"render": function (data, type, full, meta) {
            //     var action = "";
            //     action += '<button class="dispute_reply btn btn-sm btn-primary" action_id="'+full.id+'" action_type="approve"  user_name="'+full.name+'" mobile="'+full.mobile+'" reason="'+full.reason+'">Reply</button>&nbsp;&nbsp;';
            //     return action;
            // } },
            
        ],
         
    });
});
</script>
@endsection