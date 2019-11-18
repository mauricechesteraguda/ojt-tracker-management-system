<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\User;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\UserCollection;

class UserController extends Controller
{

    public function index()
    {
        return new UserCollection(User::orderBy('name', 'ASC')->paginate(5));
    }

    public function show($id)
    {
        return new UserResource(User::findOrFail($id));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'role' => 'required|max:255',
        ]);
        $current_sr_code = $request->input('sr_code');
        $users = User::where('sr_code', '=', $current_sr_code)->first();
        if (!$users) {
            $user = User::create($request->all());
            $user->password = Hash::make($request['password']);
            $user->save();

            return (new UserResource($user))
                    ->response()
                    ->setStatusCode(201);
        }
        return response()->json([
            'message' => 'Username already exists!'
        ], 500);

        
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(null, 204);
        // return new UserCollection(User::all());
    }

        
    public function update(Request $request, $id)
        {
            $this->validate($request, [
                'name' => 'required|max:255',
            ]);
    
            $user = User::findOrFail($id);
            
            $user->name = request('name');
            $user->role = request('role');
            $user->email = request('email');
            if (request('password')) {
                $user->password = Hash::make(request('password'));
            }
            $user->save();
    
            return response()->json([
                'message' => 'User updated successfully!'
            ], 200);
        }

}
