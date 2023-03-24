<html lang="en" class="">
    <head>
        <meta charset="UTF-8">
        <title>Dacorum Indian Society</title>
<style>
html, body {
	margin: 0;
	padding: 0;
	height: 100%;
}

#root {
	background: linear-gradient(to top right, #08aeea 0%, #b721ff 100%);
	font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen',
      'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue',
      sans-serif;
	font-size: 16px;
	 -webkit-font-smoothing: antialiased;
	 -moz-osx-font-smoothing: grayscale;
	width: 100%;
	height: 100%;
}

a {
	color: #3a8bbb;
	text-decoration: none;
	font-weight: 600;
}

#root {
	padding: 25px;
}

#logo, #content {
	margin: 0 auto;
}

#logo {
	display: block;
	text-align: center;
	color: #fff;
	font-size: 30px;
	font-weight: 600;
	margin-bottom: 20px;
}

#content {
	background-color: #fff;
	border-radius: 5px;
	padding: 15px;
	width: 600px;
}

#team {
	border-top: 1px solid #ddd;
	margin-top: 25px;
	padding-top: 10px;
}

.qr {
	display: block;
	width: 324px;
	margin: 0 auto;
}    
</style>
</head>
<body>
<div id="root">
	<span id="logo">Dacorum Indian Society</span>
	<div id="content">
		<p>Hello {{ $name }},</p>

		
		                        @if($user->payment_type != 'PayPal')
                                <p>Thank you for submitting the Dacorum Indian Society Membership form. Your membership is only confirmed upon receipt of payment.</p>
                                <br>
                                <p>Please pay your membership fees using below bank account details and Membership ID:</p>
                                <br>
                                @else
                                <p>Thank you for making the payment and submitting the Dacorum Indian Society Membership Form. We have received your payment.</p>
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
                                <br>
                                @endif
                                <label class="label label--block">Unique Membership ID : {{ $user->member_id }}</label><br>
                                <br>
                                <label class="label label--block">Membership Expire On  : {{ date('d-m-Y', strtotime('+'.$user->membership_year.' years')) }}</label><br>
                                <br>
                                <p>Please download your membership QR code below and bring it with you to all Dacorum Indian Society events.</p>
                                <br><br>
                                <center>
                                <img src="{{ env('IMAGE_URL').'/uploads/qrcodes/'.$qrcode_name }}">
                                <div>
                                    <a class="btn btn--radius-2 btn--red" href="{{ env('IMAGE_URL').'uploads/qrcodes/'.$qrcode_name }}" download>Download QR</a>
                                </div>
                                </center>
                                <br>
		
		
        <p><b>Now you can manage your DIS Membership Online. Use below link and login details.</b><br><a href="http://members.dacorumindiansociety.co.uk/">members.dacorumindiansociety.co.uk</a></p>
        
        <p>Login ID :<b>{{ $login_id }}</b></p>
		<p>Password :<b>{{ $password }}</b></p>
		
		<p><b>NOTE :-</b> We suggest you change your password immediately once you have logged in</p>
		
	
		    
		<p id="team">For any queries please contact us on: dis.hemel@gmail.com<br><br>
                                regards,<br>
                                DIS Membership Secretary,<br>
                                This e-mail was sent from a contact form on DACORUM INDIAN SOCIETY (https://dacorumindiansociety.co.uk)</p>
	</div>
</div>
</body>