@extends('layouts.app')
@section('title','Upload KYC Documents')
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
  <div class="card shadow mb-4" id="ub-card">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">
          <i class="fas fa-user-plus pr-2"></i> Upload KYC Documents</h6>
    </div>
    <div class="card-body">
      
        <div class="col-md-12">
            <form action="{{ route('post:upload_kyc_documents') }}" method="post" enctype="multipart/form-data">
              @csrf
              
                <div class="form-group row">
                    
                    <div class="col-md-4 mb-4">
                        <label for="pan_image">PAN Image</label>
                        @if(isset($edit_company->logo))
                          <input type="file" id="pan_image" name="pan_image" class="dropify" data-default-file="{{ asset('uploads/kycdocs/'.$edit_company->logo) }}" />
                        @else
                          <input type="file" id="pan_image" name="pan_image" class="dropify"  data-default-file="" required="" />
                        @endif
                     </div>
                     
                    <div class="col-md-4 mb-4">
                        <label for="aadhaar_front_image">Aadhaar Front Image</label>
                        @if(isset($edit_company->logo))
                          <input type="file" id="aadhaar_front_image" name="aadhaar_front_image" class="dropify" data-default-file="{{ asset('uploads/kycdocs/'.$edit_company->logo) }}" />
                        @else
                          <input type="file" id="aadhaar_front_image" name="aadhaar_front_image" class="dropify"  data-default-file="" required="" />
                        @endif
                    </div> 
                    
                    <div class="col-md-4 mb-4">
                        <label for="aadhaar_back_image">Aadhaar Back Image</label>
                        @if(isset($edit_company->logo))
                          <input type="file" id="aadhaar_back_image" name="aadhaar_back_image" class="dropify" data-default-file="{{ asset('uploads/kycdocs/'.$edit_company->logo) }}" />
                        @else
                          <input type="file" id="aadhaar_back_image" name="aadhaar_back_image" class="dropify"  data-default-file="" required="" />
                        @endif
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <label for="pan_number">PAN Number:</label>
                        <input type="text" class="form-control" id="pan_number" name="pan_number" value="" placeholder="" 
                         autocomplete="off" maxlength="10" required="" style="text-transform: uppercase">
                    </div>
                
                    <div class="col-md-6 mb-4">
                        <label for="aadhaar_number">Aadhaar Number:</label>
                        <input type="text" class="form-control" id="aadhaar_number" name="aadhaar_number" value=""  placeholder="" 
                        maxlength="12" onkeypress="return isNumber(event)"  autocomplete="off" required="">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Submit</button>
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