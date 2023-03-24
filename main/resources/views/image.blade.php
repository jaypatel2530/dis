<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title></title>
    
</head>
<body>
    <div class="container mt-4" style="max-width: 600px">
        <!--<h2 class="mb-5">Laravel Image Text Watermarking Example</h2>-->
        <form action="{{ route('post:image') }}" enctype="multipart/form-data" method="post">
            @csrf
            @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <strong>{{ $message }}</strong>
            </div>

            <div class="col-md-12 mb-3 text-center">
                <strong>Final image:</strong><a href="{{ Session::get('fileName') }}" download>Download</a><br />
                <img src="{{ Session::get('fileName') }}" width="600px"/>
            </div>
            @endif
            @if (count($errors) > 0)
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <div class="mb-3">
                <input type="file" name="file" class="form-control"  id="formFile" required>
                <input type="text" placeholder="Name" name="name" class="form-control" required>
            </div>
            <div class="d-grid mt-4">
                <button type="submit" name="submit" class="btn btn-primary">
                    Upload File
                </button>
            </div>
        </form>
    </div>
</body>
</html>