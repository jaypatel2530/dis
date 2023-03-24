@extends('layouts.app')
@section('title','Add Money')
@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
    
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4" id="ub-card">
                <div class="card-header py-3 ">
                  <h6 class="m-0 font-weight-bold">
                      <i class="fas fa-book pr-2"></i>Add Money</h6>
                </div>
                
                <div class="card-body">
                    
                   <div class="col-md-6 offset-md-300">
                        <form action="{{ route('post:add_money') }}" method="post" enctype="multipart/form-data">
                          @csrf
                          
                            <div class="form-group">
                              <label for="dropdown-test" class="control-label">Bank</label>
                                <select class="form-control" name="bank"  id="bank" required>
                                    @foreach($banks as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->bank_name}} - {{ $bank->acc}} - {{ $bank->bank_ifsc}}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group">
                              <label for="dropdown-test" class="control-label">Transfer Type</label>
                                <select class="form-control" name="transfer_type"  id="transfer_type" required>
                                    <option value="IMPS">IMPS</option>
                                    <option value="NEFT">NEFT</option>
                                    <option value="RTGS">RTGS</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Amount:</label>
                                <input type="text" class="form-control" id="amount" placeholder="Enter Amount" 
                                name="amount" maxlength="10" onkeypress="return isNumber(event)" autocomplete="off" required="">
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Bank Refference</label>
                                <input type="text" class="form-control" id="bank_ref" placeholder="Bank Refference" 
                                name="bank_ref" autocomplete="off" required="">
                            </div>
                            
                            <div class="form-group">
                                <label for="img">Bank Slip</label>
                                <input type="file" class="form-control" id="img" name="img" accept="image/*" required="">
                            </div>
                            
        
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                    </div> 
                   
                </div>
            </div>          
        </div>
    </div>
</div>
@endsection
@section('customjs')

@endsection