@extends('layouts.app')
@section('title','Add Member')
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
            <i class="fas fa-user-plus pr-2"></i> Add Member</h6>
        </span>
    </div>
    <div class="card-body">
      
        <div class="col-md-12">
            <form action="{{ route('post:add_member') }}" method="post" enctype="multipart/form-data">
              @csrf
              
                <div class="form-group row">
                    <div class="col-md-6 mb-3">
                      <label for="dropdown-test" class="control-label" >Relation*:</label>
                        <select class="form-control" name="relation_with_user" placeholder="Relation" id="relation_with_user" required>
                            <option value="">Select</option>
                            <option value="Spouse">Spouse</option>
                            <option value="Son">Son</option>
                            <option value="Daughter">Daughter</option>
                            <option value="Parents">Parents</option>
                            <option value="Brother">Brother</option>
                            <option value="Sister">Sister</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="phone">Mobile:</label>
                        <input type="text" class="form-control" id="mobile" placeholder="Enter mobile" 
                        name="mobile" value="{{ old('mobile') }}" maxlength="10" minlength="10" onkeypress="return isNumber(event)" minlength="11" maxlength="11" onkeypress="return isNumber(event)"  autocomplete="off">
                        <div class="show_mobile_error text-danger" id="show_mobile_error" style="display:none;"></div>
                    </div>
                
                    <div class="col-md-6 mb-3">
                        <label for="name">Member Name*: </label>
                        <input type="text" class="form-control" id="name" name="name" onkeypress="return (event.charCode > 64 && event.charCode < 91) || (event.charCode > 96 && event.charCode < 123) || (event.charCode == 32) || (event.charCode == 39) || (event.charCode == 45)" value="{{ old('name') }}"  
                        placeholder="Enter Member Name" autocomplete="off" required="">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="name">Profession: </label>
                        <input type="text" class="form-control" id="profession" name="profession" value="{{ old('profession') }}"  
                        placeholder="Enter Profession" autocomplete="off" >
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="email">Email:</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" 
                        placeholder="Email" autocomplete="off">
                    </div>

                    <div class="col-md-4 mb-3">
                      <label for="dropdown-test" class="control-label" >Gender*:</label>
                        <select class="form-control" name="gender" placeholder="gender" id="gender" required>
                            <option value="">Select</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
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