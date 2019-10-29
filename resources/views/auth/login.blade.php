@extends('layouts.app')

@section('content')
<div class="container fade-in">
    <div class="row justify-content-center">
        <div class="col-md-8">
                <div id="myModal" class="modal fade" role="dialog">
                        <div class="modal-dialog">
                      
                          <!-- Modal content-->
                          <div class="modal-content">
                            
                            <div class="modal-body">
            
            
                                <div class="card ">
                <div class="card-header text-white bg-primary mb-3">{{ __('Login') }}</div>

                <div class="card-body">

                        <div class="login-container animated fadeInDown" ng-init="login.init()">

                                <div class="widget">
                                    <center>
                                        <span class="widget-caption themeprimary"><b>Republic of the Philippines</b></span><br>
                                        <span class="widget-caption themeprimary"><b>Province of Batangas</b></span><br>
                                        <span class="widget-caption themeprimary" style="font-size: 30px"><b>Municipality of Mabini</b></span><br>
                                    </center>
                                     <center>
                                        <img style="width: 150px; height:150px;" src="img/MabiniLogo.png">
                                    </center>
                                </div>
                            
                            <hr/>
                        
                            
                            
                        </div>



                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="email" class="col-sm-4 col-form-label text-md-right">{{ __('Username') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus>

                                @if ($errors->has('email'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>

                                @if ($errors->has('password'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                    <label class="form-check-label" for="remember">
                                        {{ __('Remember Me') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Login') }}
                                </button>

                                <!-- <a class="btn btn-link" href="{{ route('password.request') }}">
                                    {{ __('Forgot Your Password?') }}
                                </a> -->
                            </div>
                        </div>
                    </form>

                </div>
                
              </div>
          
            </div>
          </div>
                </div>
            </div>
        </div>
    </div>
</div>
    <script>
        $(document).ready(function() {
            $('#myModal').modal({
                backdrop: 'static',
                keyboard: false
            });
            $('#myModal').modal('show');
        });

    </script>
@endsection
