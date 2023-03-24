@extends('layouts.app')
@section('title','Add Money Report')
@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
    
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4" id="ub-card">
                <div class="card-header py-3 ">
                  <h6 class="m-0 font-weight-bold">
                      <i class="fas fa-book pr-2"></i>Add Money Report</h6>
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
            
                                <div class="col-md-4">
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
                                    <div class="form-group">
                                        <small>Status</small>
                                        <div class="input-group input-group-alternative">
                                             <select class="form-control" id="txn_status">
                                                <option value="">All</option>
                                                <option value="P">Pending</option>
                                                <option value="1">Approved</option>
                                                <option value="2">Rejected</option>
                                              </select>
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
                    
                    <div class="print_section_center" id="amount_print_section">
                        <span class="amount_print" id="print_total_success_sum">Total Amount : <small><i class="fas fa-fw fa-rupee-sign"></i></small>0.00</span>
                    </div> 
                        
                    <div class="table-responsive">
                            <table class="table display responsive nowrap" style="width:100%" id="datatable_ajax">
                                  <thead>
                                      <tr role="row" class="heading">
                                        <th>#</th>
                                        <!--<th>Name</th>-->
                                        <th>Txn ID</th>
                                        <th>Time</th>
                                        <th>Amount</th>
                                        <th>Transfer Type</th>
                                        <th>Bank</th>
                                        <th>Bank Ref</th>
                                        <th>Image</th>
                                        <th>Status</th>
                                        
                                        <!--<th>Invoice</th>-->
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
        "order": [ [2, 'desc'] ],
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
            "url": "{{ route('get:add_money_report_data')}}",
            "type": 'GET',
            "data": function (d) {
                d.start_date = $('#start_date').val();
                d.end_date = $('#end_date').val();
                d.txn_status = $('#txn_status').val();
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
            
            // {"data": "name"},           
            {"data": "transaction_id"}, 
            {"data": "created_at"}, 
            {"data": "amount"}, 
            {"data": "transfer_type"},
            {"data": "bank_name"},
            {"data": "bank_ref"},
            {"data": 'img', name: 'img' , "render": function (data, type, full, meta) {
                return "<img style='border-radius:unset; height:50px;width:auto;' src=\"{{ url('/uploads/money_request') }}/" + data + "\" />";
            }}, 
            
            {"data": 'status', name: 'status' ,"render": function (data, type, full, meta) {
                    var action = "";
                    
                    switch (data)
                    {
                        case (0):
                                action = '<h6><span class="badge badge-warning">Pending</span></h6>';
                                break;
                        case ('1'):
                                action = '<h6><span class="badge badge-success">Approved</span></h6>';
                                break;
                        case ('2'):
                                action = '<h6><span class="badge badge-danger">Rejected</span></h6>';
                                break;
                        default:
                                action = data
                                break;
                    }
                    return action;
                } 
            },
            
            // { "data": 'transaction_id', "name": 'transaction_id', "sClass": "salaryright" , "render": function (data, type, full, meta) {

            //     var action = "";
            //     action += '<a target="_blank" href={{url("add-money/invoice")}}/' + data + '  class="btn btn-success btn-sm" tooltip="Edit" title="Invoice">Invoice</a>&nbsp;';
            //     return action;    }
            // }
        ],
         
    });
});
</script>
@endsection