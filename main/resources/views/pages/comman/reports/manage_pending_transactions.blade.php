@extends('layouts.app')
@section('title','Manage Pending Transactions')
@section('content')
<style>.d_inline{}</style>
<!-- Begin Page Content -->
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4" id="ub-card">
                <div class="card-header py-3 ">
                  <h6 class="m-0 font-weight-bold">
                      <i class="fas fa-book pr-2"></i>Manage Pending Transactions</h6>
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
                    
                    <!--<div class="print_section_center" id="amount_print_section">-->
                    <!--    <span class="amount_print" id="print_total_success_sum">Total Amount : <small><i class="fas fa-fw fa-rupee-sign"></i></small>0.00</span>-->
                    <!--    <span class="amount_print" id="print_total_success_commission">Total Commission : <small><i class="fas fa-fw fa-rupee-sign"></i></small>0.00</span>-->
                    <!--</div> -->
                        
                    <div class="table-responsive">
                        <table class="table display responsive nowrap" style="width:100%" id="datatable_ajax">
                              <thead>
                                    <tr role="row" class="heading">
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Mobile</th>
                                        <th>Transaction Id</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Clossing Balance</th>
                                        <th>Operator</th>
                                        <th>Action</th>
                                        <!--<th>Reference Id</th>-->
                                        <!--<th>Credit/Debit</th>-->
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
        "buttons": [{
            extend: 'collection',
            text: 'Export',
            buttons: [ 'pdfHtml5', 'csvHtml5', 'copyHtml5', 'excelHtml5' ]
        }],
        "ajax": {
            "url": "{{ route('get:manage_pending_transactions')}}",
            "type": 'GET',
            "data": function (d) {
                d.json_data = 'json_data';
                d.start_date = $('#start_date').val();
                d.end_date = $('#end_date').val();
            },
            "dataSrc": function (json) {
                // $("#print_total_success_sum").html('Total Amount : <small><i class="fas fa-fw fa-rupee-sign"></i></small>'+parseFloat(json.total_success_sum).toFixed(2));
                // $("#print_total_success_commission").html('Total Commission : <small><i class="fas fa-fw fa-rupee-sign"></i></small>'+parseFloat(json.total_success_commission).toFixed(2));
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
            {"data": "amount"},
            {"data": "final_balance"},
            // {"data": 'status', name: 'status' ,"render": function (data, type, full, meta) {
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
            {"data": 'event', name: 'event' ,"render": function (data, type, full, meta) {
                    var action = "";
                    switch (data)
                    {
                        case ('DTH'):
                                action = 'DTH';
                                break;
                        case ('PREPAID'):
                                action = 'Prepaid';
                                break;
                        case ('QUICKDMT'):
                                action = 'Money Transfer';
                                break;
                        case ('QUICKUPI'):
                                action = 'UPI Transfer';
                                break;
                        default:
                                action = data
                                break;
                    }
                    return action;
                } 
            },
            
            {"data": 'id', "name": 'id', "sClass": "d_inline" , "render": function (data, type, full, meta) {

                var action = "";
                // action += '<a href={{url("admin/edit-teacher")}}/' + full.slug + '  class="btn btn-primary btn-sm" title="Edit"><span class="fa fa-edit" ></span></a>&nbsp;';
                action+='<button class="btn btn-success btn-sm success_txn_btn" txnstatus="1" title="Success" txn_id="'+data+'">Success</button>&nbsp;';
                action+='<button class="btn btn-danger btn-sm success_txn_btn" txnstatus="2" title="Fail" txn_id="'+data+'">Fail</button>&nbsp;';
                return action;    
            }}
                
        ],
         
    });
});



$(document).on('click', '.success_txn_btn', function (e) {
      e.preventDefault();
        
        var txnstatus = $(this).attr('txnstatus');
        
        if(txnstatus==1)
            var confirmD = confirm("Are you sure,you want to success this transaction?");
        else
            var confirmD = confirm("Are you sure,you want to fail this transaction?");
       
        if (!confirmD) {
          return false;
        }

        var id = $(this).attr('txn_id');
        var parent = $(this).parent();

        $.ajax({
            type: 'post',
            url: "{{ route('post:update_txn_status') }}",
            data: {'id': id,'txnstatus': txnstatus,'_token' : '{{ csrf_token() }}'},
            success: function (result) {
                if (result.success == true) {
                    showMyToast('success', result.message);
                    $('#datatable_ajax').DataTable().ajax.reload();
                    
                } else {
                    showMyToast('error', result.message);
                }
            }
        })
    });
</script>
@endsection