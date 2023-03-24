@extends('layouts.app')
@section('title','Manage DMT Commissions')
@section('content')
<style>.mwidth{width:80%;}</style>
<div class="container-fluid">
  <div class="card shadow mb-4" id="ub-card">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold">
          <i class="fas fa-user pr-2"></i>Manage DMT Commissions</h6>
    </div>
    <div class="card-body">
        
        <form action="{{ route('post:add_dmt_commission') }}" method="post" enctype="multipart/form-data">
            @csrf
            
                <div class="form-group row">
                    <div class="col-md-12 mb-4">
                        <label for="email">Operator Name:</label>
                        <input type="text" class="form-control" id="operator_name" name="operator_name" placeholder="Operator Name" 
                        value="{{ old('operator_name') }}" required="">
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <label for="phone">Super Distributor Commission:</label>
                        <input type="text" class="form-control" id="sd_commission" name="sd_commission" placeholder="Super Distributor Commission" 
                        value="{{ old('sd_commission') }}" onkeypress="return isNumber(event)" required="">
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <label for="dropdown-test" class="control-label">Super Distributor Commission Type:</label>
                        <select class="form-control" name="sd_commission_type"  id="sd_commission_type" required>
                            <option value="">Select Commission Type</option>
                            <option value="percentage">Percentage</option>
                            <option value="flat">Flat</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <label for="phone">Distributor Commission:</label>
                        <input type="text" class="form-control" id="dist_commission" name="dist_commission" placeholder="Distributor Commission" 
                        value="{{ old('dist_commission') }}" onkeypress="return isNumber(event)" required="">
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <label for="dropdown-test" class="control-label">Distributor Commission Type:</label>
                        <select class="form-control" name="dist_commission_type"  id="dist_commission_type" required>
                            <option value="">Select Commission Type</option>
                            <option value="percentage">Percentage</option>
                            <option value="flat">Flat</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <label for="phone">Retailer Commission:</label>
                        <input type="text" class="form-control" id="retailer_commission" name="retailer_commission" placeholder="Retailer Commission" 
                        value="{{ old('retailer_commission') }}" onkeypress="return isNumber(event)" required="">
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <label for="dropdown-test" class="control-label">Retailer Commission Type:</label>
                        <select class="form-control" name="retailer_commission_type"  id="retailer_commission_type" required>
                            <option value="">Select Commission Type</option>
                            <option value="percentage">Percentage</option>
                            <option value="flat">Flat</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <label for="phone">Fee:</label>
                        <input type="text" class="form-control" id="acc" name="fee" placeholder="Fee" value="{{ old('fee') }}" 
                        onkeypress="return isNumber(event)" required="">
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <label for="dropdown-test" class="control-label">Fee Type:</label>
                        <select class="form-control" name="fee_type"  id="fee_type" required>
                            <option value="">Select Fee Type</option>
                            <option value="percentage">Percentage</option>
                            <option value="flat">Flat</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <label for="phone">Min Amount:</label>
                        <input type="text" class="form-control" id="min_amount" name="min_amount" placeholder="Min amount" value="{{ old('min_amount') }}" 
                        onkeypress="return isNumber(event)" required="">
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <label for="phone">Max Amount:</label>
                        <input type="text" class="form-control" id="acc" name="max_amount" placeholder="Max amount" value="{{ old('max_amount') }}" 
                        onkeypress="return isNumber(event)" required="">
                    </div>
                    
                    
                </div>
            
    
              
              <button type="submit" class="btn btn-primary">Submit</button>
            </form><br>
            <hr>
            
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
                            
                            <th>Fee</th>
                            <th>Fee Type</th>
                            
                            <th>Min Amount</th>
                            <th>Max Amount</th>
                            
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
            "url": "{{ route('get:manage_dmt_commissions')}}",
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
            {"data": "sd_commission"},
            {"data": "sd_commission_type"},
            {"data": "dist_commission"},
            {"data": "dist_commission_type"},
            {"data": "commission"},
            {"data": "commission_type"},
            {"data": "fee"},
            {"data": "fee_type"},
            {"data": "min_amount"},
            {"data": "max_amount"},
               
            { "data": 'id', "name": 'id', "sClass": "salaryright" , "render": function (data, type, full, meta) {
                var action = "";
                // action += '<a href={{url("edit-retailer")}}/' + data + '  class="btn btn-success btn-sm" tooltip="Edit" title="Edit"><i class="fas fa-edit"></i></a>&nbsp;';
                action+='<button class="btn btn-danger btn-sm"  tooltip="Delete" title="Delete" onclick="DataDelete(\'' + data + '\');"><i class="fas fa-trash-alt"></i></button>&nbsp;';
                return action;    }
            }
        ],
         
    });
});

function DataDelete(id) {
      
  var confirmD = confirm("Are you sure,you want to delete this?");
  if (!confirmD) {
    return false;
  }
  $.ajax({
    url: "{{ route('post:delete_operator') }}",
    type: "post",
    data: {'id':id, '_token':'{{ csrf_token() }}'},
    dataType: 'json',
    beforeSend: function() {
    },
    success: function(data) {
      if (data.success) {
          showMyToast('success', data.message);
          window.location.reload();
      } else {
          showMyToast('error', data.message);
          return false;
      }
    },
    error: function() {
      return true;
    }
  })
}
</script>
@endsection