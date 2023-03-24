@extends('layouts.app')
@section('title','Add Retailer')
@section('customcss')
<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/css/dropify.min.css" media="screen,projection">
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/js/dropify.min.js"></script>
<script>
 $(document).ready(function() {
    $('.dropify').dropify(); 
 });
</script>
@endsection
@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
  <div class="card shadow mb-3" id="ub-card">
    <div class="card-header py-3">
        <span style="float: left;">
            <h6 class="m-0 font-weight-bold">
            <i class="fas fa-user-plus pr-2"></i> Add Retailer</h6>
        </span>
        <span style="float: right;">
          <h6 class="m-0 font-weight-bold">Available Retailer Ids : {{ Auth::User()->retailer_ids }}</h6>
        </span>
    </div>
    <div class="card-body">
      
        <div class="col-md-12">
            <form action="{{ route('post:add_retailer') }}" method="post" enctype="multipart/form-data">
              @csrf
              
                <div class="form-group row">
                    <div class="col-md-6 mb-3">
                        <label for="phone">Mobile:</label>
                        <input type="text" class="form-control" id="mobile" placeholder="Enter mobile" 
                        name="mobile" value="{{ old('mobile') }}" maxlength="10" minlength="10" onkeypress="return isNumber(event)"  autocomplete="off" required="">
                        <div class="show_mobile_error text-danger" id="show_mobile_error" style="display:none;"></div>
                    </div>
                
                    <div class="col-md-6 mb-3">
                        <label for="name">Bussiness Or Person Name:
                        <small class="text-danger">(As per PAN)</small></label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}"  
                        placeholder="Enter Bussiness Or Person Name" autocomplete="off" required="">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="email">Email:</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" 
                        placeholder="Email" autocomplete="off" required="">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="phone">Date of Birth:</label>
                        <div class="input-group input-group-alternative">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            </div>
                            <input class="form-control datepicker"  type="text"  id="dob" name="dob" value="{{ old('dob') }}" 
                            placeholder="Select Date" data-date-format="dd-mm-yyyy">
                        </div>
                    </div>
                    
                    
                    <div class="col-md-4 mb-3">
                      <label for="dropdown-test" class="control-label">Category</label>
                        <select class="form-control" name="category_id"  id="category_id" required>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" >{{ $category->cat_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                      <label for="dropdown-test" class="control-label">State</label>
                        <select class="form-control" name="state"  id="state" onchange="statechange(this.value)" required>
                            @foreach($states as $state)
                            <option value="{{ $state->id }}" > {{ $state->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                      <label for="dropdown-test" class="control-label" >City</label>
                        <select class="form-control" name="city" placeholder="City" id="city" required>
                            <option value="">Select City</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="email">Address:</label>
                        <input type="text" class="form-control" id="address" name="address" placeholder="Address" 
                        value="{{ old('address') }}" autocomplete="off" required="">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="phone">Pincode:</label>
                        <input type="text" class="form-control" id="pincode" name="pincode" placeholder="Pincode" 
                        value="{{ old('pincode') }}" onkeypress="return isNumber(event)" maxlength="6"  autocomplete="off" required="">
                    </div>

                    
                    
                    <div class="col-md-6 mb-3">
                        <label for="pan_number">PAN Number:</label>
                        <input type="text" class="form-control" id="pan_number" name="pan_number" value="{{ old('pan_number') }}"  placeholder="" 
                         autocomplete="off" maxlength="10" required="" style="text-transform: uppercase">
                         <div class="show_pan_error text-danger" id="show_pan_error" style="display:none;"></div>
                    </div>
                
                    <div class="col-md-6 mb-3">
                        <label for="aadhaar_number">Aadhaar Number:</label>
                        <input type="text" class="form-control" id="aadhaar_number" name="aadhaar_number" value="{{ old('aadhaar_number') }}" placeholder="" 
                        maxlength="12" onkeypress="return isNumber(event)"  autocomplete="off" required="">
                         <div class="show_aadhaar_error text-danger" id="show_aadhaar_error" style="display:none;"></div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="pan_image">PAN Image</label>
                          <input type="file" id="pan_image" name="pan_image" class="dropify"  data-default-file="" accept="image/*" required="" />
                     </div>
                     
                    <div class="col-md-4 mb-3">
                        <label for="aadhaar_front_image">Aadhaar Front Image</label>
                        <input type="file" id="aadhaar_front_image" name="aadhaar_front_image" class="dropify"  accept="image/*" data-default-file="" required="" />
                    </div> 
                    
                    <div class="col-md-4 mb-3">
                        <label for="aadhaar_back_image">Aadhaar Back Image</label>
                        <input type="file" id="aadhaar_back_image" name="aadhaar_back_image" class="dropify"  accept="image/*" data-default-file="" required="" />
                    </div>
                    
                    
                    <!--Bank Details-->
                    <div class="col-md-6 mb-3">
                        <label for="phone">Bank Name:</label>
                        <input type="text" class="form-control" id="bank_name" name="bank_name" placeholder="Enter Bank Name" 
                        value="{{ old('bank_name') }}" autocomplete="off" required="">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="phone">Bank A/C no:</label>
                        <input type="text" class="form-control" id="account_no" name="account_no" placeholder="Enter Account Number" 
                        value="{{ old('account_no') }}" autocomplete="off" required="">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="phone">Bank IFSC:</label>
                        <input type="text" class="form-control" id="ifsc" name="ifsc" placeholder="Enter IFSC" 
                        value="{{ old('ifsc') }}" autocomplete="off" required="">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="phone">Holder Name:</label>
                        <input type="text" class="form-control" id="holder" name="holder" placeholder="Enter Holder Name" 
                         value="{{ old('holder') }}" autocomplete="off" required="">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="phone">Branch:</label>
                        <input type="text" class="form-control" id="branch" name="branch"  placeholder="Enter Branch" 
                        value="{{ old('branch') }}"  autocomplete="off" required="">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="phone">City:</label>
                        <input type="text" class="form-control" id="bank_city" name="bank_city"  placeholder="Enter Bank City" 
                        value="{{ old('bank_city') }}" autocomplete="off" required="">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="phone">State:</label>
                        <input type="text" class="form-control" id="bank_state" name="bank_state"  placeholder="Enter Bank State" 
                        value="{{ old('bank_state') }}" autocomplete="off" required="">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="phone">Bank Address:</label>
                        <input type="text" class="form-control" id="bank_address" name="bank_address"  placeholder="Enter Bank Address" 
                        value="{{ old('bank_address') }}" autocomplete="off" required="">
                    </div>
                    
                    
                </div>
                
                <button type="submit" class="btn btn-primary submit-btn">Submit</button>
            </form>
        </div>

    </div>
  </div>
</div>
@endsection
@section('customjs')
<script>
$("#mobile").blur(function() {
    $('#show_mobile_error').hide();
    $('.submit-btn').prop('disabled', false);
    var mobile = $('#mobile').val();
    var len = mobile.length;
    if(len < 10) {
        showMyToast('error','Enter valid Mobile');
        return false;
    }
    $.ajax({
        type: 'post',
        dataType:'json',
        url: "{{ route('post:check_mobile_registration') }}",
        data: {"mobile" : mobile ,"_token":"{{ csrf_token() }}"},
        success: function (result) {
            if(result.success) {
                $('#show_mobile_error').html(result.message);
                $('#show_mobile_error').show();
                
                $('.submit-btn').prop('disabled', true);
                
            }
            else{
                $('#show_mobile_error').hide();
                $('.submit-btn').prop('disabled', false);
            }
        }
    });
});

$("#pan_number").blur(function() {
    $('#show_pan_error').hide();
    $('.submit-btn').prop('disabled', false);
    var pan = $('#pan_number').val();
    var len = pan.length;
    if(len < 10) {
        showMyToast('error','Enter Valid PAN Number');
        return false;
    }
    $.ajax({
        type: 'post',
        dataType:'json',
        url: "{{ route('post:check_pan_registration') }}",
        data: {"pan" : pan ,"_token":"{{ csrf_token() }}"},
        success: function (result) {
            if(result.success) {
                $('#show_pan_error').html(result.message);
                $('#show_pan_error').show();
                $('.submit-btn').prop('disabled', true);
            }
            else{
                $('#show_pan_error').hide();
                $('.submit-btn').prop('disabled', false);
            }
        }
    });
});

$("#aadhaar_number").blur(function() {
    $('#show_aadhaar_error').hide();
    $('.submit-btn').prop('disabled', false);
    var aadhaar_number = $('#aadhaar_number').val();
    var len = aadhaar_number.length;
    if(len < 12) {
        showMyToast('error','Enter Valid Aadhaar Number');
        return false;
    }
    $.ajax({
        type: 'post',
        dataType:'json',
        url: "{{ route('post:check_aadhaar_registration') }}",
        data: {"aadhaar_number" : aadhaar_number ,"_token":"{{ csrf_token() }}"},
        success: function (result) {
            if(result.success) {
                $('#show_aadhaar_error').html(result.message);
                $('#show_aadhaar_error').show();
                $('.submit-btn').prop('disabled', true);
            }
            else{
                $('#show_aadhaar_error').hide();
                $('.submit-btn').prop('disabled', false);
            }
        }
    });
});
function statechange(state) {
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