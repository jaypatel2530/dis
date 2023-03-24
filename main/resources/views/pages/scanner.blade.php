@extends('layouts.app')
@section('title','Profile')
@section('customcss')
<style>
    .html5-qrcode-element{background: #355bcc;
    border: #171f36;
    color: #fff;
    padding: 3%;
    margin: 2%;
    }
</style>
@endsection
@section('content')
<div class="container-fluid emp-profile">
    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="dropdown-test" class="control-label" >Event</label>
            <select class="form-control" name="event" placeholder="event" id="event" required>
                @foreach($events as $r)
                <option value="{{ $r->id }}">{{ $r->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            
            <div id="qr-reader"></div>
            <!--<div id="qr-reader-results"></div>-->
            <div class="col-xl-4 col-md-6 mb-4 mt-4">
              <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2" id="qr-reader-results">
                      
                    </div>
                  </div>
                </div>
              </div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('customjs')

<script src="{{ asset('qr/minified/html5-qrcode.min.js') }}"></script>
<script>
    function docReady(fn) {
        // see if DOM is already available
        if (document.readyState === "complete"
            || document.readyState === "interactive") {
            // call on next available tick
            setTimeout(fn, 1);
        } else {
            document.addEventListener("DOMContentLoaded", fn);
        }
    }

    docReady(function () {
        var resultContainer = document.getElementById('qr-reader-results');
        var lastResult, countResults = 0;
        function onScanSuccess(decodedText, decodedResult) {
            if (decodedText !== lastResult) {
                ++countResults;
                lastResult = decodedText;
                // Handle on success condition with the decoded message.
                // console.log('Scan result ${decodedText}', decodedResult);
                // alert(decodedText);
                // $('#qr-reader-results').html(decodedText);
                var event_id = $('#event').val();
                    $.ajax({
                        type: 'post',
                        dataType: "json",
                        url: '{{ route("post:scanner_data") }}',
                        data: {"user_number" : decodedText,"event_id":event_id,"_token":"{{ csrf_token() }}"},
                        success: function (result) {
                            // alert(decodedText);
                            if(result.success) {
                                
                                    
                                $('#qr-reader-results').html('<div class="font-weight-bold text-primary text-uppercase mb-1">Name : <b class="text-success">'+result.name+'</b> <br> Mobile : <b class="text-success">'+result.mobile+'</div>');
                                $('#qr-reader').css('display','none');
                               
                                showMyToast('success', result.message);
                            }
                            else {
                                
                                showMyToast('error', result.message);
                                location.reload();
                            }
                        }
                    })
            }
        }

        var html5QrcodeScanner = new Html5QrcodeScanner(
            "qr-reader", { fps: 10, qrbox: 150 });
        html5QrcodeScanner.render(onScanSuccess);
    });
</script>
@endsection