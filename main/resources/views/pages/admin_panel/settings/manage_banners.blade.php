@extends('layouts.app')
@section('title','Manage App Banners')
@section('content')
<div class="container-fluid">
  <div class="card shadow mb-4" id="ub-card">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold">
          <i class="fas fa-user pr-2"></i>Manage App Banners</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table display responsive nowrap" style="width:100%" id="datatable_ajax">
                  <thead>
                      <tr role="row" class="heading">
                          <th>#</th>
                          <th>Image</th>
                          <th>Title</th>
                          <th>Link</th>
                          <th>Action</th>
                      </tr>
                  </thead>
                  <tbody></tbody>
              </table>
          </div>
    </div>
  </div>
</div>
@endsection
@section('customjs')
<script>
$(document).ready(function() {
    oTable = $('#datatable_ajax').DataTable({
        "processing": false,
        "serverside": true,
        "dom": 'lfrtip',
        "ajax": {
            "url": "{{ route('get:manage_app_banners')}}",
              "type": 'GET',
              data: function (d) {
                  d.json_data = "json_data";
              }
        },
        "columns": [
            { "data": "id",   
                render: function (data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;    
                }
            },
            {"data": 'image', name: 'image' , "render": function (data, type, full, meta) {
                return "<img src=\"{{ asset('uploads/app_banners/') }}/" + data + "\" height=\"50\"/>";
            }}, 
            {"data": "title"},  
            {"data": "link"},
            
            {"data": 'id', "name": 'id', "sClass": "salaryright" , "render": function (data, type, full, meta) {

              var action = "";
              action+='<button class="btn btn-danger btn-sm banner-delete-btn" title="Delete" bannerid="'+data+'" > <span class="fa fa-trash"></span></button>&nbsp;';
              return action;    }
            }
            
        ],
         
    });
});

$(document).on('click', '.banner-delete-btn', function (e) {
      e.preventDefault();

       var confirmD = confirm("Are you sure,you want to delete this item?");
        if (!confirmD) {
          return false;
        }

        var id = $(this).attr('bannerid');
        var parent = $(this).parent();

        $.ajax({
            type: 'post',
            url: "{{ route('post:delete_banner') }}",
            data: {'id': id,'_token' : '{{ csrf_token() }}'},
            success: function (result) {
                if (result.success == true) {
                    showMyToast('success', result.message);
                    $('#datatable_ajax').DataTable().ajax.reload();
                    
                } else {
                    showMyToast('error', result.message);
                }
            }
        })
    });
</script>
@endsection