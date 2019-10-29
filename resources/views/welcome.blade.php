{{-- 
        <div class="flex-center position-ref full-height">
            @if (Route::has('login'))
                <div class="top-right links">
                    @auth
                        <a href="{{ url('/home') }}">Home</a>
                    @else
                        <a href="{{ route('login') }}">Login</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}">Register</a>
                        @endif
                    @endauth
                </div>
            @endif
        </div>
             --}}

             <!DOCTYPE html>
             <html lang="en" >
             <head>
               <!-- Theme Made By www.w3schools.com -->
               <title>OJT</title>
               <link rel="icon" href="{{ asset('img/favicon.ico') }}" type="image/ico">
               <meta charset="utf-8">
               <meta name="viewport" content="width=device-width, initial-scale=1">
               <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
               <link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet" type="text/css">
               <link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet" type="text/css">
               <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

               <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
               <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
               <style>
                       <link href="{{ asset('css/app.css') }}" rel="stylesheet">
  
               body {
                   font: 400 15px Lato, sans-serif;
                   line-height: 1.8;
                   color: #818181;
               }
               h2 {
                   font-size: 24px;
                   text-transform: uppercase;
                   color: #303030;
                   font-weight: 600;
                   margin-bottom: 30px;
               }
               h4 {
                   font-size: 19px;
                   line-height: 1.375em;
                   color: #303030;
                   font-weight: 400;
                   margin-bottom: 30px;
               }  
               .jumbotron {
                   background-color: #;
                   color: #0000CD;
                   padding: 100px 25px;
                   font-family: Montserrat, sans-serif;
               }
               .container-fluid {
                   padding: 60px 50px;
               }
               .bg-grey {
                   background-color: #f6f6f6;
               }
               .logo-small {
                   color: #f4511e;
                   font-size: 50px;
               }
               .logo {
                   color: #f4511e;
                   font-size: 200px;
               }
               .thumbnail {
                   padding: 0 0 15px 0;
                   border: none;
                   border-radius: 0;
               }
               .thumbnail img {
                   width: 100%;
                   height: 100%;
                   margin-bottom: 10px;
               }
               .carousel-control.right, .carousel-control.left {
                   background-image: none;
                   color: #f4511e;
               }
               .carousel-indicators li {
                   border-color: #f4511e;
               }
               .carousel-indicators li.active {
                   background-color: #f4511e;
               }
               .item h4 {
                   font-size: 19px;
                   line-height: 1.375em;
                   font-weight: 400;
                   font-style: italic;
                   margin: 70px 0;
               }
               .item span {
                   font-style: normal;
               }
               .panel {
                   border: 1px solid #1E90FF; 
                   border-radius:0 !important;
                   transition: box-shadow 0.5s;
               }
               .panel:hover {
                   box-shadow: 5px 0px 40px rgba(0,0,0, .2);
               }
               .panel-footer .btn:hover {
                   border: 1px solid #f4511e;
                   background-color: #fff !important;
                   color: #f4511e;
               }
               .panel-heading {
                   color: #fff !important;
                   background-color: #f4511e !important;
                   padding: 25px;
                   border-bottom: 1px solid transparent;
                   border-top-left-radius: 0px;
                   border-top-right-radius: 0px;
                   border-bottom-left-radius: 0px;
                   border-bottom-right-radius: 0px;
               }
               .panel-footer {
                   background-color: white !important;
               }
               .panel-footer h3 {
                   font-size: 32px;
               }
               .panel-footer h4 {
                   color: #aaa;
                   font-size: 14px;
               }
               .panel-footer .btn {
                   margin: 15px 0;
                   background-color: #f4511e;
                   color: #fff;
               }
               .navbar {
                   margin-bottom: 0;
                   background-color: #1E90FF;
                   z-index: 9999;
                   border: 0;
                   font-size: 12px !important;
                   line-height: 1.42857143 !important;
                   letter-spacing: 4px;
                   border-radius: 0;
                   font-family: Montserrat, sans-serif;
               }
               .navbar li a, .navbar .navbar-brand {
                   color: #fff !important;
               }
               .navbar-nav li a:hover, .navbar-nav li.active a {
                   color: #f4511e !important;
                   background-color: #fff !important;
               }
               .navbar-default .navbar-toggle {
                   border-color: transparent;
                   color: #fff !important;
               }
               footer .glyphicon {
                   font-size: 20px;
                   margin-bottom: 20px;
                   color: #f4511e;
               }
               .slideanim {visibility:hidden;}
               .slide {
                   animation-name: slide;
                   -webkit-animation-name: slide;
                   animation-duration: 1s;
                   -webkit-animation-duration: 1s;
                   visibility: visible;
               }
               @keyframes slide {
                 0% {
                   opacity: 0;
                   transform: translateY(70%);
                 } 
                 100% {
                   opacity: 1;
                   transform: translateY(0%);
                 }
               }
               @-webkit-keyframes slide {
                 0% {
                   opacity: 0;
                   -webkit-transform: translateY(70%);
                 } 
                 100% {
                   opacity: 1;
                   -webkit-transform: translateY(0%);
                 }
               }
               @media screen and (max-width: 768px) {
                 .col-sm-4 {
                   text-align: center;
                   margin: 25px 0;
                 }
                 .btn-lg {
                     width: 100%;
                     margin-bottom: 35px;
                 }
               }
               @media screen and (max-width: 480px) {
                 .logo {
                     font-size: 150px;
                 }
               }
               .bgimg {
                    background-image: url('img/municipal_mabini.jpg');
                    background-repeat: no-repeat;
                    background-size:  100vw 100vh;
                    
                }
                .bg-light-header {
                    background:#f6f6f6;
                    opacity: 0.7;
                }
               
               </style>
             </head>
             <body id="home" data-spy="scroll" data-target=".navbar" data-offset="60" >
             
             <nav class="navbar navbar-default navbar-fixed-top">
               <div class="container">
                 <div class="navbar-header">
                   <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
                     <span class="icon-bar"></span>
                     <span class="icon-bar"></span>
                     <span class="icon-bar"></span>                        
                   </button>
                   <a class="navbar-brand" href="{{ url('/') }}">
                    OJT
                </a>
                 </div>
                 <div class="collapse navbar-collapse" id="myNavbar">
                   <ul class="nav navbar-nav navbar-right">
                     <li><a href="#home">HOME</a></li>
                     <li><a href="#about">SEARCH</a></li>
                     <li><a href="#services">REQUIREMENTS</a></li>
                     <li><a href="#portfolio">APPLICATION STEPS</a></li>
                     <li><a href="#pricing">DOWNLOADABLE FORMS</a></li>
                     <li> @auth
                        <a href="{{ url('/home') }}">Home</a>
                    @else
                        <a href="{{ route('login') }}">LOGIN</a>

                        @if (Route::has('register'))
                            {{-- <a href="{{ route('register') }}">Register</a> --}}
                        @endif
                    @endauth</li>
                   </ul>
                 </div>
               </div>
             </nav>
             
             <div class="jumbotron text-center bgimg">
                <center>
                    <img style="width: 150px; height:150px;" src="img/MabiniLogo.png">
                </center>
                <div class="bg-light-header">
                  <h1 >OJT</h1> 
                  <h3 >BUSINESS PERMIT AND LICENSING SYSTEM</h3>
                  <h3 >Municipality of Mabini</h3> 
                </div>
               
               
               
             </div>
             
             <!-- Container (About Section) -->
             <div id="about" class="container-fluid" ng-app="irmsApp">
                    <div ng-view></div>
             </div>
             
             <!-- Container (Services Section) -->
             <div id="services" class="container-fluid text-center">
               <h2>PERMIT & LICENSING REQUIREMENTS</h2>
               <h4>-------------</h4>
               <br>
               <div class="row slideanim">
                 <div class="col-sm-4">
                   <span class="glyphicon glyphicon-folder-open logo-small"></span>
                   <h4> <b> SMALL BUSINESS </b></h4>
                   
                   <dl class="text-left">
                    <dt>FOR NEW BUSINESS</dt>
                    <dd>1. Barangay Clearance</dd>
                    <dd>2. DTI Certificate</dd>
                    <dd>3. Photocopy of Valid ID</dd>
                    <dd>4. Business location sketch map</dd>
                    <dt>FOR RENEWAL</dt>
                    <dd>1. Barangay Clearance</dd>
                    <dd>2. Previous year’s permit & Official Receipt (OR)</dd>
                    <dd>3. Sworn declaration of Gross receipts</dd>
                  </dl>
                 </div>
                 <div class="col-sm-4">
                   <span class="glyphicon glyphicon-cd logo-small"></span>
                   <h4><b>INDUSTRIAL & COMMERCIAL ESTABLISHMENTS</b></h4>
                   <dl class="text-left">
                    <dt>FOR NEW BUSINESS</dt>
                    <dd>1. DTI Certificate for Single Proprietorship</dd>
                    <dd>2. SEC Reg. for Corporate & Partnership</dd>
                    <dd>3. Barangay Clearance</dd>
                    <dd>4. Photocopy of two (2) Valid ID</dd>
                    <dd>5. Business location sketch map</dd>
                    <dt>FOR RENEWAL</dt>
                    <dd>1. Barangay Clearance</dd>
                    <dd>2. Previous year’s permit & Official Receipt (OR)</dd>
                    <dd>3. Sworn declaration of Gross receipts</dd>
                  </dl>
                 </div>
                 <div class="col-sm-4">
                   <span class="glyphicon glyphicon-compressed logo-small"></span>
                   <h4><b> TRICYCLE REGISTRATION</b></h4>
                   <dl class="text-left">
                     <dt>1. FOR NEW & RENEWAL</dt>
                     <dd>2. LTO OR/CR</dd>
                     <dd>3. Franchise</dd>
                     <dd>4. Residential Certificate (Cedula)</dd>
                     <dd>5. Certificate of membership from TODA</dd>
                     <dd>6. Transfer of ownership (if necessary)</dd>
                     <dd>7. Mayor’s permit of TODA (Photocopy)</dd>
                     <dd>8. Previous year’s permit & Official Receipt (OR) (FOR RENEWAL)</dd>
                   </dl>
                 </div>
               </div>
               <br><br>
               <div class="row slideanim">
                 
                 <div class="col-sm-4">
                   <span class="glyphicon glyphicon-tags logo-small"></span>
                   <h4><b> BOAT REGISTRATION</b></h4>
                   <dl class="text-left">
                     <dt>FOR NEW & RENEWAL</dt>
                     <dd>1. Certificate of registration from MARINA (if necessary)</dd>
                     <dd>2. Residential Certificate (Cedula)</dd>
                     <dd>3. Previous year’s permit & Official Receipt (OR) (FOR RENEWAL)</dd>
                    
                   </dl>
                 </div>
                 <div class="col-sm-4">
                  <span class="glyphicon glyphicon-certificate logo-small"></span>
                  <h4><b> RHU</b></h4>
                  <dl class="text-left">
                    <dt>SANITARY PERMIT AND HEALTH CERTIFICATE </dt>
                    <dd>1. Urinalysis Result</dd>
                    <dd>2. Fecalysis Result</dd>
                    <dd>3. X-ray Result</dd>
                    <dd>4. Drug Test Result</dd>
                    <dd>5. Hepatitis B Result</dd>
                    <dd>6. Medical Certificate</dd>
                    <dd>7. Food Handler</dd>
                    <dd>8. CTC (Cedula)</dd>
                   
                  </dl>
                </div>
                 <div class="col-sm-4">
                  <span class="glyphicon glyphicon-flash logo-small"></span>
                  <h4><b> BFP</b></h4>
                  <dl class="text-left">
                    <dt>FIRE SAFETY INSPECTION CERTIFICATE (FSIC)</dt>
                    <dd>1. Latest Official Receipt of purchased firefighting equipment</dd>
                    <dd>2. Latest FSCI (if any)</dd>
                    <dd>3. Latest Official Receipt issued by BFP (if any)</dd>
                   
                   
                  </dl>
               </div>
             </div>
             
             <!-- Container (Portfolio Section) -->
             <div id="portfolio" class="container-fluid text-center bg-grey">
               <h2>APPLICATION STEPS</h2><br>
               
               <div class="row text-center slideanim">
                 <div class="col-sm-4">
                   <div class="thumbnail">
                      <h4><b>STEPS FOR APPLICATION OF BUSINESS PERMIT</b></h4>
                      
                     <p>
                        
                      <dl class="text-left">
                        <dt>1. Business Permit and Licensing Office (BPLO)</dt>
                        <dd>Evaluation and Approval of Requirements</dd>
                        <dd>Filling of Business Application Form Data Encoding</dd>
                        <dd>Issuance of Statement of Account (SOA)</dd>
                        <dt>2. Office of the Municipal Engineer (OME)</dt>
                        <dd>Computation of Annual Inspection Fee</dd>
                        <dt>3. Municipal Treasurer Office (MTO)</dt>
                        <dd>Payment/ Issuance of CTC (Cedula)</dd>
                        <dd>Payment of Business Tax Fee & Annual</dd>
                        <dd>Inspection Fee</dd>
                        <dt>4. Rural Health Unit</dt>
                        <dd>Issuance of Sanitary Permit/Health Certificate</dd>
                        <dt>5. Bureau of Fire Protection (BFP)</dt>
                        <dd>Payment of Fire Inspection Fee</dd>
                        <dd>Issuance of Fire Safety Inspection Certificate</dd>
                        <dt>6. Business Permit and Licensing Office (BPLO)</dt>
                        <dd>Preparation/Approval and Releasing of Business Permit</dd>

                      </dl>
                     </p>
                   </div>
                 </div>
                 <div class="col-sm-4">
                   <div class="thumbnail">
                      <h4><b>STEPS FOR APPLICATION OF TRICYCLE REGISTRATION, RENEWAL & ISSUANCE OF PERMIT TO OPERATE</b></h4>
                      
                     <p>
                        
                      <dl class="text-left">
                        <dt>1. Business Permit and Licensing Office (BPLO)</dt>
                        <dd>Evaluation and Approval of Requirements</dd>
                        <dd>Filling of Tricycle Application Form Data Encoding</dd>
                        <dd>Inspection</dd>
                        <dt>2. Municipal Treasurer Office (MTO)</dt>
                        <dd>Payment</dd>
                        <dt>3. Business Permit and Licensing Office (BPLO)</dt>
                        <dd>Preparation/Approval and Releasing of Mayor’s Permit</dd>

                       
                      </dl>
                     </p>
                   </div>
                 </div>
                 <div class="col-sm-4">
                   <div class="thumbnail">
                      <h4><b>STEPS FOR APPLICATION OF BOAT REGISTRATION, RENEWAL & ISSUANCE OF PERMIT TO OPERATE</b></h4>
                      
                     <p>
                        
                      <dl class="text-left">
                        <dt>1. Business Permit and Licensing Office (BPLO)</dt>
                        <dd>Evaluation and Approval of Requirements</dd>
                        <dd>Filling of Boat Application Form Data Encoding</dd>
                        <dd>Issuance of Statement of Account (SOA)</dd>
                        <dt>2. Municipal Treasurer Office (MTO)</dt>
                        <dd>Payment</dd>
                        <dt>3. Business Permit and Licensing Office (BPLO)</dt>
                        <dd>Preparation/Approval and Releasing of Mayor’s Permit</dd>
                       
                      </dl>
                     </p>
                   </div>
                 </div>
               </div>
               <br> 
               {{-- <h2>What our customers say</h2>
               <div id="myCarousel" class="carousel slide text-center" data-ride="carousel">
                 <!-- Indicators -->
                 <ol class="carousel-indicators">
                   <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
                   <li data-target="#myCarousel" data-slide-to="1"></li>
                   <li data-target="#myCarousel" data-slide-to="2"></li>
                 </ol>
             
                 <!-- Wrapper for slides -->
                 <div class="carousel-inner" role="listbox">
                   <div class="item active">
                     <h4>"This company is the best. I am so happy with the result!"<br><span>Michael Roe, Vice President, Comment Box</span></h4>
                   </div>
                   <div class="item">
                     <h4>"One word... WOW!!"<br><span>John Doe, Salesman, Rep Inc</span></h4>
                   </div>
                   <div class="item">
                     <h4>"Could I... BE any more happy with this company?"<br><span>Chandler Bing, Actor, FriendsAlot</span></h4>
                   </div>
                 </div>
             
                 <!-- Left and right controls -->
                 <a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">
                   <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
                   <span class="sr-only">Previous</span>
                 </a>
                 <a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">
                   <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                   <span class="sr-only">Next</span>
                 </a> --}}
               </div>
             </div>
             
             <!-- Container (Pricing Section) -->
             <div id="pricing" class="container-fluid">
               <div class="text-center">
                 <h2>FORMS</h2>
                 {{-- <h4>Choose a payment plan that works for you</h4> --}}
               </div>
               <div class="row slideanim">
                 <div class="col-sm-4 col-xs-12">
                      
                 </div>    
               </div>
             </div>
             
             <!-- Container (Contact Section) -->
             <div id="contact" class="container-fluid bg-grey">
               <h2 class="text-center">CONTACT</h2>
               <div class="row">
                 <div class="col-sm-5">
                   <p>Contact us and we'll get back to you within 24 hours.</p>
                   <p><span class="glyphicon glyphicon-map-marker"></span> Municipality of Mabini, Batangas City, Batangas Philippines</p>
                   <p><span class="glyphicon glyphicon-phone"></span> (043) 487 0873</p>
                   <p><span class="glyphicon glyphicon-envelope"></span> support_municipality_of_mabini@gmail.com</p>
                 </div>
                 <div class="col-sm-7 slideanim">
                   <div class="row">
                     <div class="col-sm-6 form-group">
                       <input class="form-control" id="name" name="name" placeholder="Name" type="text" required>
                     </div>
                     <div class="col-sm-6 form-group">
                       <input class="form-control" id="email" name="email" placeholder="Email" type="email" required>
                     </div>
                   </div>
                   <textarea class="form-control" id="comments" name="comments" placeholder="Comment" rows="5"></textarea><br>
                   <div class="row">
                     <div class="col-sm-12 form-group">
                       <button class="btn btn-default pull-right" >Send</button>
                     </div>
                   </div>
                 </div>
               </div>
             </div>
             
             
             <footer class="container-fluid text-center">
               <a href="#home" title="To Top">
                 <span class="glyphicon glyphicon-chevron-up"></span>
               </a>
              
             </footer>
             
             <script>
             $(document).ready(function(){
               // Add smooth scrolling to all links in navbar + footer link
               $(".navbar a, footer a[href='#myPage']").on('click', function(event) {
                 // Make sure this.hash has a value before overriding default behavior
                 if (this.hash !== "") {
                   // Prevent default anchor click behavior
                   event.preventDefault();
             
                   // Store hash
                   var hash = this.hash;
             
                   // Using jQuery's animate() method to add smooth page scroll
                   // The optional number (900) specifies the number of milliseconds it takes to scroll to the specified area
                   $('html, body').animate({
                     scrollTop: $(hash).offset().top
                   }, 900, function(){
                
                     // Add hash (#) to URL when done scrolling (default click behavior)
                     window.location.hash = hash;
                   });
                 } // End if
               });
               
               $(window).scroll(function() {
                 $(".slideanim").each(function(){
                   var pos = $(this).offset().top;
             
                   var winTop = $(window).scrollTop();
                     if (pos < winTop + 600) {
                       $(this).addClass("slide");
                     }
                 });
               });
             })
             </script>
             <script src="js/angular.js"></script>
             <script src="js/angular-route.js"></script>
             <script src="js/main.router.js"></script>
             <link href="//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css" rel="stylesheet">
              <script src="//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>

              <link href="{{ asset('css/angular-datatables.min.css') }}" rel="stylesheet">
             <script src="{{ asset('js/angular-datatables.min.js') }}"></script>
              

             <script src="js/controllers/dashboard.js"></script>
             </body>
             </html>
             