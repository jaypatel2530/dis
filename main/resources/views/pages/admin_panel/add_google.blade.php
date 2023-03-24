@extends('layouts.app')
@section('title','Google Code')
@section('customcss') 
@endsection
@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
  <div class="card shadow mb-4" id="ub-card">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold">
          <i class="fas fa-user-plus pr-2"></i> Google Code</h6>
    </div>
    <div class="card-body">
      
        <div class="col-md-12">
            <form action="{{ route('post:add_google_code') }}" method="post" enctype="multipart/form-data">
              @csrf
              
                <div class="form-group row">
                    <div class="col-md-6 mb-4">
                        <label for="phone">Amount:</label>
                        <input type="text" class="form-control" id="amt" placeholder="Amount" name="amt" required="">
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <label for="phone">Google Code:</label>
                        <input type="text" class="form-control" id="gcode" placeholder="gcode" name="gcode" required="">
                    </div>
                    
                    
                
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>

    </div>
  </div>
</div>
@endsection
