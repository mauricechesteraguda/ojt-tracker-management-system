<?php

namespace App\Http\Controllers;

use App\Classes\batsu_api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\User;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\UserCollection;
use App\Support\SessionTracer;

class UserController extends Controller
{
    public function current(Request $request)
    {
        $started = microtime(true); $correlationId = SessionTracer::id(request()->header('X-Correlation-ID')); SessionTracer::enter('user.current', $correlationId, array('resource_type'=>'user'));
        try {
        return response()->json($request->user(), 200);

        } catch (\Throwable $exception) { SessionTracer::exception('user.current', $correlationId, $started, 'user_unexpected', $exception); throw $exception;
        } finally { SessionTracer::leave('user.current', $correlationId, $started, 'completed', array('resource_type'=>'user'));
        }
    }

    public function internship_requirement()
    {
        $started = microtime(true); $correlationId = SessionTracer::id(request()->header('X-Correlation-ID')); SessionTracer::enter('user.internship_requirement', $correlationId, array('resource_type'=>'user'));
        try {
        abort_unless(\Auth::user()->role === 'superuser', 403);
        return \App\Support\PaginatedJson::response(User::orderBy('name', 'ASC')->paginate(5), UserResource::class, request(), 'user.pagination.internship_requirement');

        } catch (\Throwable $exception) { SessionTracer::exception('user.internship_requirement', $correlationId, $started, 'user_unexpected', $exception); throw $exception;
        } finally { SessionTracer::leave('user.internship_requirement', $correlationId, $started, 'completed', array('resource_type'=>'user'));
        }
    }
    public function index()
    {
        $started = microtime(true); $correlationId = SessionTracer::id(request()->header('X-Correlation-ID')); SessionTracer::enter('user.index', $correlationId, array('resource_type'=>'user'));
        try {
        $user = \Auth::user();
        abort_unless($user->role === 'superuser', 403);
        return \App\Support\PaginatedJson::response(User::orderBy('name', 'ASC')->paginate(5), UserResource::class, request(), 'user.pagination.index');

        } catch (\Throwable $exception) { SessionTracer::exception('user.index', $correlationId, $started, 'user_unexpected', $exception); throw $exception;
        } finally { SessionTracer::leave('user.index', $correlationId, $started, 'completed', array('resource_type'=>'user'));
        }
    }
    public function search($value)
    {
        $started = microtime(true); $correlationId = SessionTracer::id(request()->header('X-Correlation-ID')); SessionTracer::enter('user.search', $correlationId, array('resource_type'=>'user'));
        try {
        abort_unless(\Auth::user()->role === 'superuser', 403);
        return \App\Support\PaginatedJson::response(User::where('name', 'LIKE', '%'.$value.'%')->orWhere('first_name', 'LIKE', '%'.$value.'%')->orWhere('last_name', 'LIKE', '%'.$value.'%')->orWhere('sr_code', 'LIKE', '%'.$value.'%')->orderBy('name', 'ASC')->paginate(20), UserResource::class, request(), 'user.pagination.search');

        } catch (\Throwable $exception) { SessionTracer::exception('user.search', $correlationId, $started, 'user_unexpected', $exception); throw $exception;
        } finally { SessionTracer::leave('user.search', $correlationId, $started, 'completed', array('resource_type'=>'user'));
        }
    }

    public function show($id)
    {
        $started = microtime(true); $correlationId = SessionTracer::id(request()->header('X-Correlation-ID')); SessionTracer::enter('user.show', $correlationId, array('resource_type'=>'user'));
        try {
        $actor = \Auth::user();
        abort_unless($actor->role === 'superuser' || (in_array($actor->role, array('student', 'coordinator'), true) && (int) $actor->id === (int) $id), 403);
        return new UserResource(User::findOrFail($id));

        } catch (\Throwable $exception) { SessionTracer::exception('user.show', $correlationId, $started, 'user_unexpected', $exception); throw $exception;
        } finally { SessionTracer::leave('user.show', $correlationId, $started, 'completed', array('resource_type'=>'user'));
        }
    }

    public function store(Request $request)
    {
        $started = microtime(true); $correlationId = SessionTracer::id(request()->header('X-Correlation-ID')); SessionTracer::enter('user.store', $correlationId, array('resource_type'=>'user'));
        try {
        $request->validate([
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'name' => 'required|max:255',
            'role' => 'required|max:255',
        ]);
        if (!in_array($request->input('role'), array('student', 'coordinator', 'superuser'), true)) abort(422);
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



        } catch (\Throwable $exception) { SessionTracer::exception('user.store', $correlationId, $started, 'user_unexpected', $exception); throw $exception;
        } finally { SessionTracer::leave('user.store', $correlationId, $started, 'completed', array('resource_type'=>'user'));
        }
    }

    public function delete($id)
    {
        $started = microtime(true); $correlationId = SessionTracer::id(request()->header('X-Correlation-ID')); SessionTracer::enter('user.delete', $correlationId, array('resource_type'=>'user'));
        try {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(null, 204);
        // return new UserCollection(User::all());

        } catch (\Throwable $exception) { SessionTracer::exception('user.delete', $correlationId, $started, 'user_unexpected', $exception); throw $exception;
        } finally { SessionTracer::leave('user.delete', $correlationId, $started, 'completed', array('resource_type'=>'user'));
        }
    }


    public function update(Request $request, $id)
        {
        $started = microtime(true); $correlationId = SessionTracer::id(request()->header('X-Correlation-ID')); SessionTracer::enter('user.update', $correlationId, array('resource_type'=>'user'));
        try {
            $current_user = \Auth::user();

            $user = User::findOrFail($id);

            if ($request->has('role') && !in_array($request->input('role'), array('student', 'coordinator', 'superuser'), true)) abort(422);

            $user->name = request('name');
            $user->first_name = request('first_name');
            $user->last_name = request('last_name');

            if ($current_user->role == 'superuser' && $request->has('role')) {
                $user->role = request('role');
            }

            $user->email = request('email');
            if (request('password')) {
                $user->password = Hash::make(request('password'));
            }
            $user->save();

            return response()->json([
                'message' => 'User updated successfully!'
            ], 200);

        } catch (\Throwable $exception) { SessionTracer::exception('user.update', $correlationId, $started, 'user_unexpected', $exception); throw $exception;
        } finally { SessionTracer::leave('user.update', $correlationId, $started, 'completed', array('resource_type'=>'user'));
        }
    }

}
