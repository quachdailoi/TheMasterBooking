<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends CommonModel
{
    use HasFactory;

    protected $table = 'skills';

    /** Column of table */
    const COL_NAME = 'name';
    const COL_DESCRIPTION = 'description';

    //value of model

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        self::COL_NAME,
        self::COL_DESCRIPTION,
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
            self::COL_NAME => 'required',
            self::COL_DESCRIPTION => 'required',
        ];
        $errorCode = [
            'required' => ':attribute is required.',
        ];

        return CommonModel::validate($data, $validatedFields, $errorCode);
    }
}
