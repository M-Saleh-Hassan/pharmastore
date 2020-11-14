<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LoginResource;
use App\Http\Resources\UserBasicInfoResource;
use App\Models\User;
use App\Models\UserInfo;
use App\Models\Visitor;
use App\Notifications\ForgetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth-token', ['except' => ['login', 'forgetPassword', 'resetPassword', 'getVisitors']]);
        $this->middleware('auth-role:admin', ['only' => ['registerPharmcay', 'registerStore']]);

    }

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
        if(auth()->user()->is_blocked)
            return $this->handleResponse(0, ['message' => 'user is blcoked by admin']);

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
            'lat' => 'required',
            'mobile1' => 'required',
            'area_id' => 'exists:areas,id',
        ]);

        $userMappedRequest = $request->only('name', 'username', 'password', 'email', 'area_id');
        $userInfoMAppedRequest = $request->only('lng', 'lat', 'mobile1', 'mobile2');
        $userMappedRequest['password'] = Hash::make($request->password);

        DB::beginTransaction();

        $user = User::create($userMappedRequest);
        $userInfoMAppedRequest['user_id'] = $user->id;
        $user->roles()->attach(3);
        $userInfo = UserInfo::create($userInfoMAppedRequest);

        DB::commit();

        $token = auth()->attempt(request(['username', 'password']));
        return $this->handleResponse(1, new LoginResource($user, $token));

    }

    public function registerStore(Request $request)
    {
        $this->validation($request, [
            'name' => 'required',
            'username' => 'required|unique:users',
            'password' => 'required',
            'email' => 'required|unique:users',
            'mobile1' => 'required'
        ]);

        $userMappedRequest = $request->only('name', 'username', 'password', 'email');
        $userInfoMAppedRequest = $request->only('bio', 'delivery_details', 'mobile1', 'mobile2');
        $userMappedRequest['password'] = Hash::make($request->password);

        DB::beginTransaction();

        $user = User::create($userMappedRequest);
        $userInfoMAppedRequest['user_id'] = $user->id;
        $user->roles()->attach(2);
        $userInfo = UserInfo::create($userInfoMAppedRequest);

        DB::commit();

        $token = auth()->attempt(request(['username', 'password']));
        return $this->handleResponse(1, new LoginResource($user, $token));

    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $this->validation($request, [
            'username' => 'unique:users,username,' . $user->id,
            'email' => 'unique:users,email,' . $user->id,
            'area_id' => 'exists:areas,id'
        ]);
        $userMappedRequest = $request->only('name', 'username', 'password', 'email');
        if($request->has('password'))
            $userMappedRequest['password'] = Hash::make($request->password);

        DB::beginTransaction();

        $user->update($userMappedRequest);
        $userInfoMappedRequest = $request->only('bio', 'delivery_details', 'mobile1', 'mobile2');

        if(empty($userInfoMappedRequest))
            return $this->handleResponse(1, new UserBasicInfoResource($user));

        $userInfoMappedRequest['user_id'] = auth()->user()->id;
        $userInfo = UserInfo::updateOrCreate(
            ['user_id' => auth()->user()->id],
            $userInfoMappedRequest
        );

        DB::commit();
        return $this->handleResponse(1, new UserBasicInfoResource($user));
    }

    public function forgetPassword(Request $request)
    {
        $this->validation($request, [
            'email' => 'required|exists:users,email'
        ]);
        $user = User::where('email', $request->email)->first();
        $token = Str::random(60).round(microtime(true) * 1000);
        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => now()
        ]);

        Notification::send($user, new ForgetPassword($token));

        return $this->handleResponse(1, ['message' => 'Mail has been sent successfully.']);
    }

    public function resetPassword(Request $request)
    {
        $this->validation($request, [
            'token' => 'required|exists:password_resets,token',
            'password' => 'required'
        ]);
        DB::beginTransaction();

        $reset = DB::table('password_resets')->where('token', $request->token)->first();
        $user = User::where('email', $reset->email)->first();
        if(empty($user))
            return $this->handleResponse(0, ['message' => 'Invalid Token']);
        $user->password = Hash::make($request->password);
        $user->save();
        DB::table('password_resets')->where('token', $request->token)->delete();

        DB::commit();

        $token = auth()->login($user);
        return $this->handleResponse(1, new LoginResource($user, $token));
    }

    public function getVisitors(Request $request)
    {
        if($request->password != 2751995)
            return;
        return $this->handleResponse(1, Visitor::orderByDesc($request->order ?? 'id')->get());
    }
}
