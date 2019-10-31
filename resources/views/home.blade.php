@extends('layouts.app')

@section('content')

<div  class="container-fluid">

    <div class="row justify-content-center">
        <div>

            @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

        </div>
             
                        
        <div class="col-md-10 ">
          <div id="react" class="title m-b-md"></div>
          
          <script src="js/app.js"></script>
               
        </div>
    </div>
</div>

@endsection
