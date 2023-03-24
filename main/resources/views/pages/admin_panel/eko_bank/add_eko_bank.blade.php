@extends('layouts.app')
@section('title','Add Eko Banks')
@section('customcss') 
@endsection
@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
  <div class="card shadow mb-4" id="ub-card">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold">
          <i class="fas fa-user-plus pr-2"></i> Add Eko Banks</h6>
    </div>
    <div class="card-body">
      
        <div class="col-md-12">
            <form action="{{ route('post:add_eko_bank') }}" method="post" enctype="multipart/form-data">
              @csrf
              
                <div class="form-group row">
                    
                    <div class="col-md-6 mb-4">
                      <label for="dropdown-test" class="control-label">Select Bank</label>
                        <select class="form-control" name="bank_id" required>
                            <option value="7">ICICI Bank</option>
                            <option value="16">Yes Bank</option>
                            <option value="48">IndusInd Bank</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label for="phone">Account Holder Name</label>
                        <input type="text" class="form-control" name="holder_name" value="{{ old('holder_name') }}"   
                        placeholder="Account holder name" autocomplete="off" required="">
                    </div>
                
                    <div class="col-md-6 mb-4">
                        <label for="phone">Account Number</label>
                        <input type="text" class="form-control" name="account_number" value="{{ old('account_number') }}"   
                        placeholder="Account number" autocomplete="off" required="">
                    </div>
                
                    <div class="col-md-6 mb-4">
                        <label for="phone">IFSC</label>
                        <input type="text" class="form-control" name="ifsc" value="{{ old('ifsc') }}"   
                        placeholder="IFSC" autocomplete="off" required="">
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
</script>
@endsection