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
            <i class="fas fa-user-plus pr-2"></i> Update Member</h6>
        </span>
    </div>
    <div class="card-body">
      
        <div class="col-md-12">
            <form action="{{ route('post:edit_member') }}" method="post" enctype="multipart/form-data">
              @csrf
              <input type="hidden" value="{{ $edit->id }}" name="user_id">
                <div class="form-group row">
                    <div class="col-md-6 mb-3">
                      <label for="dropdown-test" class="control-label" >Relation*:</label>
                        <select class="form-control" name="relation_with_user" placeholder="Relation" id="relation_with_user" required>
                            <option value="">Select</option>
                            <option value="Spouse" @if($edit->relation_with_user == 'Spouse') selected @endif >Spouse</option>
                            <option value="Son" @if($edit->relation_with_user == 'Son') selected @endif >Son</option>
                            <option value="Daughter" @if($edit->relation_with_user == 'Daughter') selected @endif >Daughter</option>
                            <option value="Parents" @if($edit->relation_with_user == 'Parents') selected @endif >Parents</option>
                            <option value="Brother" @if($edit->relation_with_user == 'Brother') selected @endif >Brother</option>
                            <option value="Sister" @if($edit->relation_with_user == 'Sister') selected @endif >Sister</option>
                            <option value="Other" @if($edit->relation_with_user == 'Other') selected @endif >Other</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="phone">Mobile:</label>
                        <input type="text" class="form-control" id="mobile" placeholder="Enter mobile" 
                        name="mobile" value="{{ $edit->mobile }}" onkeypress="return isNumber(event)" minlength="11" maxlength="11" onkeypress="return isNumber(event)"  autocomplete="off">
                        <div class="show_mobile_error text-danger" id="show_mobile_error" style="display:none;"></div>
                    </div>
                
                    <div class="col-md-6 mb-3">
                        <label for="name">Member Name*: </label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ $edit->name }}"  
                        placeholder="Enter Member Name" autocomplete="off" required="">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="name">Profession: </label>
                        <input type="text" class="form-control" id="profession" name="profession" value="{{ $edit->profession }}"  
                        placeholder="Enter Profession" autocomplete="off" >
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="email">Email:</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ $edit->email }}" 
                        placeholder="Email" autocomplete="off">
                    </div>

                    <div class="col-md-4 mb-3">
                      <label for="dropdown-test" class="control-label" >Gender*:</label>
                        <select class="form-control" name="gender" placeholder="gender" id="gender" required>
                            <option value="">Select</option>
                            <option value="Male" @if($edit->gender == 'Male') selected @endif>Male</option>
                            <option value="Female" @if($edit->gender == 'Female') selected @endif>Female</option>
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