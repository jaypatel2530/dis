@extends('layouts.app')
@section('title','Send Notification')
@section('customcss') 
@endsection
@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
  <div class="card shadow mb-4" id="ub-card">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold">
          <i class="fas fa-user-plus pr-2"></i> Send Notification</h6>
    </div>
    <div class="card-body">
      
        <div class="col-md-12">
            <form action="{{ route('post:send_notification') }}" method="post" enctype="multipart/form-data">
              @csrf
              
                <div class="form-group row">
                    <div class="col-md-6">
                        <label for="title">Title:</label>
                        <input type="text" class="form-control" id="title" name="title" placeholder="Title" value="" required="">
                    </div>
                </div>
                
                <div class="form-group row">    
                    <div class="col-md-6">
                        <label for="message">Message:</label>
                        <input type="text" class="form-control" id="message" name="message" placeholder="Message" value="" required="">
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