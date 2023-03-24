@extends('layouts.app')
@section('title','Update Member')
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
            <i class="fas fa-user-edit pr-2"></i> Update Member</h6>
        </span>
        <span style="float: right;">
          <!--<h6 class="m-0 font-weight-bold">Available Retailer Ids : {{ Auth::User()->retailer_ids }}</h6>-->
        </span>
    </div>
    <div class="card-body">
      
        <div class="col-md-12">
            <form action="{{ route('post:edit_retailer') }}" method="post" enctype="multipart/form-data">
              @csrf
              <input type="hidden" value="{{ $edit->id }}" name="user_id">
                <div class="form-group row">
                    <div class="col-md-6 mb-3">
                        <label for="phone">Mobile:</label>
                        <input type="text" class="form-control" id="mobile" placeholder="Enter mobile" name="mobile" value="{{ $edit->mobile }}" 
                        maxlength="11" minlength="11" readonly onkeypress="return isNumber(event)"  autocomplete="off" required="">
                        <div class="show_mobile_error text-danger" id="show_mobile_error" style="display:none;"></div>
                    </div>
                
                    <div class="col-md-6 mb-3">
                        <label for="name">Member Name*:</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ $edit->name }}"  
                        placeholder="Enter Bussiness Or Person Name" autocomplete="off" required="">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="email">Email*:</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ $edit->email }}" 
                        placeholder="Email" autocomplete="off" required="">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                      <label for="dropdown-test" class="control-label">Gender</label>
                        <select class="form-control" name="gender"  id="gender" required>
                            <option value="Male" @if($edit->gender == 'Male') selected @endif >Male</option>
                            <option value="Female" @if($edit->gender == 'Female') selected @endif >Female</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="address">Town*:</label>
                        <input type="text" class="form-control" id="town" name="town" placeholder="Town" 
                        value="{{ $edit->town }}" autocomplete="off" required="">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="address">Address Line 1*:</label>
                        <input type="text" class="form-control" id="address_line_1" name="address_line_1" placeholder="Address Line 1" 
                        value="{{ $edit->address_line_1 }}" autocomplete="off" required="">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="address">Address Line 2:</label>
                        <input type="text" class="form-control" id="address_line_2" name="address_line_2" placeholder="Address Line 2" 
                        value="{{ $edit->address_line_2 }}" autocomplete="off">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="phone">POSTCODE*:</label>
                        <input type="text" class="form-control" id="postcode" name="postcode" placeholder="postcode" 
                        value="{{ $edit->postcode }}" onkeypress="return isNumber(event)" maxlength="6"  autocomplete="off" required="">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="pan_number">Profession:</label>
                        <input type="text" class="form-control" id="profession" name="profession" value="{{ $edit->profession }}"  placeholder="Profession" 
                         autocomplete="off">
                         
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
// $("#mobile").blur(function() {
//     $('#show_mobile_error').hide();
//     $('.submit-btn').prop('disabled', false);
//     var mobile = $('#mobile').val();
//     var len = mobile.length;
//     if(len < 10) {
//         showMyToast('error','Enter valid Mobile');
//         return false;
//     }
//     $.ajax({
//         type: 'post',
//         dataType:'json',
//         url: "{{ route('post:check_mobile_registration') }}",
//         data: {"mobile" : mobile ,"_token":"{{ csrf_token() }}"},
//         success: function (result) {
//             if(result.success) {
//                 $('#show_mobile_error').html(result.message);
//                 $('#show_mobile_error').show();
                
//                 $('.submit-btn').prop('disabled', true);
                
//             }
//             else{
//                 $('#show_mobile_error').hide();
//                 $('.submit-btn').prop('disabled', false);
//             }
//         }
//     });
// });

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

$(document).ready(function(){
    
    var state= $('#edit_state').val();
    var city= $('#edit_city').val();
    
    $.ajax({
        type: 'post',
        dataType:'html',
        url: "{{ route('post:get_edit_state_city') }}",
        data: {"state" : state,"city" : city, "_token":"{{ csrf_token() }}"},
        success: function (result) {
            $('#city').html(result);
        }
    });
    
    
});
</script>
@endsection