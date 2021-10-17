<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOrder extends CommonModel
{
    use HasFactory;

    protected $table = 'product_orders';

    /** Column of table */
    const COL_ORDER_ID = 'order_id';
    const COL_PRODUCT_ID = 'product_id';

    /** value of model */
    const VAL_ORDER_ID = 'orderId';
    const VAL_PRODUCT_ID = 'productId';

    /** relations */

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        self::COL_ORDER_ID,
        self::COL_PRODUCT_ID,
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
            self::COL_ORDER_ID => 'required|numeric',
            self::COL_PRODUCT_ID => 'required|numeric',
        ];
        $errorCode = [
            'required' => ':attribute is required.',
            'numeric' => ':attribute must be a number',
        ];

        return CommonModel::validate($data, $validatedFields, $errorCode);
    }

    /**
     * Get the order.
     */
    public function order()
    {
        return $this->hasOne(UserOrder::class, UserOrder::COL_ID, self::COL_ORDER_ID);
    }

    /**
     * Get the product.
     */
    public function product()
    {
        return $this->hasOne(Product::class, Product::COL_ID, self::COL_PRODUCT_ID);
    }
}
