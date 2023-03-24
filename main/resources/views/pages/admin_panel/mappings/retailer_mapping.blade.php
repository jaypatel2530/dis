@extends('layouts.app')
@section('title','Retailer Mapping')
@section('customcss') 
@endsection
@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
  <div class="card shadow mb-4" id="ub-card">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold">
          <i class="fas fa-user-plus pr-2"></i> Retailer Mapping</h6>
    </div>
    <div class="card-body">
      
        <div class="col-md-12">
            <form action="{{ route('post:retailer_mapping') }}" method="post" enctype="multipart/form-data">
              @csrf
              
                <div class="form-group ">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                          <label for="dropdown-test" class="control-label">Select Distributor</label>
                            <select class="form-control select2" name="toplevel_id"  id="toplevel_id" required>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name}}-{{ $user->mobile }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="phone">Retailer Mobile:</label>
                            <input type="text" class="form-control" id="mobile" placeholder="Retailer mobile" 
                            name="mobile" value="{{ old('mobile') }}" maxlength="10" onkeypress="return isNumber(event)"  autocomplete="off" required="">
                        </div>
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