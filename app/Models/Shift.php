<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends CommonModel
{
    use HasFactory;

    protected $table = 'shifts';

    /** Column of table */
    const COL_START_TIME = 'start_time';
    const COL_END_TIME = 'end_time';
    const COL_STORE_ID = 'store_id';
    const COL_DAY_IN_WEEK = 'day_in_week';
    const COL_SHIFT_NAME = 'shift_name';

    /** day in week */
    const DAY_IN_WEEK = [
        'Chủ nhật',
        'Thứ 2',
        'Thứ 3',
        'Thứ 4',
        'Thứ 5',
        'Thứ 6',
        'Thứ 7',
    ];

    //value of model
    const VAL_START_TIME = 'startTime';
    const VAL_END_TIME = 'endTime';
    const VAL_STORE_ID = 'storeId';
    const VAL_DAY_IN_WEEK = 'dayInWeek';
    const VAL_SHIFT_NAME = 'shiftName';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        self::COL_START_TIME,
        self::COL_END_TIME,
        self::COL_DAY_IN_WEEK,
        self::COL_SHIFT_NAME,
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
        self::COL_START_TIME => 'datetime:Y-m-d H:i:s',
        self::COL_END_TIME => 'datetime:Y-m-d H:i:s',
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
            self::VAL_START_TIME => 'required|date_format:H:i',
            self::VAL_END_TIME => 'required|date_format:H:i|after:' . self::VAL_START_TIME,
            self::VAL_DAY_IN_WEEK => 'required|between:0,7',
            self::VAL_SHIFT_NAME => 'required',
            self::VAL_STORE_ID => 'nullable|numeric',
        ];
        $errorCode = [
            'required' => ':attribute is required.',
            'numeric' => ':attribute must be a number',
            'date_format' => 'time must be in format hour:minute',
            'between' => ':attribute size must be in range :min - :max',
        ];

        return CommonModel::validate($data, $validatedFields, $errorCode);
    }

    public static function mergeShift($shifts)
    {
        $dayInWeekShift = [];

        foreach ($shifts as $shift) {
            $dayInWeek = $shift->{Shift::COL_DAY_IN_WEEK};
            $dayShift = $dayInWeekShift[$dayInWeek] ?? [];
            array_push($dayShift, $shift);

            $dayInWeekShift[$dayInWeek] = $dayShift;
        }

        return $dayInWeekShift;
    }
}
