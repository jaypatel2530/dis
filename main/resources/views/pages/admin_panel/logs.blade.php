@extends('layouts.app')
@section('title','Logs')
@section('content')
<div class="container-fluid">
  <div class="card shadow mb-4" id="ub-card">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold">
          <i class="fas fa-user pr-2"></i>Logs</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table display responsive nowrap" style="width:100%" id="datatable_ajax">
                  <thead>
                      <tr role="row" class="heading">
                          <th>#</th>
                          <th>Request</th>
                          <th>Response</th>
                          <th>Name</th>
                          <th>CreatedAt</th>
                          <th>UpdateAt</th>
                          
                      </tr>
                  </thead>
                  <tbody>
                      @foreach($data as $k => $r)
                        <tr>
                            <td>{{ $k+1 }}</td>
                            <td>{{ $r->request }}</td>
                            <td>{{ $r->response }}</td>
                            <td>{{ $r->api_name }}</td>
                            <td>{{ $r->created_at }}</td>
                            <td>{{ $r->updated_at }}</td>
                        </tr>
                      @endforeach
                  </tbody>
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
         
    });
});


</script>
@endsection