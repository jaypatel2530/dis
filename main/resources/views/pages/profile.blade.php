@extends('layouts.app')
@section('title','Profile')
@section('customcss')
<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/css/dropify.min.css" media="screen,projection">
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/js/dropify.min.js"></script>
<style>
.emp-profile{padding: 3%; margin-top: 3%; margin-bottom: 3%; border-radius: 0.5rem;}
.profile-img{text-align: center;}
.profile-img img{width: 70%;height: 100%;}
.profile-head h5{color: #333;}
.profile-head h6{color: #0062cc;}
.profile-edit-btn{border: none; border-radius: 1.5rem; width: 70%; padding: 2%; font-weight: 600; color: #6c757d; cursor: pointer;}
.proile-rating{font-size: 12px; color: #818182; margin-top: 5%;}
.proile-rating span{color: #495057; font-size: 15px;font-weight: 600;}
.profile-head .nav-tabs{margin-bottom:5%;}
.profile-head .nav-tabs .nav-link{font-weight:600;border: none;}
.profile-head .nav-tabs .nav-link.active{border: none;border-bottom:2px solid #0062cc;}
.profile-work {margin-top:12px;}
.profile-work p{font-size: 12px;color: #8c8a99;font-weight: 600;margin-top: 10%;}
.profile-work a{text-decoration: none; color: #8c8a99;font-weight: 600; font-size: 14px;}
.profile-work ul{list-style: none;}
.profile-tab label{font-weight: 600;}
.profile-tab p{font-weight: 600;color: #8c8a99;}
</style>
@endsection
@section('content')
<div class="container-fluid emp-profile">
    <div class="row">
        <div class="col-md-4">
            
            <div class="sec1">
                <form action="{{ route('post:profile') }}" method="post" id="profileform" enctype="multipart/form-data">
                    @csrf
                    <input type="file" id="profile_pic" name="profile_pic" class="dropify" data-default-file="{{ $profile->profile_pic }}" />
                    <input id="form_submit_btn" type="submit" style="display:none;">
                </form>
            </div>
            
            @if($profile->user_type == 3)
            <div class="sec2">
                <div class="profile-work">
                    <h6>Address : </h6>
                    <a>{{ $profile->address }},</a><br/>
                    <a>{{ $profile->city_name }},</a><br/>
                    <a>{{ $profile->state_name }}, </a>
                    <a>{{ $profile->pincode }}</a><br/>
                </div>
            </div>
            @endif
            
        </div>
        <div class="col-md-6">
            <div class="profile-head">
                <h5> {{ $profile->name }} </h5>
                <h6> @if($profile->user_type == 1) Administrator @elseif($profile->user_type==3) Distributor @endif</h6><br>
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="primary_bank_tab" data-toggle="tab" href="#change_password" role="tab" aria-controls="change_password" aria-selected="false">Change Password</a>
                    </li>
                    @if($profile->user_type == 3)
                    <li class="nav-item">
                        <a class="nav-link" id="primary_bank_tab" data-toggle="tab" href="#primary_bank" role="tab" aria-controls="primary_bank" aria-selected="false">Primary Bank</a>
                    </li>
                    @endif
                </ul>
                
                <div class="tab-content profile-tab" id="myTabContent">
                    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                        <div class="row">
                            <div class="col-md-2">
                                <label>Name</label>
                            </div>
                            <div class="col-md-10">
                                <p>{{ $profile->name }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2">
                                <label>Mobile</label>
                            </div>
                            <div class="col-md-10">
                                <p>{{ $profile->mobile }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2">
                                <label>Email</label>
                            </div>
                            <div class="col-md-10">
                                <p>{{ $profile->email }}</p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-2">
                                <label>Profession</label>
                            </div>
                            <div class="col-md-10">
                                <p>{{ $profile->profession }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="primary_bank" role="tabpanel" aria-labelledby="primary_bank_tab">
                        
                        @if(isset($user_bank))
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <label>Bank</label>
                                </div>
                                <div class="col-md-8">
                                    <p>{{ $user_bank->bank_name }}</p>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <label>Account Number</label>
                                </div>
                                <div class="col-md-8">
                                    <p>{{ $user_bank->account_no }}</p>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <label>IFSC</label>
                                </div>
                                <div class="col-md-8">
                                    <p>{{ $user_bank->ifsc }}</p>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <label>Holder Name</label>
                                </div>
                                <div class="col-md-8">
                                    <p>{{ $user_bank->holder }}</p>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <label>Branch</label>
                                </div>
                                <div class="col-md-4">
                                    <p>{{ $user_bank->branch }}</p>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <label>City</label>
                                </div>
                                <div class="col-md-8">
                                    <p>{{ $user_bank->city }}</p>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <label>State</label>
                                </div>
                                <div class="col-md-8">
                                    <p>{{ $user_bank->state }}</p>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <label>Address</label>
                                </div>
                                <div class="col-md-8">
                                    <p>{{ $user_bank->address }}</p>
                                </div>
                            </div>
                        
                        @else
                            <div class="row">
                                <div class="col-md-12">
                                    <form action="{{ route('post:add_user_bank') }}" method="post" enctype="multipart/form-data">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-6 mb-4">
                                                <label for="phone">Bank Name:</label>
                                                <input type="text" class="form-control" id="bank_name" name="bank_name" placeholder="Enter Bank Name" 
                                                value="" autocomplete="off" required="">
                                            </div>
                                            
                                            <div class="col-md-6 mb-4">
                                                <label for="phone">Bank A/C no:</label>
                                                <input type="text" class="form-control" id="account_no" name="account_no" placeholder="Enter Account Number" 
                                                value="" autocomplete="off" required="">
                                            </div>
                                            
                                            <div class="col-md-6 mb-4">
                                                <label for="phone">Bank IFSC:</label>
                                                <input type="text" class="form-control" id="ifsc" name="ifsc" placeholder="Enter IFSC" 
                                                value=""autocomplete="off" required="">
                                            </div>
                                            
                                            <div class="col-md-6 mb-4">
                                                <label for="phone">Holder Name:</label>
                                                <input type="text" class="form-control" id="holder" name="holder" placeholder="Enter Holder Name" 
                                                 value="" autocomplete="off" required="">
                                            </div>
                                            
                                            <div class="col-md-6 mb-4">
                                                <label for="phone">Branch:</label>
                                                <input type="text" class="form-control" id="branch" name="branch"  placeholder="Enter Branch" 
                                                value="" autocomplete="off" required="">
                                            </div>
                                            
                                            <div class="col-md-6 mb-4">
                                                <label for="phone">City:</label>
                                                <input type="text" class="form-control" id="bank_city" name="bank_city"  placeholder="Enter Bank City" 
                                                value="" autocomplete="off" required="">
                                            </div>
                                            
                                            <div class="col-md-6 mb-4">
                                                <label for="phone">State:</label>
                                                <input type="text" class="form-control" id="bank_state" name="bank_state"  placeholder="Enter Bank State" 
                                                value="" autocomplete="off" required="">
                                            </div>
                                            
                                            <div class="col-md-6 mb-4">
                                                <label for="phone">Bank Address:</label>
                                                <input type="text" class="form-control" id="bank_address" name="bank_address"  placeholder="Enter Bank Address" 
                                                value="" autocomplete="off" required="">
                                            </div>
                                            
                                            <div class="col-md-6 mb-4">
                                                <button type="submit" class="btn btn-primary">Submit</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                
                            </div>
                        
                        @endif
                        
                        
                    
                        
                        
                      
                    </div>
                    <div class="tab-pane fade" id="change_password" role="tabpanel" aria-labelledby="change_password_tab">
                        <div class="row">
                                <div class="col-md-12">
                                    <form action="{{ route('post:change_password') }}" method="post" enctype="multipart/form-data">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-6 mb-4">
                                                <label for="phone">Old Password:</label>
                                                <input type="password" class="form-control" id="old_password" value="{{ old('old_password') }}" name="old_password" placeholder="Enter Old Password" autocomplete="off" required="">
                                            </div>
                                            
                                            <div class="col-md-6 mb-4">
                                                <label for="phone">New Password:</label>
                                                <input type="password" class="form-control" id="password" value="{{ old('password') }}" name="password" placeholder="Enter New Password" autocomplete="off" required="">
                                            </div>
                                            
                                            <div class="col-md-6 mb-4">
                                                <label for="phone">Confirm Password:</label>
                                                <input type="password" class="form-control" id="cnf_password" value="{{ old('cnf_password') }}" name="cnf_password" placeholder="Enter Renter Password" autocomplete="off" required="">
                                            </div>
                                            
                                            
                                            
                                            <div class="col-md-6 mb-4">
                                                <button type="submit" class="btn btn-primary">Submit</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                
                            </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
  
</div>
        
   
@endsection
@section('customjs')
<script>
 $(document).ready(function() {
    $('.dropify').dropify(); 
 });
 
$('#profile_pic').change(function(e) {
    $("#form_submit_btn").click();
});

</script>
@endsection