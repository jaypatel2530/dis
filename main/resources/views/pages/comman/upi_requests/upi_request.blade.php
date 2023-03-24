@extends('layouts.app')
@section('title','UPI Request')
@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
    
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4" id="ub-card">
                <div class="card-header py-3 ">
                  <h6 class="m-0 font-weight-bold">
                      <i class="fas fa-book pr-2"></i>UPI Request</h6>
                </div>
                
                <div class="card-body">
                    
                   <div class="col-md-6 offset-md-300">
                        <form  action="{{ route('post:upi_request') }}" method="post" enctype="multipart/form-data">
                          @csrf
                          
                            <div class="form-group">
                                <label for="upi_id">UPI ID</label>
                                <input type="text" class="form-control" id="upi_id" placeholder="UPI ID" name="upi_id" autocomplete="off" required="">
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Amount:</label>
                                <input type="text" class="form-control" id="amount" placeholder="Enter Amount" 
                                name="amount" maxlength="10" onkeypress="return isNumber(event)" autocomplete="off" required="">
                            </div>
                            
                            <button type="submit" class="btn btn-primary submit-btn">Submit</button>
                        </form>
                    </div> 
                   
                </div>
            </div>          
        </div>
    </div>
</div>
@endsection
@section('customjs')
<script>
    
$("#upi_id").blur(function() {

    var upi = $('#upi_id').val();

    var match = /[a-zA-Z0-9._]{3,}@[a-zA-Z]{3,}/;
    var result = match.test(upi); // True
    
    if(result) {
        $('.submit-btn').prop('disabled',false);
    }
    else{
        $('.submit-btn').prop('disabled',true);
        showMyToast('error','Please enter valid UPI');
    }
    
});
</script>
@endsection