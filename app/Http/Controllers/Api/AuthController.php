<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LoginResource;
use App\Http\Resources\UserBasicInfoResource;
use App\Models\User;
use App\Modelpublic function __construct()
    {
        $this->middleware('auth-token', ['except' => ['login', 'registerPharmcay', 'registerStore']]);
    }s\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{



    public function login(Request $request)
    {
        $this->validation($request, [
            'username' => 'required',
            'password' => 'required'
        ]);

        $credentials = request(['username', 'password']);
        if (! $token = auth()->attempt($credentials)) {
            unset($credentials['username']);
            $credentials['email'] = request('username');
            if (! $token = auth()->attempt($credentials))
                return $this->handleResponse(0, ['message' => 'Unauthorized']);
        }

        return $this->handleResponse(1, new LoginResource(auth()->user(), $token));
    }

    public function logout()
    {
        auth()->logout();

        return $this->handleResponse(1, ['message' => 'Successfully logged out']);
    }

    public function registerPharmcay(Request $request)
    {
        $this->validation($request, [
            'name' => 'required',
            'username' => 'required|unique:users',
            'password' => 'required',
            'email' => 'required|unique:users',
            'lng' => 'required',
            'lat' => 'required'
        ]);

        $userMappedRequest = $request->only('name', 'username', 'password', 'email');
        $userInfoMAppedRequest = $request->only('lng', 'lat');
        $userMappedRequest['password'] = Hash::make($request->password);

        DB::beginTransaction();

        $user = User::create($userMappedRequest);
        $userInfoMAppedRequest['user_id'] = $user->id;
        $user->roles()->attach(3);
        $userInfo = UserInfo::create($userInfoMAppedRequest);

        DB::commit();

        $token = auth()->attempt(request(['username', 'password']));
        return $this->handleResponse(1, new LoginResource(auth()->user(), $token));

    }

    public function registerStore(Request $request)
    {
        $this->validation($request, [
            'name' => 'required',
            'username' => 'required|unique:users',
            'password' => 'required',
            'email' => 'required|unique:users'
        ]);

        $userMappedRequest = $request->only('name', 'username', 'password', 'email');
        $userMappedRequest['password'] = Hash::make($request->password);

        DB::beginTransaction();

        $user = User::create($userMappedRequest);
        $user->roles()->attach(2);

        DB::commit();

        $token = auth()->attempt(request(['username', 'password']));
        return $this->handleResponse(1, new LoginResource(auth()->user(), $token));

    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $this->validation($request, [
            'username' => 'unique:users,username,' . $user->id,
            'email' => 'unique:users,email,' . $user->id
        ]);
        $userMappedRequest = $request->only('name', 'username', 'password', 'email');
        if($request->has('password'))
            $userMappedRequest['password'] = Hash::make($request->password);

        $user->update($userMappedRequest);

        return $this->handleResponse(1, new UserBasicInfoResource($user));
    }

    // public function refresh()
    // {
    //     return $this->respondWithToken(auth()->refresh());
    // }

    // protected function respondWithToken($token)
    // {
    //     return response()->json([
    //         'access_token' => $token,
    //         'token_type' => 'bearer',
    //         'expires_in' => auth()->factory()->getTTL() * 60
    //     ]);
    // }
}
