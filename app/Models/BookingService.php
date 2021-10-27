<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingService extends CommonModel
{
    use HasFactory;

    protected $table = 'booking_services';

    /** Column of table */
    const COL_SERVICE_ID = 'service_id';
    const COL_ORDER_ID = 'order_id';

    /** value of model */
    const VAL_SERVICE_ID = 'serviceId';
    const VAL_ORDER_ID = 'orderId';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        self::COL_ORDER_ID,
        self::COL_SERVICE_ID,
        self::COL_CREATED_AT,
        self::COL_UPDATED_AT,
        self::COL_DELETED_AT,
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
            self::VAL_ORDER_ID => 'required|numeric',
            self::VAL_SERVICE_ID => 'required|numeric',
            'serviceIds' => 'required|array',
        ];
        $errorCode = [
            'required' => ':attribute is required.',
            'numeric' => ':attribute must be a number',
        ];

        return CommonModel::validate($data, $validatedFields, $errorCode);
    }
}
