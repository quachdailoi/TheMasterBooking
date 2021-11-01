<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
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
    const COL_CART = 'cart';

    /** Value of model */
    const VAL_REMEMBER_TOKEN = 'remember_token';
    const VAL_CONFIRM_PASSWORD = 'confirmPassword';
    const ACCESS_TOKEN = 'accessToken';
    const VAL_CURRENT_PASSWORD = 'currentPassword';
    const VAL_NEW_PASSWORD = 'newPassword';
    const VAL_CONFIRM_NEW_PASSWORD = 'confirmNewPassword';
    const VAL_CODE = 'code';
    const VAL_TYPE = 'type';
    const VAL_CHANNEL = 'channel';
    const VAL_RECEIVER = 'receiver';
    const VAL_USER_ID = 'userId';
    const VAL_AVATAR = 'avatar';
    const VAL_ROLE_ID = 'roleId';

    // Value
    const STAFF_ROLE_ID = 4;
    const ADMIN_ROLE_ID = 3;
    const MANAGER_ROLE_ID = 2;
    const CUSTOMER_ROLE_ID = 1;
    const ACTIVE_STATUS = 1;
    const UNACTIVE_STATUS = 0;

    /** relation function */
    const FILE_RELATIONSHIP = 'file';

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
        self::COL_CART => 'array',
    ];


    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['avatar', 'shifts', 'skills'];

    /**
     * Get the user's avatar.
     *
     * @return string
     */
    public function getAvatarAttribute()
    {
        $images = $this->files()->select(
            File::COL_ID . ' as fileId',
            File::COL_PATH . ' as filePath',
        )->get()->toArray();
        if (count($images) == 1) {
            $images = $images[0];
        } elseif (count($images) == 0) {
            $images = getenv('DEFAULT_USER_AVATAR_URL');
        }
        return $images;
    }

    /**
     * Get the staff's shift.
     *
     * @return string
     */
    public function getShiftsAttribute()
    {
        if ($this->{self::COL_ROLE_ID} = self::STAFF_ROLE_ID) {
            $shifts = UserShift::with('shift')
                ->where(UserShift::COL_USER_ID, $this->{User::COL_ID})
                ->get()->pluck('shift');
            return Shift::mergeShift($shifts);
        }
    }

    /**
     * Get the staff's skill.
     *
     * @return string
     */
    public function getSkillsAttribute()
    {
        if ($this->{self::COL_ROLE_ID} = self::STAFF_ROLE_ID) {
            $skills = UserSkill::with('skill')
                ->where(UserSkill::COL_USER_ID, $this->{User::COL_ID})
                ->get()->pluck('skill');
            return $skills;
        }
    }

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
    public static function validator(array $data, $receiverChannel = VerifiedCode::EMAIL_CHANNEL)
    {
        $mailRules = 'required|email:rfc,filter';
        $phoneRules = 'required|numeric';
        if ($receiverChannel == VerifiedCode::EMAIL_CHANNEL) {
            $receiverRules = $mailRules;
        } else {
            $receiverRules = $phoneRules;
        }
        $validatedFields = [
            self::COL_NAME => 'required',
            self::COL_EMAIL => $mailRules,
            self::COL_PHONE => $phoneRules,
            self::COL_PASSWORD => 'required|between:6,25|required_with:' . self::VAL_CONFIRM_PASSWORD,
            self::VAL_CONFIRM_PASSWORD => 'required|same:' . self::COL_PASSWORD,
            self::VAL_NEW_PASSWORD => 'required|between:6,25|required_with:' . self::VAL_CONFIRM_NEW_PASSWORD,
            self::VAL_CONFIRM_NEW_PASSWORD => 'required|same:' . self::VAL_NEW_PASSWORD,
            self::VAL_CURRENT_PASSWORD => 'required',
            self::COL_GENDER => 'nullable|numeric',
            self::COL_BIRTHDAY => 'nullable|before_or_equal:'.\Carbon\Carbon::now()->subYears(10)->format('Y-m-d'),
            self::COL_STATUS => 'required|numeric',
            self::COL_ROLE_ID => 'required|numeric',
            self::COL_STORE_ID => 'nullable|numeric',
            self::VAL_TYPE => 'required|numeric|between:0,1',
            self::VAL_CHANNEL => 'required|numeric|between:0,1',
            self::VAL_RECEIVER => $receiverRules,
            self::VAL_USER_ID => $receiverRules,
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

    public function findForPassport($userId)
    {
        return $this->where(self::COL_PHONE, $userId)->orWhere(self::COL_EMAIL, $userId)->first();
    }

    /**
     * Get the user's file.
     */
    public function files()
    {
        return $this->morphMany(File::class, 'owner');
    }

    public function userShifts()
    {
        return $this->hasMany(UserShift::class, UserShift::COL_USER_ID, self::COL_ID);
    }
}
