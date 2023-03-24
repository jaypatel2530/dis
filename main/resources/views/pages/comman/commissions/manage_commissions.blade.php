@extends('layouts.app')
@section('title','Manage Commissions')
@section('content')
<style>
    .mwidth{
        width:80%;
    }
</style>
<div class="container-fluid">
  <div class="card shadow mb-4" id="ub-card">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold">
          <i class="fas fa-user pr-2"></i>Manage Commissions</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table display responsive nowrap" style="width:100%" id="datatable_ajax">
                  <thead>
                        <tr role="row" class="heading">
                            <th>#</th>
                            <th>Operator Name</th>
                            <th>Super Distributor Commission</th>
                            <th>Super Distributor Commission Type</th>
                            <th>Distributor Commission</th>
                            <th>Distributor Commission Type</th>
                            <th>Retailer Commission</th>
                            <th>Retailer Commission Type</th>
                            <th>API</th>
                            <th>Action</th>
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
$(document).ready(function() {
    oTable = $('#datatable_ajax').DataTable({
        "processing": false,
        "serverside": true,
        "dom": 'lfrtip',
        "ajax": {
            "url": "{{ route('get:manage_commissions')}}",
              "type": 'GET',
              data: function (d) {
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
          
            {"data": 'sd_commission', name: 'sd_commission' ,"render": function (data, type, full, meta) {
                return action = '<input type="text" value="'+data+'" id="sd_commission_'+full.id+'" class="mwidth">';
            }},

            {"data": 'sd_commission_type', name: 'sd_commission_type' ,"render": function (data, type, full, meta) {
                var action = '';
                action += '<select id="sd_commission_type_'+full.id+'">';
                action += '<option value="percentage" '+ (data == "percentage" ? "selected" : "")  +'>Percentage</option>';
                action += '<option value="flat" '+ (data == "flat" ? "selected" : "")  +'>Flat</option>';
                action += '</select>';
                return action;
            }},
            
            {"data": 'dist_commission', name: 'dist_commission' ,"render": function (data, type, full, meta) {
                return action = '<input type="text" value="'+data+'" id="dist_commission_'+full.id+'" class="mwidth">';
            }},

            {"data": 'dist_commission_type', name: 'dist_commission_type' ,"render": function (data, type, full, meta) {
                var action = '';
                action += '<select id="dist_commission_type_'+full.id+'">';
                action += '<option value="percentage" '+ (data == "percentage" ? "selected" : "")  +'>Percentage</option>';
                action += '<option value="flat" '+ (data == "flat" ? "selected" : "")  +'>Flat</option>';
                action += '</select>';
                return action;
            }},
            
            
            {"data": 'commission', name: 'commission' ,"render": function (data, type, full, meta) {
                return action = '<input type="text" value="'+data+'" id="retailer_commission_'+full.id+'" class="mwidth">';
            }},

            {"data": 'commission_type', name: 'commission_type' ,"render": function (data, type, full, meta) {
                var action = '';
                action += '<select id="retailer_commission_type_'+full.id+'">';
                action += '<option value="percentage" '+ (data == "percentage" ? "selected" : "")  +'>Percentage</option>';
                action += '<option value="flat" '+ (data == "flat" ? "selected" : "")  +'>Flat</option>';
                action += '</select>';
                return action;
            }},
            
            {"data": 'api_id', name: 'api_id' ,"render": function (data, type, full, meta) {
                var action = '';
                action += '<select id="api_id_'+full.id+'">';
                action += '<option value="0" '+ (data == "0" ? "selected" : "")  +'>Down</option>';
                // action += '<option value="1" '+ (data == "1" ? "selected" : "")  +'>vastwebindia</option>';
                action += '<option value="2" '+ (data == "2" ? "selected" : "")  +'>mofuse</option>';
                // action += '<option value="3" '+ (data == "3" ? "selected" : "")  +'>Earmwaypay</option>';
                action += '</select>';
                return action;
            }},
            
            { "data": 'id', "name": 'id', "sClass": "salaryright" , "render": function (data, type, full, meta) {
                var action = "";
                action+='<button class="btn btn-success btn-sm"  tooltip="Delete" title="Delete" onclick="updateCommission(\'' + data + '\');">Update</button>&nbsp;';
                return action;    }
            }


            // {"data": 'status', name: 'status' ,"render": function (data, type, full, meta) {
            //         var action = "";
                    
            //         switch (data)
            //         {
            //             case (0):
            //                     action = '<h6><span class="badge badge-danger">Disabled</span></h6>';
            //                     break;
            //             case ('1'):
            //                     action = '<h6><span class="badge badge-success">Enabled</span></h6>';
            //                     break;
            //             default:
            //                     action = data
            //                     break;
            //         }
            //         return action;
            //     } 
            // },
            
            
            
        ],
         
    });
});

function updateCommission(id) {
    
    var sd_commission = $("#sd_commission_"+id).val();
    var sd_commission_type = $("#sd_commission_type_"+id).val();
    
    var dist_commission = $("#dist_commission_"+id).val();
    var dist_commission_type = $("#dist_commission_type_"+id).val();
    
    var retailer_commission = $("#retailer_commission_"+id).val();
    var retailer_commission_type = $("#retailer_commission_type_"+id).val();
    
    var api_id = $("#api_id_"+id).val();
    // alert(api_id);
    $.ajax({
        type: 'post',
        url: '{{ route("post:update_commissions") }}',
        data: {"id" : id, "sd_commission" : sd_commission, "sd_commission_type" : sd_commission_type, "dist_commission" : dist_commission, 
        "dist_commission_type" : dist_commission_type, "retailer_commission" : retailer_commission, "retailer_commission_type" : retailer_commission_type,"api_id" : api_id, "_token":"{{ csrf_token() }}"},
        success: function (result) {
            if(result.success)
                showMyToast("success",result.message);  
            else
                showMyToast("error",result.message);
        }
    });
    
}
</script>
@endsection