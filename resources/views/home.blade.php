@extends('layouts.app')

@section('content')
<div  class="container-fluid" ng-app="irmsApp">

    <div class="row justify-content-center">
        <div>

            @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

        </div>
             <div class="just-padding container-fluid col-md-2">

                    <div class="list-group list-group-root well">
                      {{-- @if($user->role =='Administrator')          --}}
                      <a href="#!dashboard" class="list-group-item active dashboard text-white bg-danger">
                        <i class="fa fa-home "></i></i> Dashboard
                      </a>
                      {{-- @else
                      <a href="#!dashboard" class="list-group-item active dashboard text-white bg-danger">
                        <i class="fa fa-home "></i></i> No
                      </a>       
                      @endif --}}
                      
                    @if($user->role =='Administrator' || $user->role =='bploencoder' || $user->role =='bploadminaide3' || $user->role =='bploadminjo' || $user->role =='municipalmayor')
                      <a href="#item-2" class="list-group-item prevent text-white bg-danger" data-toggle="collapse">
                        <i class="fa fa-id-card"></i> BPLO              <i class="fa fa-caret-down "></i>
                      </a>
                          <div class="list-group collapse" id="item-2">
                            @if($user->role =='Administrator' || $user->role =='bploencoder' || $user->role =='bploadminaide3' || $user->role =='bploadminjo')
                            <a href="#!/businesspermits" class="list-group-item" data-toggle="collapse">
                              <i class="glyphicon glyphicon-chevron-right"></i>Business Permit
                            </a>
                            @endif
                            @if($user->role =='Administrator' || $user->role =='bploencoder' || $user->role =='bploadminaide3' || $user->role =='bploadminjo')
                            <a href="#!/tricyclepermits" class="list-group-item" data-toggle="collapse">
                              <i class="glyphicon glyphicon-chevron-right"></i>Tricycle Permit
                            </a>
                            @endif
                            @if($user->role =='Administrator' || $user->role =='bploencoder' || $user->role =='bploadminaide3' || $user->role =='bploadminjo')
                            <a href="#!/boatpermits" class="list-group-item" data-toggle="collapse">
                              <i class="glyphicon glyphicon-chevron-right"></i>Boat Permit
                            </a>
                            @endif


                            @if($user->role =='Administrator' || $user->role =='bploadminaide3' || $user->role =='bploadminjo')
                            <a href="#!/businesstransactions" class="list-group-item" data-toggle="collapse">
                              <i class="glyphicon glyphicon-chevron-right"></i>Business Transaction
                            </a>
                            @endif
                            
                            @if($user->role =='Administrator' || $user->role =='bploadminaide3' || $user->role =='bploadminjo')
                            <a href="#!/tricycletransactions" class="list-group-item" data-toggle="collapse">
                              <i class="glyphicon glyphicon-chevron-right"></i>Tricycle Transaction
                            </a>
                            @endif
                            
                            @if($user->role =='Administrator' || $user->role =='bploadminaide3' || $user->role =='bploadminjo')
                            <a href="#!/boattransactions" class="list-group-item" data-toggle="collapse">
                              <i class="glyphicon glyphicon-chevron-right"></i>Boat Transaction
                            </a>
                            @endif
                                <a href="#item-8" class="list-group-item prevent text-white bg-dark" data-toggle="collapse">
                                  <i class="fa fa-id-card"></i> BPLO Reports              <i class="fa fa-caret-down "></i>
                                </a>
                                <div class="list-group collapse" id="item-8">
                                  
                                      <a href="#!/bploreports/active" class="list-group-item" data-toggle="collapse">
                                        <i class="glyphicon glyphicon-chevron-right"></i>Active Businesses
                                      </a>
                                      <a href="#!/bploreports/new" class="list-group-item" data-toggle="collapse">
                                        <i class="glyphicon glyphicon-chevron-right"></i>New Businesses
                                      </a>
                                      <a href="#!/bploreports/renewal" class="list-group-item" data-toggle="collapse">
                                        <i class="glyphicon glyphicon-chevron-right"></i>Renewal Businesses
                                      </a>
                                      <a href="#!/bploreports/retire" class="list-group-item" data-toggle="collapse">
                                        <i class="glyphicon glyphicon-chevron-right"></i>Retired Businesses
                                      </a>
                                      {{-- <a href="#!/businesspermits" class="list-group-item" data-toggle="collapse">
                                        <i class="glyphicon glyphicon-chevron-right"></i>Unpaid Businesses
                                      </a>
                                      <a href="#!/businesspermits" class="list-group-item" data-toggle="collapse">
                                        <i class="glyphicon glyphicon-chevron-right"></i>Business Transaction Details
                                      </a>
                                      <a href="#!/businesspermits" class="list-group-item" data-toggle="collapse">
                                        <i class="glyphicon glyphicon-chevron-right"></i>Quarterly Payment Businesses
                                      </a> --}}

                                </div>
                            
                          </div>
                      @endif
                      
                      @if($user->role =='Administrator' || $user->role =='rhuadminaide' || $user->role =='rhuencoder'|| $user->role =='municipalmayor')

                      <a href="#item-3" class="list-group-item prevent text-white bg-danger" data-toggle="collapse">
                        <i class="fa fa-medkit "></i> RHU             <i class="fa fa-caret-down "></i>
                      </a>
                      
                        <div class="list-group collapse" id="item-3">

                            @if($user->role =='Administrator' || $user->role =='rhuadminaide' || $user->role =='rhuencoder')
                              <a href="#!/medicals" class="list-group-item" data-toggle="collapse">
                                  <i class="glyphicon glyphicon-chevron-right"></i>Medical
                                </a>

                              
                                @endif
                                
                              <a href="#item-9" class="list-group-item prevent text-white bg-dark" data-toggle="collapse">
                                <i class="fa fa-id-card"></i> Business              <i class="fa fa-caret-down "></i>
                              </a>
                              <div class="list-group collapse" id="item-9">
                                
                                    
                                    <a href="#!/foods/report" class="list-group-item" data-toggle="collapse">
                                      <i class="glyphicon glyphicon-chevron-right"></i>Food
                                    </a>

                                    <a href="#!/wets/report" class="list-group-item" data-toggle="collapse">
                                      <i class="glyphicon glyphicon-chevron-right"></i>Dry
                                    </a>
                                    
                                    
                              </div>
                        
                      </div>
                      @endif
                      
                      
                      @if($user->role =='Administrator' || $user->role =='bfpcro' || $user->role =='bfpfcaa' || $user->role =='bfpcfseu' || $user->role =='bfpcmfm' || $user->role =='bfpfca' || $user->role =='municipalmayor')
                      <a href="#item-4" class="list-group-item prevent text-white bg-danger" data-toggle="collapse">
                        <i class="fa fa-fire "></i> BFP             <i class="fa fa-caret-down "></i>
                          </a>

                          <div class="list-group collapse" id="item-4">
                          @if($user->role =='Administrator' || $user->role =='bfpcro' || $user->role =='bfpfcaa' || $user->role =='bfpcfseu' || $user->role =='bfpcmfm' || $user->role =='bfpfca' )
                          
                            
                          <a href="#!/firetransactions" class="list-group-item" data-toggle="collapse">
                          <i class="glyphicon glyphicon-chevron-right"></i>Fire Transactions
                        </a>

                        <a href="#!/firepayments" class="list-group-item" data-toggle="collapse">
                          <i class="glyphicon glyphicon-chevron-right"></i>Payment Details
                        </a> 
                        
                          <a href="#!/fireinspections" class="list-group-item" data-toggle="collapse">
                          <i class="glyphicon glyphicon-chevron-right"></i>FSIC
                        </a> 
                        
    
                        
                        
                        
                        @endif
                            
                            
                          </div>
                        @endif 

                        @if($user->role =='Administrator' || $user->role =='engineering')
                        <a href="#item-10" class="list-group-item prevent text-white bg-danger" data-toggle="collapse">
                        <i class="fa fa-money "></i> ENGINEERING             <i class="fa fa-caret-down "></i>
                          </a>
                          <div class="list-group collapse" id="item-10">
                            
    
                        <a href="#!/engineertransactions" class="list-group-item" data-toggle="collapse">
                          <i class="glyphicon glyphicon-chevron-right"></i>Transactions
                        </a> 
                           
                          </div>
                          @endif

                        @if($user->role =='Administrator' || $user->role =='treasureradminaide' || $user->role =='treasurerencoder' || $user->role =='municipalmayor')
                        <a href="#item-7" class="list-group-item prevent text-white bg-danger" data-toggle="collapse">
                        <i class="fa fa-money "></i> TREASURER             <i class="fa fa-caret-down "></i>
                          </a>
                          <div class="list-group collapse" id="item-7">
                            
    
                        <a href="#!/treasurerpayments" class="list-group-item" data-toggle="collapse">
                          <i class="glyphicon glyphicon-chevron-right"></i>Payment Report
                        </a> 
                           
                          </div>
                          @endif

                          @if($user->role =='Administrator' || $user->role =='admin')
                          <a href="#item-5" class="list-group-item prevent text-white bg-danger" data-toggle="collapse">
                            <i class="fa fa-cogs "></i> </i>Settings              <i class="fa fa-caret-down "></i>
                              </a>
                              <div class="list-group collapse" id="item-5">
                                
                                <a href="#!/users" class="list-group-item">
                                    <i class="fa fa-users "></i> Manage Users
                                </a>
        
                                <a href="#!/businesstaxes" class="list-group-item">
                                  <i class="fa fa-users "></i> Business Tax
                              </a>
                                <!-- <a href="#item-5-2" class="list-group-item" data-toggle="collapse">
                                  <i class="glyphicon glyphicon-chevron-right"></i>Item 5.2
                                </a>
        
                                
                                <a href="#item-5-3" class="list-group-item" data-toggle="collapse">
                                  <i class="glyphicon glyphicon-chevron-right"></i>Item 5.3
                                </a>
         -->
                                
                              </div>
                              @endif
                              {{-- <a href="#item-6" class="list-group-item text-white bg-danger" data-toggle="collapse">
                                <i class="fa fa-phone-square "></i> Contact Us
                                  </a> --}}
                      
                    </div>
    
                </div>
                        
        <div class="col-md-10 ">
            <div ng-view></div>
                    
              <script>
                $(document).ready(function() {
                    $('.prevent ').click(function(e) {
                        e.preventDefault();
                    });
                    
                      window.location.replace(window.location.href + '#!dashboard');   
                      dash_a_tag = document.querySelector('.dashboard');
                      dash_a_tag.click();
                    
                    
                    
                });
                var user_role = '{{ $user->role }}';
              </script>
  
                
                    <script src="js/main.router.js"></script>

                    <script src="js/controllers/users.js"></script>
                    <script src="js/controllers/dashboard.js"></script>
                    <script src="js/controllers/dashboard2.js"></script>
                    <script src="js/controllers/businesspermits.js"></script>

                    <script src="js/controllers/bploactive.js"></script>
                    <script src="js/controllers/bplonew.js"></script>
                    <script src="js/controllers/bplorenewal.js"></script>
                    <script src="js/controllers/bploretire.js"></script>
                    
                    <script src="js/controllers/tricyclepermits.js"></script>
                    <script src="js/controllers/boatpermits.js"></script>
                    <script src="js/controllers/medicals.js"></script>
                    <script src="js/controllers/medicalsreport.js"></script>
                    <script src="js/controllers/foods.js"></script>
                    <script src="js/controllers/foodsreport.js"></script>
                    <script src="js/controllers/wets.js"></script>
                    <script src="js/controllers/wetsreport.js"></script>

                    <script src="js/controllers/fireinspections.js"></script>
                    <script src="js/controllers/firereport.js"></script>
                    <script src="js/controllers/ctcreports.js"></script>
                    <script src="js/controllers/businesstaxes.js"></script>
                    <script src="js/controllers/businesstransactions.js"></script>
                    <script src="js/controllers/tricycletransactions.js"></script>
                    <script src="js/controllers/boattransactions.js"></script>
                    <script src="js/controllers/firetransactions.js"></script>
                    <script src="js/controllers/firepayments.js"></script>
                    <script src="js/controllers/treasurerpayments.js"></script>
                    <script src="js/controllers/engineertransactions.js"></script>
                    
               
        </div>
    </div>
</div>
@endsection
