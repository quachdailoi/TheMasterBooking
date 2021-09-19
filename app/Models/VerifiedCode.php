<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerifiedCode extends CommonModel
{
    use HasFactory;

    protected $table = 'verified_codes';

    /** Column of table */
    const COL_ID = 'id';
    const COL_RECEIVER = 'receiver';
    const COL_CODE = 'code';
    const COL_TYPE = 'type';
    const COL_CHANNEL = 'channel';
    const COL_WAS_VERIFIED = 'wasVerified';

    // value of model
    const NOT_VERIFY_STATUS = false;
    const VERIFIED_STATUS = true;
    const REGISTER_TYPE = 0;
    const FORGOT_PASSWORD_TYPE = 1;
    const EMAIL_CHANNEL = 1;
    const PHONE_CHANNEL = 0;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        self::COL_ID,
        self::COL_RECEIVER,
        self::COL_CODE,
        self::COL_TYPE,
        self::COL_CHANNEL,
        self::COL_WAS_VERIFIED,
        self::COL_CREATED_AT,
        self::COL_UPDATED_AT,
        self::COL_DELETED_AT,
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        self::COL_CREATED_AT,
        self::COL_UPDATED_AT,
        self::COL_DELETED_AT,
    ];

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
            self::COL_TYPE => 'required|numeric|between:0,1',
            self::COL_CHANNEL => 'required|numeric|between:0,1',
            self::COL_WAS_VERIFIED => 'required|boolean',
        ];
        $errorCode = [
            self::COL_TYPE => 'IER400011',
            self::COL_CHANNEL => 'IER400012',
        ];
        return CommonModel::validate($data, $validatedFields, $errorCode);
    }
}
