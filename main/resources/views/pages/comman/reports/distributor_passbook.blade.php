@extends('layouts.app')
@section('title','Retailer Passbook')
@section('content')
<div class="container-fluid">
  <div class="card shadow mb-4" id="ub-card">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-white1">
          <i class="fas fa-address-card pr-2"></i>Retailer Passbook</h6>
    </div>
    <div class="card-body">
        
        <form class="form" name="date_filter_form">
            <input type="hidden" id="retailer_id" value="{{ $retailer_id }}" name="retailer_id">
            <input type="hidden" id="retailer_mobile" value="{{ $mobile }}" name="retailer_mobile">
                <div class="form-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <small>Select Date</small>
                                <div class="input-group input-group-alternative">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    </div>
                                    <input class="form-control datepicker" id="start_date" placeholder="Select date" type="text" data-date-format="dd-mm-yyyy">
                                </div>
                            </div>
                        </div>
                        <!--<div class="col-md-4">-->
                        <!--    <div class="form-group">-->
                        <!--        <small>End Date</small>-->
                        <!--        <div class="input-group input-group-alternative">-->
                        <!--            <div class="input-group-prepend">-->
                        <!--                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>-->
                        <!--            </div>-->
                        <!--            <input class="form-control datepicker" id="end_date" placeholder="Select date" type="text" data-date-format="dd-mm-yyyy">-->
                        <!--        </div>-->
                        <!--    </div>-->
                        <!--</div>-->
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
                        <th>Transaction Id</th>
                        <th>Time</th>
                        <th>Transaction Type</th>
                        <th>Amount</th>
                        <th>Clossing Balance</th>
                        <th>Credit/Debit</th>
                        <th>Status</th>
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
        "dom": 'Blfrtip',
        "buttons": [
          {
             extend: 'collection',
             text: 'Export',
             buttons: [ 'pdfHtml5', 'csvHtml5', 'copyHtml5', 'excelHtml5' ]
          }
        ],
        "ajax": {
            "url": "{{ route('get:retailer_passbook_data')}}",
            "type": 'GET',
            "data": function (d) {
                d.start_date = $('#start_date').val();
                d.end_date = $('#end_date').val();
                d.retailer_mobile = $('#retailer_mobile').val();
                d.retailer_id = $('#retailer_id').val();
                
            }
        },
        "columns": [
            { "data": "id",   
                render: function (data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;    
                }
            },
            
            // {"data": "transaction_id"}, 
            
            
            {"data": 'transaction_id', name: 'transaction_id' ,"render": function (data, type, full, meta) {
                    var event = full.event;
                    
                    if(event == 'REFUND'){
                        return data+', for-'+ full.ref_txn_id;
                    }
                    
                    return data;
                } 
            },
            
            {"data": "created_at"}, 
            {"data": 'event', name: 'event' ,"render": function (data, type, full, meta) {
                    var action = "";
                    
                    switch (data)
                    {
                        case ('QUICKDMT'):
                                action = 'Money Transfer';
                                break;
                        case ('QUICKUPI'):
                                action = 'UPI Payment';
                                break;
                        case ('CREDITVA'):
                                action = 'VA Collection';
                                break;
                        case ('UPICREDIT'):
                                action = 'UPI Collection';
                                break;
                        case ('ADDMONEY'):
                                action = 'Wallet Load';
                                break;
                        case ('FUNDADDED'):
                                action = 'Fund Load';
                                break;
                        case ('VERIFYBANKUSER'):
                                action = 'A/C Verify';
                                break;
                        case ('AEPSTXN'):
                                action = 'AePS Cash W/H';
                                break;
                        case ('DMTCOMM'):
                                action = 'Commission';
                                break;  
                        case ('OPCOMM'):
                                action = 'Commission';
                                break;  
                        case ('REFUND'):
                                action = 'Refund';
                                break; 
                        default:
                                action = data;
                                break;
                    }
                    return action;
                } 
            },
            {"data": "amount"}, 
            {"data": "final_balance"}, 
            {"data": "txn_type"},
            {"data": 'status', name: 'status' ,"render": function (data, type, full, meta) {
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
        ],
         
    });
});
</script>
@endsection