

var app = angular.module('irmsApp', ["ngRoute","datatables"]);
app.config(['$interpolateProvider',function($interpolateProvider) {
    $interpolateProvider.startSymbol('<%');
    $interpolateProvider.endSymbol('%>');
}]);

app.config(['$httpProvider', function ($httpProvider) {
    $httpProvider.defaults.headers.post['X-CSRF-TOKEN'] = $('meta[name=csrf-token]').attr('content');
        }]);
    app.config(['$interpolateProvider',function($interpolateProvider) {
        $interpolateProvider.startSymbol('<%');
        $interpolateProvider.endSymbol('%>');
    }]);


    // Add SPA Pages here
    // Then create controllers in js/controllers folder
    // Then create template page in templates/ folder
app.config(function($routeProvider) {
    $routeProvider
        .when("/", {
            templateUrl : "templates/dashboard.htm",
            controller: 'DashboardController',
        })
        .when("/users", {
            templateUrl : "templates/users.htm",
            controller: 'UserController',
        })
        .when("/dashboard", {
            templateUrl : "templates/dashboard2.htm",
            controller: 'Dashboard2Controller',
        })
        .when("/businesspermits", {
            templateUrl : "templates/businesspermits.htm",
            controller: 'BusinessPermitController',
        })
        .when("/businesspermits/:app_id", {
            templateUrl : "templates/businesspermits.htm",
            controller: 'BusinessPermitController',
        })
        
        .when("/bploreports/active", {
            templateUrl : "templates/bploactive.htm",
            controller: 'BPLOActiveController',
        })
        .when("/bploreports/new", {
            templateUrl : "templates/bplonew.htm",
            controller: 'BPLONewController',
        })
        .when("/bploreports/renewal", {
            templateUrl : "templates/bplorenewal.htm",
            controller: 'BPLORenewalController',
        })
        .when("/bploreports/retire", {
            templateUrl : "templates/bploretire.htm",
            controller: 'BPLORetireController',
        })


        .when("/tricyclepermits", {
            templateUrl : "templates/tricyclepermits.htm",
            controller: 'TricyclePermitController',
        })
        .when("/tricyclepermits/:app_id", {
            templateUrl : "templates/tricyclepermits.htm",
            controller: 'TricyclePermitController',
        })

        .when("/boatpermits", {
            templateUrl : "templates/boatpermits.htm",
            controller: 'BoatPermitController',
        })
        .when("/boatpermits/:app_id", {
            templateUrl : "templates/boatpermits.htm",
            controller: 'BoatPermitController',
        })

        .when("/medicals", {
            templateUrl : "templates/medicals.htm",
            controller: 'MedicalController',
        })
        .when("/medicals/report", {
            templateUrl : "templates/medicalsreport.htm",
            controller: 'MedicalReportController',
        })
        .when("/foods", {
            templateUrl : "templates/foods.htm",
            controller: 'FoodController',
        })
        .when("/foods/report", {
            templateUrl : "templates/foodsreport.htm",
            controller: 'FoodReportController',
        })
        .when("/wets", {
            templateUrl : "templates/wets.htm",
            controller: 'WetController',
        })
        .when("/wets/report", {
            templateUrl : "templates/wetsreport.htm",
            controller: 'WetReportController',
        })
        .when("/fireinspections", {
            templateUrl : "templates/fireinspections.htm",
            controller: 'FireInspectionController',
        })
        .when("/fireinspections/report", {
            templateUrl : "templates/firereport.htm",
            controller: 'FireReportController',
        })
        .when("/ctcreports", {
            templateUrl : "templates/ctcreports.htm",
            controller: 'CtcReportController',
        })
        .when("/businesstaxes", {
            templateUrl : "templates/businesstaxes.htm",
            controller: 'BusinessTaxController',
        })
        .when("/businesstransactions", {
            templateUrl : "templates/businesstransactions.htm",
            controller: 'BusinessTransactionController',
        })
        .when("/tricycletransactions", {
            templateUrl : "templates/tricycletransactions.htm",
            controller: 'TricycleTransactionController',
        })
        .when("/boattransactions", {
            templateUrl : "templates/boattransactions.htm",
            controller: 'BoatTransactionController',
        })
        .when("/firetransactions", {
            templateUrl : "templates/firetransactions.htm",
            controller: 'FireTransactionController',
        })
        .when("/firepayments", {
            templateUrl : "templates/firepayments.htm",
            controller: 'FirePaymentController',
        })
        .when("/treasurerpayments", {
            templateUrl : "templates/treasurerpayments.htm",
            controller: 'TreasurerPaymentController',
        })
        .when("/engineertransactions", {
            templateUrl : "templates/engineertransactions.htm",
            controller: 'EngineerTransactionController',
        })

    }
    );


