@extends('layouts.app')
@section('title','Add App Banners')
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
          <i class="fas fa-user-plus pr-2"></i> Add App Banners</h6>
    </div>
    <div class="card-body">
      
        <div class="col-md-12">
            <form action="{{ route('post:add_app_banners') }}" method="post" enctype="multipart/form-data">
              @csrf
                <div class="form-group row">        
                    <div class="col-md-6">
                        <label for="banner_image">Banner Image</label>
                        <input type="file" id="banner_image" name="banner_image" class="dropify"  accept="image/*" data-default-file="" required="" />
                    </div>
                </div>
                    
                <div class="form-group row">
                    <div class="col-md-6">
                        <label for="title">Title:</label>
                        <input type="text" class="form-control" id="title" name="title" placeholder="Title" value="" required="">
                    </div>
                </div>
                
                <div class="form-group row">    
                    <div class="col-md-6"> 
                        <label for="link">Link:</label>
                        <input type="text" class="form-control" id="link" name="link" placeholder="Link" value="" required="">
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