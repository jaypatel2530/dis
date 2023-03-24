@extends('layouts.app')
@section('title','Dashboard')
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
        
        <div class="col-xl-4 offset-md-2 offset-sm-0 col-md-4 mb-4 mt-4">
          <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
              <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                  <div class="font-weight-bold text-primary text-uppercase mb-1">Membership</div>
                  <div class="h5 mb-0 font-weight-bold text-gray-800"><strong>
                      @if(Auth::user()->membership == 'Family')
                      Family
                      @else
                      Single
                      @endif
                      
                      ( {{ Auth::user()->membership_year }} @if(Auth::user()->membership_year == 1) Year @else Years @endif)
                  </strong>
                  </div>
                </div>
                <div class="col mr-2">
                  <div class="font-weight-bold text-primary text-uppercase mb-1">Expire On </div>
                  <div class="h5 mb-0 font-weight-bold text-gray-800"><strong>
                      {{ date('d-m-Y', strtotime(Auth::user()->membership_date.' +'.Auth::user()->membership_year.' years')) }}
                  </strong>
                  <?php
                    $date1=date_create(date('Y-m-d'));
                    
                    $exp_date = date('Y-m-d', strtotime(Auth::user()->membership_date.' +'.Auth::user()->membership_year.' years'));
                    $date2=date_create($exp_date);
                    $diff=date_diff($date1,$date2);
                    
                  ?>
                  
                  @if($diff->days <= 7)
                  <a style="white-space: nowrap;" title="Renewal" href="{{ route('get:member_renewal') }}" class="btn btn-primary btn-sm" >Renewal</a>
                  @elseif($diff->invert == 1)
                  <a style="white-space: nowrap;" title="Renewal" href="{{ route('get:member_renewal') }}" class="btn btn-primary btn-sm" >Renewal</a>
                  @else
                  <a style="white-space: nowrap;" title="Renewal" href="" class="btn btn-secondary btn-sm" user_id=""  disabled >Renewal</a>
                  @endif
                  </div>
                </div>
                
              </div>
              <br>
              
              <a href="{{ route('get:manage_member') }}" style-"bottom: 5%;position: absolute;">View Details</a>
              @if(Auth::user()->membership == 'Family')
              |
              <a href="{{ route('get:add_member') }}" style-"bottom: 5%;position: absolute;">Add Family Member</a>
              @endif
            </div>
          </div>
        </div>
        
        <div class="col-xl-4 col-md-4 mb-4 mt-4">
          <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
              <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                  <div class="font-weight-bold text-primary text-uppercase mb-1">Membership Id</div>
                  <div class="h5 mb-0 font-weight-bold text-gray-800"><strong >
                      {{ Auth::user()->member_id }}
                  </strong><input value="{{ Auth::user()->member_id }}" type="hidden" id="copymemberid"><a onclick="withJquery();"><i class="fa fa-clone" aria-hidden="true"></i></a>
                  </div>
                </div>
                
              </div>
            </div>
          </div>
        </div>
    </div>
    <div class="row bg-set">    
        
        <div class="col-xl-4 offset-md-2 offset-sm-0 col-lg-offset-1 col-md-4 mb-4 mt-4">
          <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
              <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <p class="font-weight-bold text-primary text-uppercase mb-1">Please download the QR code below and bring it with you to all Dacorum Indian Society events.</p>
                  <center>
                                <img class="img-thumbnail" src="{{ env('IMAGE_URL').'/uploads/qrcodes/'.Auth::user()->qr_img }}">
                                <div>
                                    <a class="btn btn--radius-2 btn--red" href="{{ env('IMAGE_URL').'uploads/qrcodes/'.Auth::user()->qr_img }}" download>Download QR</a>
                                </div>
                                </center>
                </div>
                
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-xl-4 col-md-4 mb-4 mt-4">
          <div class="card border-left-primary shadow h-100 py-2" >
            <div class="card-body">
                <div class="font-weight-bold text-primary text-uppercase mb-1">DIS Updates</div>
              <div class="row no-gutters align-items-center" style="overflow: hidden;">
                  
                <div class="col mr-2 marquee">
                    
                    @foreach($notify as $r)
                  <b>{{ $r->title }}</b>
                  <p>{{ $r->message }}</p>
                    @endforeach
                </div>
                
              </div>
            </div>
          </div>
        </div>
        
        
        
    </div>
</div>
@endsection
@section('customjs')
<script>
function withJquery(){
  console.time('time1');
  
	navigator.clipboard.writeText($('#copymemberid').val());
 
  document.execCommand("copy");
  
    console.timeEnd('time1');
}

function copyText() {
  // Get the text field
  var copyText = $('#copymemberid').val();
    alert(copyText);
  // Select the text field
  copyText.select();
  copyText.setSelectionRange(0, 99999); // For mobile devices

  // Copy the text inside the text field
  navigator.clipboard.writeText(copyText.value);
  
  // Alert the copied text
  alert("Copied the text: " + copyText.value);
}    
</script>
@endsection