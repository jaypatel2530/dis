@extends('layouts.app')
@section('title','Manage Pending Disputes')
@section('content')
<div class="container-fluid">
  <div class="card shadow mb-4" id="ub-card">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold">
          <i class="fas fa-user pr-2"></i>Manage Pending Disputes</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table display responsive nowrap" style="width:100%" id="datatable_ajax">
                  <thead>
                      <tr role="row" class="heading">
                          <th>#</th>
                          <th>Name</th>
                          <th>Mobile</th>
                          <th>Transaction Id</th>
                          <th>Transaction Status</th>
                          <th>Raised Time</th>
                          <th>Query</th>
                          <th>Reply</th></th>
                      </tr>
                  </thead>
                  <tbody></tbody>
              </table>
          </div>
    </div>
  </div>
</div>

<!--KYC -Confirmation -->
<div class="modal fade" id="reply_comfirmation" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="exampleModalLabel">Confirmation</h6>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <h5>Reply to disputed message</h5>  
          
        Name : <span id="show_name"></span></br>
        Mobile : <span id="show_mobile"></span></br>
        Query : <span id="show_reason"></span></br></br>
        
        <input type="hidden" name="input_action_id" id="input_action_id" val="">
        
        <div class="row col-md-12">
            <input class="form-control mb-2" type="text" id="admin_remark" name="admin_remark" 
            class="mb-2" placeholder="Remark">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-primary reply_submit_btn">Yes</button>
        <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">No</button>
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
            "url": "{{ route('get:manage_pending_disputes')}}",
              "type": 'GET',
              "data": function (d) {
                  d.json_data = 'json_data';
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
            {"data": 'txn_status', name: 'txn_status' ,"render": function (data, type, full, meta) {
                    var action = "";
                    
                    switch (data)
                    {
                        case (0):
                                action = '<h6><span class="badge badge-warning">Processing</span></h6>';
                                break;
                        case ('3'):
                                action = '<h6><span class="badge badge-primary">Refunded</span></h6>';
                                break;
                        case ('2'):
                                action = '<h6><span class="badge badge-danger">Failed</span></h6>';
                                break;
                        case ('1'):
                                action = '<h6><span class="badge badge-success">Completed</span></h6>';
                                break;
                        default:
                                action = data
                                break;
                    }
                    return action;
                } 
            },
            {"data": "created_at"},
            {"data": "reason"},
            {"data": 'id', name: 'id' ,"render": function (data, type, full, meta) {
                var action = "";
                action += '<button class="dispute_reply btn btn-sm btn-primary" action_id="'+full.id+'" action_type="approve"  user_name="'+full.name+'" mobile="'+full.mobile+'" reason="'+full.reason+'">Reply</button>&nbsp;&nbsp;';
                return action;
            } },
            
        ],
         
    });
});

$(document).on('click', '.dispute_reply', function() { 
    var action_id = $(this).attr('action_id');
    var reason = $(this).attr('reason');
    var mobile = $(this).attr('mobile');
    var user_name = $(this).attr('user_name');
    $('#show_name').html(user_name);
    $('#show_mobile').html(mobile);
    $('#show_reason').html(reason);
    $('#input_action_id').val(action_id);
    $('#reply_comfirmation').modal('show');
});

$(document).on('click', '.reply_submit_btn', function() {
    var dispute_id = $("#input_action_id").val();
    var admin_remark = $("#admin_remark").val();
    $.ajax({
        type: 'post',
        url: "{{ route('post:reply_to_dispute') }}",
        data: {"dispute_id" : dispute_id,'admin_remark' : admin_remark,"_token" : "{{ csrf_token() }}"},
        dataType: 'json',
        success: function (data) {
            if (data.status) {
                $('#reply_comfirmation').modal('hide');
                $("#input_action_id").val('');
                $("#admin_remark").val('');
                $('#datatable_ajax').DataTable().ajax.reload();
                showMyToast("success",data.message); 
            }
            else {
                $('#datatable_ajax').DataTable().ajax.reload();
                showMyToast("error",data.message);
            }
        }
    });
});
</script>
@endsection