@extends('layouts.app')
@section('title','Add Event')
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
            <i class="fas fa-user-plus pr-2"></i> Add Event</h6>
        </span>
    </div>
    <div class="card-body">
      
        <div class="col-md-12">
            <form action="{{ route('post:add_event') }}" method="post" enctype="multipart/form-data">
              @csrf
              
                <div class="form-group row">
                    
                
                    <div class="col-md-6 mb-3">
                        <label for="name">Event Name: </label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}"  
                        placeholder="Enter Event Name" autocomplete="off" required="">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="name">Date: </label>
                        <input type="date" class="form-control" id="event_date" name="event_date" value="{{ old('event_date') }}"  
                        placeholder="Enter date" autocomplete="off" required="">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="email">Venue:</label>
                        <input type="text" class="form-control" id="venue" name="venue" value="{{ old('venue') }}" 
                        placeholder="Venue" autocomplete="off" required="">
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


</script>
@endsection