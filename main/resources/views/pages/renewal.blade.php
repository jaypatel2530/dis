@extends('layouts.app')
@section('title','Renewal')
@section('content')
<style>
    .bg-set{
        background: rgb(2,0,36);
        background: linear-gradient(90deg, rgba(2,0,36,1) 0%, rgba(9,9,121,0.6698879380853904) 0%, rgba(0,212,255,1) 100%);
        border: 1px solid aqua;
        border-radius: 5px;
    }
    
    .marquee {
        top: 6em;
        position: relative;
        box-sizing: border-box;
        animation: marquee 15s linear infinite;
    }
    
    .marquee:hover {
        animation-play-state: paused;
    }
    
    /* Make it move! */
    @keyframes marquee {
        0%   { top:   8em }
        100% { top: -11em }
    }
</style>
<div class="container-fluid">
    <div class="row bg-set">
        
        <div class="col-xl-8 offset-md-2 offset-sm-0 col-md-6 mb-4 mt-4">
          <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
              <div class="col-md-12">
               <form action="{{ route('post:member_renewal') }}" method="post" enctype="multipart/form-data" id="myform">
                   @csrf
                         <div class="form-group row">
                            <div class="col-md-12 mb-3">
                                  <label class="label label--block">Membership Type:*</label>
                                    <div class="p-t-15">
                                        <label class="radio-container m-r-55">Family
                                            <input type="radio" name="membership" onclick="getMemberYear()" value="Family" required>
                                            <span class="checkmark"></span>
                                        </label>
                                        <label class="radio-container">Single
                                            <input type="radio" name="membership" onclick="getMemberYear()" value="Single" required>
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                            
                            <div class="col-md-12 mb-3">
                                <div class="form-row" style="display:none" id="add_year">
                                    <div id="lodder_year">
                                        <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
                                        <lottie-player src="https://assets3.lottiefiles.com/packages/lf20_rlzitsb5.json"  background="transparent"  speed="1"  style="width: 300px; height: 75px;"  loop  autoplay></lottie-player>
                                    </div>
                                    <div id="show_year">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                            <div class="form-row" style="display:none" id="add_paymment_method">
                                <div id="lodder_paymment_method">
                                    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
                                    <lottie-player src="https://assets3.lottiefiles.com/packages/lf20_rlzitsb5.json"  background="transparent"  speed="1"  style="width: 300px; height: 75px;"  loop  autoplay></lottie-player>
                                </div>
                                <div id="show_paymment_method">
                                </div>
                            </div>
                            </div>
                            
                            <div id="print_json" style="color:red;">
                            
                            </div>
                            <div class="col-md-12 mb-3">
                            <div id="final_button">
                                
                            </div>
                            </div>
                            
                            <input type="hidden" name="paypal_id" id="paypal_id">
                            <input type="hidden" name="payer_name" id="payer_name">
                            <input type="hidden" name="payer_surname" id="payer_surname">
                            <input type="hidden" name="payer_email" id="payer_email">
                            <input type="hidden" name="payer_id" id="payer_id">
                            <input type="hidden" name="payment_time" id="payment_time">
                            <input type="hidden" name="status" id="status">
                            <input type="hidden" name="amount" id="amount">    
                         </div>
                     </form>
              </div>
              
            </div>
          </div>
        </div>
        
        
    </div>
    
</div>
@endsection
@section('customjs')
<script src="https://www.paypal.com/sdk/js?client-id=AWx1gpCaWDcvW0Tfxwf6gqMuvRkxVyhYPKJIEB6KVO3AdxxZJVnNaFxbQkemez5VwrqpkcMbRN8ZGJ-j"></script>
<script>
    function getMemberYear(){
        var member_type = $('input[name="membership"]:checked').val();
        $("#add_year").css('display','block');
        $("#lodder_year").css('display','block');
        $("#show_year").html('');
        
        if(member_type == 'family'){
            $("#add_member_button_section").css('display','block');
        }else{
            $("#add_member_button_section").css('display','none');
            $("#member_details").html('');
        }
        
        $.ajax({
	        type: 'post',
	        dataType:'html',
	        url: "{{ route('post:get_member_years') }}",
	        data: {"member_type":member_type,"_token":"{{ csrf_token() }}"},
	        success: function (result) {
						// alert(result);
			    $("#lodder_year").css('display','none');			
	            $("#show_year").html(result);
	            
	            
	        }
	    });
    }
    
    function getPaymentMethod(){
        $("#add_paymment_method").css('display','block');
        $("#lodder_paymment_method").css('display','block');
        $("#show_paymment_method").html('');
        $.ajax({
	        type: 'post',
	        dataType:'html',
	        url: "{{ route('post:get_payment_method') }}",
	        data: {"_token":"{{ csrf_token() }}"},
	        success: function (result) {
						// alert(result);
			    $("#lodder_paymment_method").css('display','none');			
	            $("#show_paymment_method").html(result);
	            paymentMethodSelect();
	            
	            
	        }
	    });
    }
    
    function paymentMethodSelect(){
        var member_type = $('input[name="membership"]:checked').val();
        var member_year = $('input[name="membershipyear"]:checked').val();
        var payment_mode = $('input[name="payment"]:checked').val();
        $("#final_button").html('');
        if(member_type == 'Family'){
            var total = member_year * 15;
        }else{
            var total = member_year * 7;
        }
        
        if(payment_mode == 'bank'){
            $("#final_button").html('<button class="btn btn-primary btn-icon-split btn-lg" type="submit" id="store_data"><span class="icon text-white-50"><i class="fas fa-arrow-right"></i></span><span class="text">Register Now</span></button>');
        }else{
            paypal.Buttons({
                createOrder: function (data, actions) {
                    return actions.order.create({
                        purchase_units: [{
                            amount: {
                                value: total
                            }
                        }]
                    });
                },
                onApprove: function (data, actions) {
                    return actions.order.capture().then(function (details) {
                        // window.location.href = "approve.html";
                        console.log(details);
                        $("#paypal_id").val(details.id);
                        $("#payer_name").val(details.payer.name.given_name);
                        $("#payer_surname").val(details.payer.name.surname);
                        $("#payer_email").val(details.payer.email_address);
                        $("#payer_id").val(details.payer.payer_id);
                        $("#payment_time").val(details.update_time);
                        $("#status").val(details.status);
                        $("#amount").val(total);
                        
                        $("#final_button").html('<button class="btn btn-primary btn-icon-split btn-lg" type="submit" id="store_data"><span class="icon text-white-50"><i class="fas fa-arrow-right"></i></span><span class="text">Register Now</span></button>');
                        
                        $("#myform").submit();
                    });
                },
                onError: function (err) {
                    
                    $("#print_json").html('Something went wrong with the payment; please try again later or use a different payment method.');
                    $("#final_button").html('<button class="btn btn-primary btn-icon-split btn-lg" type="submit" id="store_data"><span class="icon text-white-50"><i class="fas fa-arrow-right"></i></span><span class="text">Register Now</span></button>');
                    
                }
            }).render('#final_button'); // Display payment options on your web page
        }
        
        
    }
</script>
@endsection