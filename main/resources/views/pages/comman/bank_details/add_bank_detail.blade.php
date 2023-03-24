@extends('layouts.app')
@section('title')
    @if(isset($retailerdata)) Edit Bank Detail @else Add Bank Detail @endif
@endsection
@section('customcss') 
@endsection
@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
  <div class="card shadow mb-4" id="ub-card">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold">
          <i class="fas fa-user-plus pr-2"></i>@if(isset($retailerdata)) Edit Bank Detail @else Add Bank Detail @endif</h6>
    </div>
    <div class="card-body">
      
        <div class="form-group row">
            <div class="col-md-12">    
            
            <form action="{{ route('post:add_bank_details') }}" method="post" enctype="multipart/form-data">
            @csrf
              
            @if(isset($retailerdata))
                <input type="hidden" name="retailer_id" value="{{ $retailerdata->id }}">
            @endif
                
                <div class="form-group row">
                    <div class="col-md-6 mb-4">
                        <label for="email">Bank Name:</label>
                        <input type="text" class="form-control" id="bank_name" name="bank_name" placeholder="Bank Name" 
                        value="@if(isset($retailerdata)){{ $retailerdata->bank_name }}@else{{ old('bank_name') }}@endif" autocomplete="off" required="">
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <label for="phone">Account Number:</label>
                        <input type="text" class="form-control" id="acc" name="acc" placeholder="Account Number" 
                        value="@if(isset($retailerdata)){{ $retailerdata->acc }}@else{{ old('acc') }}@endif" onkeypress="return isNumber(event)"  autocomplete="off" required="">
                    </div>
                        
                    <div class="col-md-6 mb-4">
                        <label for="dropdown-test" class="control-label">Account Type</label>
                        <select class="form-control" name="acc_type"  id="acc_type" required>
                            <option value="">Select Account Type</option>
                            <option value="Saving Account">Saving Account</option>
                            <option value="Current Account">Current Account</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <label for="email">IFSC:</label>
                        <input type="text" class="form-control" id="bank_ifsc" name="bank_ifsc" placeholder="IFSC" 
                        value="@if(isset($retailerdata)){{ $retailerdata->bank_ifsc }}@else{{ old('bank_ifsc') }}@endif" autocomplete="off" required="">
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <label for="email">Branch:</label>
                        <input type="text" class="form-control" id="bank_branch" name="bank_branch" placeholder="Branch" 
                        value="@if(isset($retailerdata)){{ $retailerdata->bank_branch }}@else{{ old('bank_branch') }}@endif" autocomplete="off" required="">
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <label for="email">Address:</label>
                        <input type="text" class="form-control" id="bank_address" name="bank_address" placeholder="Address" 
                        value="@if(isset($retailerdata)){{ $retailerdata->bank_address }}@else{{ old('bank_address') }}@endif" autocomplete="off" required="">
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <label for="email">District:</label>
                        <input type="text" class="form-control" id="bank_district" name="bank_district" placeholder="District" 
                        value="@if(isset($retailerdata)){{ $retailerdata->bank_district }}@else{{ old('bank_district') }}@endif" autocomplete="off" required="">
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <label for="email">City:</label>
                        <input type="text" class="form-control" id="bank_city" name="bank_city" placeholder="City" 
                        value="@if(isset($retailerdata)){{ $retailerdata->bank_city }}@else{{ old('bank_city') }}@endif" autocomplete="off" required="">
                    </div>
                        
                    <div class="col-md-6 mb-4">
                        <label for="email">State:</label>
                        <input type="text" class="form-control" id="bank_state" name="bank_state" placeholder="State" 
                        value="@if(isset($retailerdata)){{ $retailerdata->bank_state }}@else{{ old('bank_state') }}@endif" autocomplete="off" required="">
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <label for="email">Bank Logo:</label>
                        <input type="file" class="form-control" id="img" name="img" placeholder="img" required="">
                    </div>
                    
                </div>
            
              <!--<div class="form-group">-->
              <!--  <label for="category_name">Status:</label><br>-->
              <!--  <label class="radio-inline">-->
              <!--      <input type="radio" name="status" value="1" @if(isset($retailerdata)) {{ ($retailerdata->status == 1) ? 'checked' : '' }} @endif checked>&nbsp;Enabled</label>&nbsp;&nbsp;&nbsp;-->
              <!--  <label class="radio-inline">-->
              <!--      <input type="radio" name="status" value="0" @if(isset($retailerdata)) {{ ($retailerdata->status == 0) ? 'checked' : '' }} @endif>&nbsp;Disabled</label>-->
              <!--</div>-->
              
              <button type="submit" class="btn btn-primary">Submit</button>
            </form>
                </div>
             </div>

    </div>
  </div>
</div>
@endsection
@section('customjs')

<script>

function statechange(state)
{
    $.ajax({
        type: 'post',
        dataType:'html',
        url: "{{ route('post:get_state_city') }}",
        data: {"state" : state ,"_token":"{{ csrf_token() }}"},
        success: function (result) {
            $('#city').html(result);
        }
    });
}

</script>
@endsection