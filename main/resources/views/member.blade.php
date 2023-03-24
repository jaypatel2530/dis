<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags-->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="keywords" content=" ">

    <!-- Title Page-->
    <title>Dacorum Indian Society</title>

    <!-- Icons font CSS-->
    <link href="form/vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">
    <link href="form/vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <!-- Font special for pages-->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i" rel="stylesheet">

    <!-- Vendor CSS-->
    <link href="form/vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="form/vendor/datepicker/daterangepicker.css" rel="stylesheet" media="all">

    <!-- Main CSS-->
    <link href="form/css/main.css" rel="stylesheet" media="all">
    <style>
        .subtitle{
            text-transform: uppercase;
            font-weight: 500;
            text-align: center;
                margin-bottom: 7%;
        }
    </style>
</head>

<body>
    <div class="page-wrapper bg-gra-03 p-t-45 p-b-50">
        <div class="wrapper wrapper--w790">
            <div class="card card-5">
                <div class="card-heading">
                    <h2 class="title">Dacorum Indian Society</h2>
                    
                </div>
                
                <div class="card-body">
                    <center><h4 class="subtitle">Dacorum Indian Society Membership Form.</h4></center>
                    <center>
                    
                    @if (Session::has('success'))
                    <!--<h3 style="color: #5fab73;">{{ Session::get('success') }}</h3>-->
                    <!--<br>-->
                    @endif
                    @if (Session::has('error'))
                    <h5 style="color: #ef6b6b;">{{ Session::get('error') }}</h65>
                    <br><br>
                    @endif
                    </center>
                    
                    <form action="{{ route('post:member') }}" method="POST" id="myform">
                        @csrf
                        <div class="form-row m-b-55">
                            <div class="name">Name*</div>
                            <div class="value">
                                <div class="row row-space">
                                    <div class="col-2">
                                        <div class="input-group-desc">
                                            <input class="input--style-5" type="text" name="first_name" value="{{ old('first_name') }}" onkeypress="return (event.charCode > 64 && event.charCode < 91) || (event.charCode > 96 && event.charCode < 123) || (event.charCode == 32) || (event.charCode == 39) || (event.charCode == 45)" required>
                                            <label class="label--desc">first name</label>
                                        </div>
                                    </div>
                                    <div class="col-2">
                                        <div class="input-group-desc">
                                            <input class="input--style-5" type="text" name="last_name" value="{{ old('last_name') }}" onkeypress="return (event.charCode > 64 && event.charCode < 91) || (event.charCode > 96 && event.charCode < 123) || (event.charCode == 32) || (event.charCode == 39) || (event.charCode == 45)" required>
                                            <label class="label--desc">last name</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="name">Email*</div>
                            <div class="value">
                                <div class="input-group">
                                    <input class="input--style-5" type="email" name="email" value="{{ old('email') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="name">Mobile No*</div>
                            <div class="value">
                                <div class="input-group">
                                    
                                    <input class="input--style-5" type="tel" maxlength="11" minlength="11" onkeypress="return isNumber(event)" name="mobile" value="{{ old('mobile') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="name">POSTCODE*</div>
                            <div class="value">
                                <div class="input-group">
                                    <input class="input--style-5" type="text" id="postcode" name="postcode" value="{{ old('postcode') }}" onkeyup="postcodelookup()" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="name">Address Line 1*</div>
                            <div class="value">
                                <div class="input-group">
                                    <input class="input--style-5" type="text" id="address_line_1" value="{{ old('address_line_1') }}" name="address_line_1" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="name">Address Line 2</div>
                            <div class="value">
                                <div class="input-group">
                                    <input class="input--style-5" type="text" id="address_line_2" value="{{ old('address_line_2') }}" name="address_line_2">
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="name">Town*</div>
                            <div class="value">
                                <div class="input-group">
                                    
                                    <input class="input--style-5" type="text" name="town" id="town" value="{{ old('town') }}" required>
                                </div>
                            </div>
                        </div>
                        
                        
                        
                        
                        
                        
                        <div class="form-row">
                            <div class="name">Gender:*</div>
                            <div class="value">
                                <div class="input-group">
                                    <div class="rs-select2 js-select-simple select--no-search">
                                        <select name="gender" required>
                                            <!--<option disabled="disabled" selected="selected">Choose option</option>-->
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                        <div class="select-dropdown"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="name">Profession</div>
                            <div class="value">
                                <div class="input-group">
                                    <input class="input--style-5" type="text" name="profession"  value="{{ old('profession') }}">
                                </div>
                            </div>
                        </div>
                        
                        
                        <div class="form-row">
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
                        
                        <div style="display:none" id="add_member_button_section">
                            <button class="btn btn--radius-2 btn--blue" title="Add New Member" type="button" onclick="addMember()"><i class="fa fa-user-plus" aria-hidden="true"></i> Add Family Member</button>
                            <br>
                            <br>
                        </div>
                        <div id="member_details">
                            
                        </div>
                        
                        <div class="form-row" style="display:none" id="add_year">
                            <div id="lodder_year">
                                <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
                                <lottie-player src="https://assets3.lottiefiles.com/packages/lf20_rlzitsb5.json"  background="transparent"  speed="1"  style="width: 300px; height: 75px;"  loop  autoplay></lottie-player>
                            </div>
                            <div id="show_year">
                            </div>
                        </div>
                        
                        <div class="form-row" style="display:none" id="add_paymment_method">
                            <div id="lodder_paymment_method">
                                <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
                                <lottie-player src="https://assets3.lottiefiles.com/packages/lf20_rlzitsb5.json"  background="transparent"  speed="1"  style="width: 300px; height: 75px;"  loop  autoplay></lottie-player>
                            </div>
                            <div id="show_paymment_method">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <label class="label label--block">Add phone no. to DIS WhatsApp group?:*</label>
                            <div class="p-t-15">
                                <label class="radio-container m-r-55">Yes
                                    <input type="radio" checked="checked" name="exist" value="1" >
                                    <span class="checkmark"></span>
                                </label>
                                <label class="radio-container">No
                                    <input type="radio" name="exist" value="0">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                        </div>
                        
                        
                        <div class="form-row">
                            <label class="label label--block"></label>
                            <div class="p-t-15">
                                <label class="radio-container m-r-55">Please confirm that you agree to DIS Membership <a href="/terms" target="_blank">Terms & Conditions</a> *
                                    <input type="checkbox" name="tnc" required>
                                    <span class="checkmark_sq"></span>
                                </label>
                                
                            </div>
                        </div>
                        <div id="print_json" style="color:red;">
                            
                        </div>
                        <div id="final_button">
                            
                        </div>
                        
                        <input type="hidden" name="paypal_id" id="paypal_id">
                        <input type="hidden" name="payer_name" id="payer_name">
                        <input type="hidden" name="payer_surname" id="payer_surname">
                        <input type="hidden" name="payer_email" id="payer_email">
                        <input type="hidden" name="payer_id" id="payer_id">
                        <input type="hidden" name="payment_time" id="payment_time">
                        <input type="hidden" name="status" id="status">
                        <input type="hidden" name="amount" id="amount">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Jquery JS-->
    <script src="form/vendor/jquery/jquery.min.js"></script>
    <!-- Vendor JS-->
    <script src="form/vendor/select2/select2.min.js"></script>
    <script src="form/vendor/datepicker/moment.min.js"></script>
    <script src="form/vendor/datepicker/daterangepicker.js"></script>

    <!-- Main JS-->
    <script src="form/js/global.js"></script>
    <script src="https://www.paypal.com/sdk/js?client-id=AWx1gpCaWDcvW0Tfxwf6gqMuvRkxVyhYPKJIEB6KVO3AdxxZJVnNaFxbQkemez5VwrqpkcMbRN8ZGJ-j"></script>
    <script>
    function postcodelookup(){
        var postcode = $("#postcode").val();
        if(postcode.length == 7){
            
        
            var regex = /^[A-Z]{1,2}[0-9]{1,2} ?[0-9][A-Z]{2}$/i;
            if(regex.test(postcode)){
                $.ajax({
	        type: 'get',
	        dataType:'json',
	        url: "https://api.postcodes.io/postcodes/"+postcode,
	        success: function (result) {
				// 		alert(result);
	            
	            if(result.status == 200){
	                $("#town").val(result.result.parliamentary_constituency);
	                $("#postcode").css('background','#e5e5e5');
	            }else{
	                $("#postcode").css('background','#f9abab');
	            }
	            
	        }
	    });
            }else{
                $("#postcode").css('background','#f9abab');
            }
        }
    }
    function addMember(){
        var len=$(".member_add").length;
        // var member_type = $('input[name="membership"]:checked').val();
        
        if(len == 5){
            alert("Sorry! You can add maximum 5 family members.");
            return false;
        }
        
        $.ajax({
	        type: 'post',
	        dataType:'html',
	        url: "{{ route('post:add_member_front') }}",
	        data: {"len":len,"_token":"{{ csrf_token() }}"},
	        success: function (result) {
						// alert(result);
	            $("#member_details").append(result);
	            
	            try {
                    var selectSimple = $('.js-select-simple');
                
                    selectSimple.each(function () {
                        var that = $(this);
                        var selectBox = that.find('select');
                        var selectDropdown = that.find('.select-dropdown');
                        selectBox.select2({
                            dropdownParent: selectDropdown
                        });
                    });
                
                } catch (err) {
                    console.log(err);
                }
	        }
	    });
    }
    
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
            $("#final_button").html('<button class="btn btn--radius-2 btn--green" type="submit" id="store_data">Register Now</button>');
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
                        
                        $("#final_button").html('<button class="btn btn--radius-2 btn--green" type="submit" id="store_data">Register Now</button>');
                        
                        $("#myform").submit();
                    });
                },
                onError: function (err) {
                    
                    $("#print_json").html('Something went wrong with the payment; please try again later or use a different payment method.');
                    $("#final_button").html('<button class="btn btn--radius-2 btn--green" type="submit" id="store_data">Register Now</button>');
                    
                }
            }).render('#final_button'); // Display payment options on your web page
        }
        
        
    }
    
    function removeMember(id){
        var divid=$("#membersection_"+id).remove();;
    }
    
    function isNumber(evt) {
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if(charCode == 46) { return true; }
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    }
    </script>

</body><!-- This templates was made by Colorlib (https://colorlib.com) -->

</html>
<!-- end document-->