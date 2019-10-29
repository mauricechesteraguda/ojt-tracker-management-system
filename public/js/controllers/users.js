
app.controller('UserController', ['$scope', '$http', function ($scope, $http) {
    $scope.errors = [];
    $scope.users = []
    $scope.user = {
        name: '',
        email: '',
        role: '',
        password: ''
    };
    
    $('#edit_user').on('hidden.bs.modal', function () {
        $http.get(window.location.protocol + '//'+window.location.hostname+'/irms/api/users')
        .then(function success(e) {
            $scope.users = e.data;
        });
    }) 

    $http.get(window.location.protocol + '//'+window.location.hostname+'/irms/api/users')
            .then(function success(e) {
                $scope.users = e.data;
            });

    $scope.initUser = function () {
        $scope.resetForm();
        $('#add_new_user').modal({backdrop: 'static', keyboard: false})  
        $("#add_new_user").modal('show');
    };

    $scope.addUser = function () {
        $http.post(window.location.protocol + '//'+window.location.hostname+'/irms/public/api/users', {
            name: $scope.user.name,
            email: $scope.user.email,
            password: $scope.user.password,
            role: $scope.user.role
        }).then(function success(e) {
            $scope.resetForm();
            $http.get(window.location.protocol + '//'+window.location.hostname+'/irms/api/users')
            .then(function success(e) {
                $scope.users = e.data;
            });
            $("#add_new_user").modal('hide');

        }, function error(error) {
            $scope.recordErrors(error)
        });
    };

    $scope.recordErrors = function (error) {
        $scope.errors = [];
        if (error.statusText) {
            $scope.errors.push(error.statusText);
        }

        if (error.data.message) {
            $scope.errors.push(error.data.message);
        }
    };

    $scope.resetForm = function () {
        $scope.user.name = '';
        $scope.user.email = '';
        $scope.user.password = '';
        $scope.user.role = '';
        $scope.errors = [];
    };
	
    $scope.edit_user = {};
        // initialize update action
    $scope.initEdit = function (index) {
        $scope.errors = [];
        $scope.edit_user = $scope.users.data[index];
        $('#edit_user').modal({backdrop: 'static', keyboard: false})  
        $("#edit_user").modal('show');
    };

    // update the given user
    $scope.updateUser = function () {
        $http.post(window.location.protocol + '//'+window.location.hostname+'/irms/public/api/users/' + $scope.edit_user.id, {
            name: $scope.edit_user.name,
            password: $scope.edit_user.password,
            role: $scope.edit_user.role
        }).then(function success(e) {
            $scope.errors = [];
            $("#edit_user").modal('hide');
        }, function error(error) {
            $scope.recordErrors(error);
        });
    };

    $scope.deleteUser = function (index) {

        var conf = confirm("Do you really want to delete this user?");
    
        if (conf === true) {
            $http.delete(window.location.protocol + '//'+window.location.hostname+'/irms/public/api/users/' + $scope.users.data[index].id)
                .then(function success(e) {
                    $scope.users.data.splice(index, 1);
                });
        }
    };
}]);  