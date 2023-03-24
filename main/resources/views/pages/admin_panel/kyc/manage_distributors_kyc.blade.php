@extends('layouts.app')
@section('title','Distributors Pending KYC')
@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">

            <div class="card shadow mb-4" id="ub-card">
                <div class="card-header py-3 ">
                  <h6 class="m-0 font-weight-bold text-whitee">
                      <i class="fas  fa-address-card pr-2"></i>Distributors Pending KYC</h6>
                </div>
                <div class="card-body">
                    
                    <div class="table-responsive">
                        <table class="table display responsive nowrap" style="width:100%" id="datatable_ajax">
                            <thead>
                              <tr role="row" class="heading">
                                  <th>#</th>
                                  <th>Name</th>
                                  <th>Mobile</th>
                                  <th>City</th>
                                  <th>State</th>
                                  <th>View Docs</th>
                                  <th>Action</th>
                              </tr>
                            </thead>
                              <tbody></tbody>
                          </table>
                    </div>
                </div>          
            </div>
        </div>
    </div>
</div>


<!--KYC-Modal -->
<div class="modal fade" id="kycDocsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="exampleModalLabel">Kyc Documents</h6>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
               <div class="row">
                    <div class="col-md-4">
                        <span id="pan_image"></span>
                    </div>
                    <div class="col-md-4">
                        <span id="aadhaar_front_image"></span>
                    </div>
                    <div class="col-md-4">
                        <span id="aadhaar_back_image"></span>
                    </div>
                </div>
                 <hr>
            </div>
           
            <div class="col-md-12 mt-2">
                Name   : <span id="agent_name"></span><br>
                Mobile : <span id="agent_mobile"></span><br>
                PAN Number : <span id="pan_number"></span><br>
                Aadhaar Number : <span id="aadhaar_number"></span><br>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<!--KYC -Confirmation -->
<div class="modal fade" id="kyc_confirmation" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="exampleModalLabel">Confirmation</h6>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <h5>Are you sure you want to <span id="show_action_type"></span> this KYC?</h5>  
          
        Name : <span id="show_name"></span></br>
        Mobile : <span id="show_mobile"></span></br></br>
        
        <input type="hidden" name="input_action_id" id="input_action_id" val="">
        <input type="hidden" name="input_action_type" id="input_action_type" val="">
        
        <div class="row col-md-12 show_kyc_reject_input">
            <input class="form-control mb-2" type="text" id="kyc_reject_reason" name="kyc_reject_reason" 
            class="mb-2" placeholder="Reject Reason">
        </div>
            
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-primary final_kyc_btn">Yes</button>
        <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">No</button>
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
        "dom": 'Blfrtip',
        "buttons": [
          {
             extend: 'collection',
             text: 'Export',
             buttons: [ 'pdfHtml5', 'csvHtml5', 'copyHtml5', 'excelHtml5' ]
          }
        ],
        "ajax": {
            "url": "{{ route('get:distributors_pending_kyc_data')}}",
            "type": 'GET',
            "data": function (d) { }
        },
        "columns": [
            { "data": "id",   
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;    
                }
            },
            {"data": "name"}, 
            {"data": "mobile"}, 
            {"data": "cityname"}, 
            {"data": "statename"}, 
            
            {"data": 'id', name: 'id',"sClass": "text-center" ,"render": function (data, type, full, meta) {
                    var action = "";
                        action += '<button type="button" class="btn btn-primary btn-sm docs-btn" data-toggle="modal" uid="'+data+'" >KYC Docs</button>&nbsp;';
                    return action;
            } }, 
            
            {"data": 'kyc_status', name: 'kyc_status' ,"render": function (data, type, full, meta) {
                var action = "";
                action += '<button class="status_change btn btn-sm btn-primary" action_id="'+full.id+'" action_type="approve"  user_name="'+full.name+'" mobile="'+full.mobile+'">Approve</button>&nbsp;&nbsp;';
                action += '<button class="status_change btn btn-sm btn-danger" action_id="'+full.id+'" action_type="reject"  user_name="'+full.name+'" mobile="'+full.mobile+'">Reject</button>';
                return action;
            } },
        ],
    });
});


$(document).on('click', '.docs-btn', function () {
    
    var user_id = $(this).attr('uid');
    
    $.ajax({
        type: 'post',
        url: "{{ route('post:get_kyc_docs') }}",
        data: {"user_id" : user_id,"_token" : "{{ csrf_token() }}"},
        dataType: 'json',
        success: function (data) {
            if (data.status) {
                $('#doc_type').html(data.doc_name);
                $('#doc_number').html(data.doc_num);
               
                if(data.pan_image) {
                    $('#pan_image').html('<a href="'+data.pan_image+'" target="_blank"><img src='+data.pan_image+' alt="pan_image" height="80px;"></a>');
                }
                
                if(data.aadhaar_front_image) {
                    $('#aadhaar_front_image').html('<a href="'+data.aadhaar_front_image+'" target="_blank"><img src='+data.aadhaar_front_image+' alt="aadhar_front_image" height="80px;"></a>');
                }
                
                if(data.aadhaar_back_image) {
                    $('#aadhaar_back_image').html('<a href="'+data.aadhaar_back_image+'" target="_blank"><img src='+data.aadhaar_back_image+' alt="aadhaar_back_image" height="80px;"></a>');
                }
                
                $('#agent_name').html(data.name);
                $('#agent_mobile').html(data.mobile);
                $('#pan_number').html(data.pan_number);
                $('#aadhaar_number').html(data.aadhaar_number);
                
                $('#kycDocsModal').modal('show');
            }
            else {
                showMyToast("error",data.message); 
            }
        }
    });
});

$(document).on('click', '.status_change', function() { 
    
    var action_id = $(this).attr('action_id');
    var action_type = $(this).attr('action_type');
    
    var mobile = $(this).attr('mobile');
    var user_name = $(this).attr('user_name');
    
    $('#show_name').html(user_name);
    $('#show_mobile').html(mobile);
    $('#show_action_type').html(action_type);
    
    $('#input_action_id').val(action_id);
    $('#input_action_type').val(action_type);

    if(action_type == 'reject') {
        $('.show_kyc_reject_input').show();
    }else{
        $('.show_kyc_reject_input').hide();
    }

    $('#kyc_confirmation').modal('show');
});


$(document).on('click', '.final_kyc_btn', function() { 
    
    var status = $("#input_action_type").val();
    var user_id = $("#input_action_id").val();
    var kyc_reject_reason = $("#kyc_reject_reason").val();
    
    $.ajax({
        type: 'post',
        url: "{{ route('post:update_kyc_status') }}",
        data: {"status": status, "user_id" : user_id,'kyc_reject_reason' : kyc_reject_reason,"_token" : "{{ csrf_token() }}"},
        dataType: 'json',
        success: function (data) {
            if (data.status) {
                $('#kyc_confirmation').modal('hide');
                showMyToast("success",data.message); 
                
                $("#input_action_type").val('');
                $("#input_action_id").val('');
                
                $('#datatable_ajax').DataTable().ajax.reload();
                
                $("#kyc_reject_reason").val('');
                $('#show_kyc_reject_input').hide();
            }
            else {
                showMyToast("error",data.message); 
                $('#datatable_ajax').DataTable().ajax.reload();
            }
        }
    });
});

</script>
@endsection