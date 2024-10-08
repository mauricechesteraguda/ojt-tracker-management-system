@extends('layouts.app')

@section('content')
<div class="container fade-in">
    <div class="row justify-content-center">
        <div class="col-md-8">
                <div id="myModal" class="modal fade" role="dialog">
                        <div class="modal-dialog modal-lg">
                      
                          <!-- Modal content-->
                          <div class="modal-content">
                            
                            <div class="modal-body">
            
            
                                <div class="card ">
                <div class="card-header">
                    <h4 class="modal-title ng-binding" id="myModalLabel">Batangas State University Login - OJT PORTAL</h4>    
                </div>

                <div class="card-body">

                        <div class="login-container animated fadeInDown" ng-init="login.init()">

                                <div class="widget">
                                    
                                    
                                     <center>
                                        <img style="width: 150px; height:150px;" src='img/brand/logo.png'>
                                    </center>
                                </div>
                            
                            <hr/>
                        
                            
                            
                        </div>



                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="sr_code" class="col-sm-4 col-form-label text-md-right">{{ __('SRCODE') }}</label>

                            <div class="col-md-6">
                                <input id="sr_code" type="text" class="form-control{{ $errors->has('sr_code') ? ' is-invalid' : '' }}" name="sr_code" value="{{ old('sr_code') }}" required autofocus>

                                @if ($errors->has('sr_code'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('sr_code') }}</strong>
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

                                <span>
                                        <a class="btn btn-link" href="{{ route('password.request') }}">
                                                {{ __('Forgot Your Password?') }}
                                            </a>  
                                </span>
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                                <div class="col-md-8 offset-md-4">
                                    <span>Don't have an account? <a href="/register">
                                        {{ __(' Register here!') }}
                                </a></span>
                               
                                        
                                    
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
