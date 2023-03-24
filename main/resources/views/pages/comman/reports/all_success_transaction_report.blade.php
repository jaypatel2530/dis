@extends('layouts.app')
@section('title','All Success Transactions Report')
@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4" id="ub-card">
                <div class="card-header py-3 ">
                  <h6 class="m-0 font-weight-bold">
                      <i class="fas fa-book pr-2"></i>All Success Transactions Report</h6>
                </div>
                <div class="card-body">
                    <form class="form" name="date_filter_form">
                        <div class="form-body">
                            <div class="row">

                                <div class="col-md-4">
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
                                
                                <!--<div class="col-md-4">-->
                                <!--    <div class="form-group">-->
                                <!--        <small>Status</small>-->
                                <!--        <div class="input-group input-group-alternative">-->
                                <!--             <select class="form-control" id="report_for">-->
                                                <!--<option value="">All</option>-->
                                <!--                <option value="DMT" selected>DMT</option>-->
                                <!--                <option value="AEPSTXN">AePS</option>-->
                                <!--                <option value="RECHARGE">Recharge</option>-->
                                <!--                <option value="BILL">Bill Payments (Electricity,Water,Gas etc...)</option>-->
                                <!--                <option value="UPICREDIT">UPI Collections</option>-->
                                <!--                <option value="CREDITVA">VA Collections</option>-->
                                <!--              </select>-->
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
                                    <th>Transactions Id</th>
                                    <th>Date</th>
                                    <th>Event</th>
                                    <th>Amount</th>
                                    <th>Closing Balance</th>
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
        "order": [[4, 'desc']],
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
            "url": "{{ route('get:all_success_transaction_report')}}",
            "type": 'GET',
            "data": function (d) {
                d.start_date = $('#start_date').val();
                d.end_date = $('#end_date').val();
                d.json_data = 'json_data';
            },
             
            "dataSrc": function (json) {
                $("#print_total_success_sum").html('Total Success Amount : <small><i class="fas fa-fw fa-rupee-sign"></i></small>'+parseFloat(json.total_success_sum).toFixed(2));
                // $("#print_total_pending_sum").html('Total Pending Amount : <small><i class="fas fa-fw fa-rupee-sign"></i></small>'+parseFloat(json.total_pending_sum).toFixed(2));
                // $("#print_total_failed_sum").html('Total Failed Amount : <small><i class="fas fa-fw fa-rupee-sign"></i></small>'+parseFloat(json.total_failed_sum).toFixed(2));
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
            {"data": "user_mobile"}, 
            {"data": "transaction_id"},
            {"data": "created_at"},
            {"data": "event"},
            {"data": "amount"}, 
            {"data": "final_balance"}, 
        ],
         
    });
});
</script>
@endsection