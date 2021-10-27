<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOrder extends CommonModel
{
    use HasFactory;

    protected $table = 'service_orders';

    /** Column of table */
    const COL_USER_ID = 'user_id';
    const COL_AMOUNT = 'amount';
    const COL_ORDER_DATE = 'order_date';
    const COL_STATUS = 'status';
    const COL_EMAIL = 'email';
    const COL_PHONE = 'phone';
    const COL_USER_NAME = 'user_name';
    const COL_NOTE = 'note';
    const COL_SETTINGS = 'settings';
    const COL_STORE_ID = 'store_id';

    /** value of model */
    const VAL_USER_ID = 'userId';
    const VAL_ORDER_DATE = 'orderDate';
    const VAL_USER_NAME = 'userName';
    const VAL_STORE_ID = 'storeId';

    //value for status
    const CANCEL = 0;
    const JUST_ORDER = 1;
    const CONFIRM = 2;
    const USED = 3;

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
        self::COL_EMAIL,
        self::COL_PHONE,
        self::COL_USER_NAME,
        self::COL_NOTE,
        self::COL_SETTINGS,
        self::COL_STORE_ID,
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
        self::COL_ORDER_DATE => 'datetime:Y-m-d H:i:s',
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
        $now = new DateTime();
        $nowStr = $now->format('Y-m-d H:i:s');
        $validatedFields = [
            self::VAL_USER_ID => 'required|numeric',
            self::COL_AMOUNT => 'required|double',
            self::VAL_ORDER_DATE => "required|date_format:Y-m-d H:i:s|before_or_equal:$nowStr",
            self::COL_STATUS => 'nullable|numeric',
            self::COL_EMAIL => 'nullable|email',
            self::COL_PHONE => 'required|numeric',
            self::VAL_USER_NAME => 'required',
            self::COL_NOTE => 'nullable',
            self::COL_SETTINGS => 'nullable',
            self::VAL_STORE_ID => 'required|numeric',
        ];
        $errorCode = [
            'required' => ':attribute is required.',
            'numeric' => ':attribute must be a number',
            'date_format' => 'Order datetime must be in format Year-month-day hour-minute-second',
            'before_or_equal' => 'Order datetime must be after now',
        ];

        return CommonModel::validate($data, $validatedFields, $errorCode);
    }
}
