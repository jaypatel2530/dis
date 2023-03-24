@extends('layouts.app')
@section('title','Money Requests')
@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
    
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4" id="ub-card">
                <div class="card-header py-3 ">
                  <h6 class="m-0 font-weight-bold">
                      <i class="fas fa-book pr-2"></i>
                    @if($requests_for == 'super_distributors')
                        Super Distributors 
                    @elseif($requests_for == 'sd_distributors')
                        Distributors
                    @elseif($requests_for == 'distributors')
                        Distributors
                    @elseif($requests_for == 'retailers')
                        Retailers
                    @endif
                    Money Requests</h6>
                </div>
                
                <div class="card-body">
                    
                    <input type="hidden" id="requests_for" name="requests_for" value="{{ $requests_for }}">
                    
                    <!--<form class="form" name="date_filter_form">-->
                        
                    
                    <!--    <div class="form-body">-->
                    <!--        <div class="row">-->

                    <!--            <div class="col-md-5">-->
                    <!--                <div class="form-group">-->
                    <!--                    <small>Start Date</small>-->
                    <!--                    <div class="input-group input-group-alternative">-->
                    <!--                        <div class="input-group-prepend">-->
                    <!--                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>-->
                    <!--                        </div>-->
                    <!--                        <input class="form-control datepicker" id="start_date" placeholder="Select date" type="text" data-date-format="dd-mm-yyyy">-->
                    <!--                    </div>-->
                    <!--                </div>-->
                    <!--            </div>-->
            
                    <!--            <div class="col-md-5">-->
                    <!--                <div class="form-group">-->
                    <!--                    <small>End Date</small>-->
                    <!--                    <div class="input-group input-group-alternative">-->
                    <!--                        <div class="input-group-prepend">-->
                    <!--                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>-->
                    <!--                        </div>-->
                    <!--                        <input class="form-control datepicker" id="end_date" placeholder="Select date" type="text" data-date-format="dd-mm-yyyy">-->
                    <!--                    </div>-->
                    <!--                </div>-->
                    <!--            </div>-->
                                
                    <!--            <div class="col-md-2">-->
                    <!--                <div class="form-group"><br>-->
                    <!--                    <button class="form-control btn btn-primary" type="button" id="btnFiterSubmitSearch">Go</button>-->
                    <!--                </div>-->
                    <!--            </div>-->
            
                    <!--        </div>-->
                    <!--    </div>-->
                    <!--</form>-->
                    
                    <div class="print_section_center" id="amount_print_section">
                        <span class="amount_print" id="print_total_success_sum">Total Amount : <small><i class="fas fa-fw fa-rupee-sign"></i></small>0.00</span>
                    </div> 
                        
                    <div class="table-responsive">
                    <table class="table display responsive nowrap" style="width:100%" id="datatable_ajax">
                          <thead>
                            <tr role="row" class="heading">
                                <th>#</th>
                                <th>Name</th>
                                <th>Mobile</th>
                                <th>Txn ID</th>
                                <th>Time</th>
                                <th>Amount</th>
                                <th>Transfer Type</th>
                                <th>Bank</th>
                                <th>Bank Ref</th>
                                <th>Image</th>
                                
                                <th>Action</th>
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



<!--MoneyRequestModal-->
<div class="modal fade" id="money_request_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="exampleModalLabel">Confirmation</h6>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <h5>Are you sure you want to <span id="show_action_type"></span> this?</h5>  
          
        <p>Name : <span id="show_name"></span></p>
        <p>Mobile : <span id="show_mobile"></span></p>
        <p>Amount : <span id="show_amount"></span></p>
        
        <input type="hidden" name="input_action_id" id="input_action_id" val="">
        <input type="hidden" name="input_action_type" id="input_action_type" val="">
        
        <div class="row col-md-12 show_reject_input" id="show_reject_input">
            <input class="form-control mb-2" type="text" id="reject_reason" name="reject_reason" 
            class="mb-2" placeholder="Reject Reason">
        </div>
            
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-primary money_request_btn">Yes</button>
        <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">No</button>
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
        "order": [ [4, 'desc'] ],
        "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            var index = iDisplayIndexFull + 1;
            $("td:first", nRow).html(index);
            return nRow;
        },
        "buttons": [
          {
             extend: 'collection',
             text: 'Export',
             buttons: [ 'pdfHtml5', 'csvHtml5', 'copyHtml5', 'excelHtml5' ]
          }
        ],
        "ajax": {
            "url": "{{ route('get:money_requests_data')}}",
            "type": 'GET',
            "data": function (d) {
                d.start_date = $('#start_date').val();
                d.end_date = $('#end_date').val();
                d.requests_for = $('#requests_for').val();
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
            {"data": "transaction_id"}, 
            {"data": "created_at"}, 
            {"data": "amount"}, 
            {"data": "transfer_type"},
            {"data": "bank_name"},
            {"data": "bank_ref"},
            {"data": 'img', name: 'img' , "render": function (data, type, full, meta) {
            return "<img style='border-radius:unset; height:50px;width:auto;' src=\"{{ url('/uploads/money_request') }}/" + data + "\" />";
            }}, 
            
            {"data": 'money_requests_status', name: 'money_requests_status' , "sClass":".d-inline" , "render": function (data, type, full, meta) {
                var action = "";
                action += '<button class="status_change btn btn-sm btn-primary" action_id="'+full.id+'" action_type="approve" request_amount="'+full.amount+'"  user_name="'+full.name+'" mobile="'+full.mobile+'">Approve</button>&nbsp;&nbsp;';
                action += '<button class="status_change btn btn-sm btn-danger" action_id="'+full.id+'" action_type="reject"  request_amount="'+full.amount+'" user_name="'+full.name+'" mobile="'+full.mobile+'">Reject</button>';
                return action;
            } },
            
        ],
         
    });
});


$(document).on('click', '.status_change', function() { 
    
    var action_id = $(this).attr('action_id');
    var action_type = $(this).attr('action_type');
    
    var mobile = $(this).attr('mobile');
    var user_name = $(this).attr('user_name');
    var request_amount = $(this).attr('request_amount');
    
    $('#show_name').html(user_name);
    $('#show_mobile').html(mobile);
    $('#show_amount').html(request_amount);
    
    $('#show_action_type').html(action_type);
    
    $('#input_action_id').val(action_id);
    $('#input_action_type').val(action_type);
    
    if(action_type == 'reject') {
        $('#show_reject_input').show();
        $("#reject_reason").val('');
    }else{
        $('#show_reject_input').hide();
    }

    $('#money_request_modal').modal('show');
});

$(document).on('click', '.money_request_btn', function() { 
    
    var status = $("#input_action_type").val();
    var action_id = $("#input_action_id").val();
    var reject_reason = $("#reject_reason").val();
    
    $.ajax({
        type: 'post',
        url: "{{ route('post:money_requests_change_status') }}",
        data: {"status": status, "action_id" : action_id, 'reject_reason' : reject_reason,"_token" : "{{ csrf_token() }}"},
        dataType: 'json',
        success: function (data) {
            if (data.success) {
                $('#money_request_modal').modal('hide');
                showMyToast("success",data.message); 
                
                $("#input_action_type").val('');
                $("#input_action_id").val('');

                $('#datatable_ajax').DataTable().ajax.reload();
                $("#reject_reason").val('');
                $('#show_reject_input').hide();
            }
            else {
                showMyToast("error",data.message); 
                 $('#datatable_ajax').DataTable().draw(true);
            }
        }
    });
});

</script>
@endsection