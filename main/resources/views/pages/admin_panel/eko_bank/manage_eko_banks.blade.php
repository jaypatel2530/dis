@extends('layouts.app')
@section('title','Eko Banks')
@section('content')
<div class="container-fluid">
  <div class="card shadow mb-4" id="ub-card">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold">
          <i class="fas fa-user pr-2"></i>Eko Banks</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table display responsive nowrap" style="width:100%" id="datatable_ajax">
                  <thead>
                      <tr role="row" class="heading">
                          <th>#</th>
                          <th>Holder Name</th>
                          <th>Bank</th>
                          <th>A/c Number</th>
                          <th>IFSC</th>
                          <th>Recipient Id</th>
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
            "url": "{{ route('get:manage_eko_bank')}}",
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
            
            {"data": "holder_name"},           
            {"data": "bank_name"},
            {"data": "account_number"},
            {"data": "ifsc"},
            {"data": "recipient_id"},
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
</script>
@endsection