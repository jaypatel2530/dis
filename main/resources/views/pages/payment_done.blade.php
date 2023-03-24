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
    <link href="{{ asset('form/vendor/mdi-font/css/material-design-iconic-font.min.css') }}" rel="stylesheet" media="all">
    <link href="{{ asset('form/vendor/font-awesome-4.7/css/font-awesome.min.css') }}" rel="stylesheet" media="all">
    <!-- Font special for pages-->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i" rel="stylesheet">

    <!-- Vendor CSS-->
    <link href="{{ asset('form/vendor/select2/select2.min.css') }}" rel="stylesheet" media="all">
    <link href="{{ asset('form/vendor/datepicker/daterangepicker.css') }}" rel="stylesheet" media="all">

    <!-- Main CSS-->
    <link href="{{ asset('form/css/main.css') }}" rel="stylesheet" media="all">
</head>

<body>
    <div class="page-wrapper bg-gra-03 p-t-45 p-b-50">
        <div class="wrapper wrapper--w790">
            <div class="card card-5">
                <div class="card-heading">
                    <h2 class="title">Thank you</h2>
                </div>
                <div class="card-body">
                    <form method="POST">
                        
                        
                        
                        
                        
                        
                        <div class="form-row p-t-20">
                            <label class="label label--block">Dear {{ $user->name }} ,</label>
                            <div class="p-t-15">
                                
                                @if($user->payment_type != 'PayPal')
                                <p>Thank you for renewing your membership the Dacorum Indian Society Membership form. Your membership is only confirmed upon receipt of payment.</p>
                                <br>
                                <p>Please pay your membership fees using below bank account details and Membership ID:</p>
                                <br>
                                @else
                                <p>Thank you for making the payment and submitting the Dacorum Indian Society renewing Membership Form. We have received your payment.</p>
                                <br>
                                @endif
                                <strong>@if($user->membership == 'Family') Family @else Single @endif Membership For {{ $user->membership_year }} @if($user->membership_year == 1) Year @else Years @endif </strong>
                                <br><br>
                                <strong>Membership Fee :  £{{ $user->membership_year*$fee }}</strong>
                                <br><br>
                                @if($user->payment_type != 'PayPal')
                                <label class="label label--block">Electronic Payments details:-</label><br>
                                Account Name: Dacorum Indian Society <br>
                                Sort Code 30-94-08 <br>
                                Acc. No. 02802554 <br>
                                Payment Reference {{ $user->member_id }} <br>
                                <br>
                                @endif
                                
                                <label class="label label--block">Membership Expire On  : {{ date('d-m-Y', strtotime('+'.$user->membership_year.' years')) }}</label><br>
                                <br>
                                
                                <label class="label label--block">For any queries please contact us on: dis.hemel@gmail.com<br><br>
                                regards,<br>
                                DIS Membership Secretary,<br>
                                This e-mail was sent from a contact form on DACORUM INDIAN SOCIETY (https://dacorumindiansociety.co.uk)</label>
                            </div>
                        </div>
                        
                        
                    </form>
                    <center><a class="btn btn--radius-2 btn--blue" href="/">Go To Dashboard</a></center>
                </div>
            </div>
        </div>
    </div>

    <!-- Jquery JS-->
    <script src="{{ asset('form/vendor/jquery/jquery.min.js') }}"></script>
    <!-- Vendor JS-->
    <script src="{{ asset('form/vendor/select2/select2.min.js') }}"></script>
    <script src="{{ asset('form/vendor/datepicker/moment.min.js') }}"></script>
    <script src="{{ asset('form/vendor/datepicker/daterangepicker.js') }}"></script>

    <!-- Main JS-->
    <script src="{{ asset('form/js/global.js') }}"></script>

</body><!-- This templates was made by Colorlib (https://colorlib.com) -->

</html>
<!-- end document-->