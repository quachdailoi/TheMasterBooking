<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Category extends CommonModel
{
    use HasFactory;

    protected $table = 'categories';

    /** Column of table */
    const COL_NAME = 'name';

    /** value of model */

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        self::COL_NAME,
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
            'numeric' => ':attribute must be a number',
        ];

        return CommonModel::validate($data, $validatedFields, $errorCode);
    }

    public function products()
    {
        return $this->hasMany(Product::class, Product::COL_CATEGORY_ID, self::COL_ID);
    }

    public function homeProducts()
    {
        return $this->hasMany(Product::class, Product::COL_CATEGORY_ID, self::COL_ID)->take(5);
    }
}
