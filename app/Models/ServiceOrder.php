<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

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
    const COL_SERVICES = 'services';
    const COL_STORE_ID = 'store_id';
    const COL_CANCEL_REASON = 'cancel_reason';
    const COL_RATING = 'rating';
    const COL_FEED_BACK = 'feed_back';

    /** value of model */
    const VAL_USER_ID = 'userId';
    const VAL_ORDER_DATE = 'orderDate';
    const VAL_USER_NAME = 'userName';
    const VAL_STORE_ID = 'storeId';
    const VAL_SORT_BY = 'sortBy';
    const VAL_SORT_ORDER = 'sortOrder';
    const VAL_ITEM_PER_PAGE = 'itemPerPage';
    const VAL_PAGE = 'page';
    const VAL_FROM_DATE = 'fromDate';
    const VAL_TO_DATE = 'toDate';
    const VAL_CANCEL_REASON = 'cancelReason';
    const VAL_FEED_BACK = 'feedback';

    /** Sort order */
    const ASC_ORDER = 'asc';
    const DESC_ORDER = 'desc';

    /** default value */
    const ITEM_PER_PAGE_DEFAULT = 10;
    const PAGE_DEFAULT = 1;

    //value for status
    const CUSTOMER_CANCEL = 0;
    const MANAGE_CANCEL = 1;
    const NOT_COMFIRM = 2;
    const CONFIRMED= 3;
    const USED = 4;

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
        self::COL_SERVICES,
        self::COL_STORE_ID,
        self::COL_CANCEL_REASON,
        self::COL_RATING,
        self::COL_FEED_BACK,
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
        self::COL_SERVICES => 'array',
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
            self::COL_ID => "required|numeric",
            self::VAL_USER_ID => 'required|numeric',
            self::COL_AMOUNT => 'required|double',
            self::VAL_ORDER_DATE => "required|date_format:Y-m-d H:i:s|before_or_equal:$nowStr",
            self::COL_STATUS => 'nullable|numeric',
            self::COL_EMAIL => 'nullable|email',
            self::COL_PHONE => 'required|numeric',
            self::VAL_USER_NAME => 'required',
            self::COL_NOTE => 'nullable',
            self::COL_SERVICES => 'required|array',
            self::VAL_STORE_ID => 'required|numeric',
            'serviceIds' => 'required|array',
            self::VAL_PAGE => 'numeric',
            self::VAL_ITEM_PER_PAGE => 'numeric',
            self::VAL_SORT_ORDER => 'in:asc,desc',
            self::VAL_FROM_DATE => 'date_format:Y-m-d',
            self::VAL_TO_DATE => 'date_format:Y-m-d|after_or_equal:'.self::VAL_FROM_DATE,
            self::VAL_SORT_BY => 'in:id,order_date',
            self::VAL_CANCEL_REASON => 'required|max:120',
            self::COL_RATING => 'required|numeric|between:0,5',
            self::VAL_FEED_BACK => 'required|max:120',
        ];
        $errorCode = [
            'required' => ':attribute is required.',
            'numeric' => ':attribute must be a number',
            'date_format' => 'Order datetime must be in format Year-month-day',
            'before_or_equal' => 'Order datetime must be after now',
        ];

        return CommonModel::validate($data, $validatedFields, $errorCode);
    }
}
