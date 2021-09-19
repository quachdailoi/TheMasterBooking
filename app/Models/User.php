<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $table = 'users';

    /** Column of table */
    const COL_ID = 'id';
    const COL_NAME = 'name';
    const COL_EMAIL = 'email';
    const COL_PHONE = 'phone';
    const COL_EMAIL_VERIFIED_AT = 'email_verified_at';
    const COL_PHONE_VERIFIED_AT = 'phone_verified_at';
    const COL_PASSWORD = 'password';
    const COL_GENDER = 'gender';
    const COL_BIRTHDAY = 'birthDay';
    const COL_STATUS = 'status';
    const COL_ROLE_ID = 'role_id';
    const COL_STORE_ID = 'store_id';

    /** Value of model */
    const VAL_REMEMBER_TOKEN = 'remember_token';
    const VAL_CONFIRM_PASSWORD = 'confirmPassword';
    const ACCESS_TOKEN = 'accessToken';

    // Value
    const ADMIN_ROLE_ID = 3;
    const MANAGER_ROLE_ID = 2;
    const CUSTOMER_ROLE_ID = 1;
    const ACTIVE_STATUS = 1;
    const UNACTIVE_STATUS = 0;

    // message path
    const MESSAGE_PATH = '/messages/user.';

    // IER code
    const IER400 = 'IER400001';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        self::COL_NAME,
        self::COL_EMAIL,
        self::COL_PHONE,
        self::COL_EMAIL_VERIFIED_AT,
        self::COL_PHONE_VERIFIED_AT,
        self::COL_PASSWORD,
        self::COL_GENDER,
        self::COL_BIRTHDAY,
        self::COL_STATUS,
        self::COL_ROLE_ID,
        self::COL_STORE_ID,
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        self::COL_PASSWORD,
        self::VAL_REMEMBER_TOKEN,
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        self::COL_EMAIL_VERIFIED_AT => 'datetime',
        self::COL_PHONE_VERIFIED_AT => 'datetime',
    ];

    public static function getTableName()
    {
        return with(new static)->getTableName();
    }

    /**
     * @functionName: validator
     * @type:         public static
     * @description:  validate parameter
     * @param:        \Array $data
     * @param:        \Array $rule
     * @param:        \Array $message nullable
     * @return:       \Validate $validate
     */
    public static function validator(array $data)
    {
        $validatedFields = [
            self::COL_NAME => 'required',
            self::COL_EMAIL => 'required|email:rfc,filter',
            self::COL_PHONE => 'required|numeric',
            self::COL_PASSWORD => 'required|between:6,25|required_with:confirmPassword',
            self::VAL_CONFIRM_PASSWORD => 'required|same:password',
            self::COL_GENDER => 'nullable|numeric',
            self::COL_BIRTHDAY => 'required',
            self::COL_STATUS => 'required|numeric',
            self::COL_ROLE_ID => 'required|numeric',
            self::COL_STORE_ID => 'nullable|numeric',
        ];
        $errorCode = [
            'required' => ':attribute is required.',
            'email' => 'Email is wrong format.',
            'numeric' => ':attribute must be a number',
            'between' => ':attribute size must be in range :min - :max',
            'same' => ':attribute must be match with :other',
        ];
        return CommonModel::validate($data, $validatedFields, $errorCode);
    }

    public function findForPassport($phoneNumber)
    {
        return $this->where(self::COL_PHONE, $phoneNumber)->first();
    }
}
