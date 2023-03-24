@extends('layouts.app')
@section('title','App Settings')
@section('customcss') 
<script src="https://cdn.tiny.cloud/1/px1y39yk4mbvfra2eq00xl0hlstbjpdnmnaab7bqsjuxd1o9/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
@endsection
@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
  <div class="card shadow mb-4" id="ub-card">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold">
          <i class="fas fa-user-plus pr-2"></i> App Settings</h6>
    </div>
    <div class="card-body">
      
        <div class="col-md-12">
            <form action="{{ route('post:app_settings') }}" method="post" enctype="multipart/form-data">
              @csrf
              
                <div class="form-group row">
                    <div class="col-md-6 mb-4">
                        <label for="phone">TERMS & CONDITIONS:</label>
                        <textarea name="tncs">
                             {{ $data->tncs }}
                          </textarea>
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
    tinymce.init({
      selector: 'textarea',
      plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount checklist mediaembed casechange export formatpainter pageembed linkchecker a11ychecker tinymcespellchecker permanentpen powerpaste advtable advcode editimage tinycomments tableofcontents footnotes mergetags autocorrect typography inlinecss',
      toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
      tinycomments_mode: 'embedded',
      tinycomments_author: 'Author name',
      mergetags_list: [
        { value: 'First.Name', title: 'First Name' },
        { value: 'Email', title: 'Email' },
      ]
    });
  </script>
@endsection