<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOrder extends CommonModel
{
    use HasFactory;

    protected $table = 'product_orders';

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
    const COL_PRODUCTS = 'products';
    const COL_NOTE = 'note';

    /** value of model */
    const VAL_USER_ID = 'userId';
    const VAL_ORDER_DATE = 'orderDate';
    const VAL_RECEIVER_NAME = 'receiverName';
    const VAL_SHIPPING_METHOD = 'shippingMethod';
    const VAL_PAYMENT_METHOD = 'paymentMethod';
    const VAL_SORT_BY = 'sortBy';
    const VAL_SORT_ORDER = 'sortOrder';
    const VAL_ITEM_PER_PAGE = 'itemPerPage';
    const VAL_PAGE = 'page';
    const VAL_FROM_DATE = 'fromDate';
    const VAL_TO_DATE = 'toDate';

    /** Sort order */
    const ASC_ORDER = 'asc';
    const DESC_ORDER = 'desc';

    /** default value */
    const ITEM_PER_PAGE_DEFAULT = 10;
    const PAGE_DEFAULT = 1;

    // shipping method
    const FAST_SHIPPING = 0;
    const STANDARD_SHIPPING = 1;
    const SHIPPING_MAP = [
        'Giao hàng nhanh',
        'Giao hàng tiêu chuẩn'
    ];

    // payment method
    const MOMO_PAYMENT = 0;
    const COD_PAYMENT = 1;
    const PAYMENT_MAP = [
        'Thanh toán momo',
        'Thanh toán khi nhận hàng'
    ];

    /** order status */
    const CUSTOMER_CANCELED = 0;
    const ADMIN_CANCELED = 1;
    const NOT_CONFIRMED = 2;
    const CONFIRMED = 3;
    const DELIVERY = 4;
    const COMPLETED = 5;

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
        self::COL_PRODUCTS,
        self::COL_NOTE,
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
        self::COL_PRODUCTS => 'array'
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
            self::COL_ID => 'required|numeric',
            self::COL_USER_ID => 'required|numeric',
            self::COL_AMOUNT => 'required|numeric',
            self::COL_ADDRESS => 'required',
            self::COL_EMAIL => 'required|email:rfc,filter',
            self::COL_PHONE => 'required|numeric',
            self::COL_RECEIVER_NAME => 'required',
            self::COL_SHIPPING_METHOD => 'required|between:'.self::FAST_SHIPPING.','.self::STANDARD_SHIPPING,
            self::COL_PAYMENT_METHOD => 'required|between:'.self::MOMO_PAYMENT.','.self::COD_PAYMENT,
            self::COL_NOTE => 'nullable'
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

    public static function returnQuantityProduct(int $orderId)
    {
        $order = ProductOrder::find($orderId);

        $productIds = collect($order->{ProductOrder::COL_PRODUCTS})->pluck(Product::COL_ID);
        $values = collect($order->{ProductOrder::COL_PRODUCTS})->pluck(Product::COL_QUANTITY);
        $products = Product::whereIn(Product::COL_ID, $productIds)->get();
        for ($i = 0; $i < count($productIds); $i++) {
            $product = $products[$i];
            $product->{Product::COL_QUANTITY} += $values[$i];
            if (!$product->save()) {
                return false;
            }
        }
        return true;
    }
}
