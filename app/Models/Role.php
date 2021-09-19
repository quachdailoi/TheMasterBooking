<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends CommonModel
{
    use HasFactory;

    protected $table = 'roles';

    /** Column of table */
    const COL_ID = 'id';
    const COL_NAME = 'name';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        self::COL_NAME,
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
    protected $casts = [];

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
        ];
        $errorCode = [
            'required' => ':attribute is required.',
        ];
        return CommonModel::validate($data, $validatedFields, $errorCode);
    }
}
