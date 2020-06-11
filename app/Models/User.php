<?php

namespace App\Models;

use App\Models\Role;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $fillable = [
        'name','username', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id')->withTimestamps();
    }

    public function branches()
    {
        return $this->hasMany(Branch::class, 'store_id');
    }

    public function items()
    {
        return $this->hasManyThrough(
            Item::class,
            Branch::class,
            'store_id',
            'branch_id'
        );
    }

    public function info()
    {
        return $this->hasOne(UserInfo::class, 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id')->where('orders.is_cart', 0);
    }

    public function cart()
    {
        return $this->hasManyThrough(
            OrderItem::class,
            Order::class,
            'user_id',
            'order_id'
        )->where('orders.is_cart', 1);
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'user_follower', 'user_id', 'follower_id')->withTimestamps();
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follower', 'follower_id', 'user_id')->withTimestamps();
    }

    public function history()
    {
        return $this->hasMany(SearchHistory::class);
    }

    public function uploadHistories()
    {
        return $this->hasMany(UploadHistory::class);
    }

    public function isStore()
    {
        if($this->roles()->where('name', 'store')->first())
            return true;
        return false;
    }

    public function isPharmacy()
    {
        if($this->roles()->where('name', 'pharmacy')->first())
            return true;
        return false;
    }

    public function scopeStore($query)
    {
        return $query->whereHas('roles', function (Builder $query) {
            $query->where('name', 'store');
        });
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('username', 'like', '%'.$search.'%' )->orWhere('name', 'like', '%'.$search.'%');
    }

    public function scopeNotAdmin($query)
    {
        return $query->whereHas('roles', function (Builder $query) {
            $query->where('name', '<>', 'admin');
        });
    }

    public function scopeRole($query, $role)
    {
        return $query->whereHas('roles', function (Builder $query) use ($role) {
            if($role == 'all')
                $query->where('name', '<>', 'admin');
            else
                $query->where('name', $role);
        });
    }
}
