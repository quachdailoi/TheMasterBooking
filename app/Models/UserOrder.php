<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserOrder extends CommonModel
{
    use HasFactory;

    protected $table = 'user_orders';

    /** Column of table */
    const COL_USER_ID = 'user_id';
    const COL_AMOUNT = 'amount';
    const COL_ORDER_DATE = 'order_date';
    const COL_STATUS = 'status';
    const COL_ADDRESS = 'address';
    const COL_EMAIL = 'email';
    const COL_PHONE = 'phone';
    const COL_RECEIVER_NAME = 'receiver_name';
    const COL_SHIPPING_METHOD = 'shipping_method';
    const COL_PAYMENT_METHOD = 'payment_method';

    /** value of model */
    const VAL_USER_ID = 'userId';
    const VAL_ORDER_DATE = 'orderDate';
    const VAL_RECEIVER_NAME = 'receiverName';
    const VAL_SHIPPING_METHOD = 'shippingMethod';
    const VAL_PAYMENT_METHOD = 'paymentMethod';

    // shipping method
    const FAST_SHIPPING = 0;
    const STANDARD_SHIPPING = 1;

    // payment method
    const MOMO_PAYMENT = 0;
    const COD_PAYMENT = 1;

    /** relations */

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        self::COL_USER_ID,
        self::COL_AMOUNT,
        self::COL_ORDER_DATE,
        self::COL_STATUS,
        self::COL_ADDRESS,
        self::COL_EMAIL,
        self::COL_PHONE,
        self::COL_RECEIVER_NAME,
        self::COL_SHIPPING_METHOD,
        self::COL_PAYMENT_METHOD,
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
        self::COL_ORDER_DATE => 'datetime',
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
            self::COL_AMOUNT => 'required|numeric',
            self::COL_ADDRESS => 'required',
            self::COL_EMAIL => 'required|email:rfc,filter',
            self::COL_PHONE => 'required|numeric',
            self::COL_RECEIVER_NAME => 'required',
            self::COL_SHIPPING_METHOD => 'required|between:'.self::FAST_SHIPPING.','.self::STANDARD_SHIPPING,
            self::COL_PAYMENT_METHOD => 'required|between:'.self::MOMO_PAYMENT.','.self::COD_PAYMENT,
        ];
        $errorCode = [
            'required' => ':attribute is required.',
            'numeric' => ':attribute must be a number',
            'email' => 'Email is wrong format.',
            'between' => ':attribute size must be in range :min - :max',
        ];

        return CommonModel::validate($data, $validatedFields, $errorCode);
    }

    /**
     * Get the user.
     */
    public function user()
    {
        return $this->hasOne(User::class, User::COL_ID, self::COL_USER_ID);
    }

    /**
     * Get the productOrder.
     */
    public function productOrders()
    {
        return $this->hasMany(User::class, User::COL_ID, self::COL_USER_ID);
    }
}
