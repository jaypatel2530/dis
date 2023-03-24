@extends('layouts.app')
@section('title','Manage Bank Details')
@section('content')
<div class="container-fluid">
  <div class="card shadow mb-4" id="ub-card">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold">
          <i class="fas fa-user pr-2"></i>Manage Bank Details</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table display responsive nowrap" style="width:100%" id="datatable_ajax">
                  <thead>
                      <tr role="row" class="heading">
                          <th>#</th>
                          <th>Bank</th>
                          <th>A/C Number</th>
                          <th>IFSC</th>
                          <th>A/C Type</th>
                          <th>Branch</th>
                          <th>Address</th>
                          <th>District</th>
                          <th>City</th>
                          <th>State</th>
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
$(document).ready(function() {
    oTable = $('#datatable_ajax').DataTable({
        "processing": false,
        "serverside": true,
        "dom": 'lfrtip',
        "ajax": {
            "url": "{{ route('get:manage_bank_detail_data')}}",
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
            
            {"data": "bank_name"},           
            {"data": "acc"},
            {"data": "bank_ifsc"},
            {"data": "acc_type"},
            {"data": "bank_branch"},
            {"data": "bank_address"},
            {"data": "bank_district"},
            {"data": "bank_city"},
            {"data": "bank_state"},
            {"data": 'status', name: 'status' ,"render": function (data, type, full, meta) {
                    var action = "";
                    
                    switch (data)
                    {
                        case (0):
                                action = '<h6><span class="badge badge-danger">Disabled</span></h6>';
                                break;
                        case ('1'):
                                action = '<h6><span class="badge badge-success">Enabled</span></h6>';
                                break;
                        default:
                                action = data
                                break;
                    }
                    return action;
                } 
            },
            
            // { "data": 'id', "name": 'id', "sClass": "salaryright" , "render": function (data, type, full, meta) {
            //     var action = "";
            //     action += '<a href={{url("edit-retailer")}}/' + data + '  class="btn btn-success btn-sm" tooltip="Edit" title="Edit"><i class="fas fa-edit"></i></a>&nbsp;';
            //     action+='<button class="btn btn-danger btn-sm"  tooltip="Delete" title="Delete" onclick="DataDelete(\'' + data + '\');"><i class="fas fa-trash-alt"></i></button>&nbsp;';
            //     return action;    }
            // }
            
        ],
         
    });
});
</script>
@endsection