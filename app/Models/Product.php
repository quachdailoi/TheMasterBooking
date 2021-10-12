<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends CommonModel
{
    use HasFactory;

    protected $table = 'products';

    /** Column of table */
    const COL_NAME = 'name';
    const COL_QUANTITY = 'quantity';
    const COL_PRICE = 'price';
    const COL_DESCRIPTION = 'description';
    const COL_STATUS = 'status';
    const COL_CATEGORY_ID = 'category_id';

    /** value of model */
    const VAL_ITEM_PER_PAGE = 'itemPerPage';
    const VAL_PAGE = 'page';
    const VAL_CATEGORY_ID = 'categoryId';
    const VAL_SEARCH_VALUE = 'searchValue';
    const VAL_QUANTITY = 'quantity';
    const VAL_IMAGE = 'image';
    const VAL_AMOUNT = 'amount';
    const VAL_SORT_BY = 'sortBy';
    const VAL_SORT_ORDER = 'sortOrder';

    /** Sort order */
    const ASC_ORDER = 'asc';
    const DESC_ORDER = 'desc';

    /** default value */
    const ITEM_PER_PAGE_DEFAULT = 10;
    const PAGE_DEFAULT = 1;

    /** max images of product */
    const MAX_IMAGES = 1;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['image'];

    /**
     * Get the product's images.
     *
     * @return string
     */
    public function getImageAttribute()
    {
        $imageUrl = $this->file()->first()->{File::COL_PATH} ?? getenv('DEFAULT_PRODUCT_IMAGE_URL');
        return $imageUrl;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        self::COL_NAME,
        self::COL_QUANTITY,
        self::COL_PRICE,
        self::COL_DESCRIPTION,
        self::COL_STATUS,
        self::COL_CATEGORY_ID,
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
            self::COL_NAME => 'required|numeric',
            self::COL_NAME => 'required',
            self::COL_QUANTITY => 'required|numeric',
            self::COL_DESCRIPTION => 'required',
            self::COL_PRICE => 'required|numeric',
            self::COL_STATUS => 'required|numeric',
            self::COL_CATEGORY_ID => 'nullable|numeric',
            self::VAL_ITEM_PER_PAGE => 'nullable|numeric',
            self::VAL_PAGE => 'nullable|numeric'
        ];
        $errorCode = [
            'required' => ':attribute is required.',
            'numeric' => ':attribute must be a number',
        ];

        return CommonModel::validate($data, $validatedFields, $errorCode);
    }

    /**
     * Get the user's file.
     */
    public function file()
    {
        return $this->morphMany(File::class, 'owner');
    }
}
