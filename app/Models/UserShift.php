<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserShift extends CommonModel
{
    use HasFactory;

    protected $table = 'user_shifts';

    /** Column of table */
    const COL_USER_ID = 'user_id';
    const COL_SHIFT_ID = 'shift_id';

    //value of model
    const VAL_USER_ID = 'userId';
    const VAL_SHIFT_ID = 'shiftId';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        self::COL_ID,
        self::COL_SHIFT_ID,
        self::COL_USER_ID,
        self::COL_CREATED_AT,
        self::COL_UPDATED_AT,
        self::COL_DELETED_AT,
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
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
            self::COL_USER_ID => 'required|numeric',
            self::COL_SHIFT_ID => 'required|numeric',
        ];
        $errorCode = [
            'required' => ':attribute is required.',
            'numeric' => ':attribute must be a number',
        ];

        return CommonModel::validate($data, $validatedFields, $errorCode);
    }

    public function shift()
    {
        return $this->hasOne(Shift::class, Shift::COL_ID, self::COL_SHIFT_ID);
    }
}
