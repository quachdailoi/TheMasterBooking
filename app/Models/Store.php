<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends CommonModel
{
    use HasFactory;

    protected $table = 'stores';

    /** Column of table */
    const COL_PHONE = 'phone';
    const COL_NAME = 'name';
    const COL_ADDRESS = 'address';
    const COL_WORK_SCHEDULE = 'work_schedule';
    const COL_STATUS = 'status';

    /** value of model */
    const VAL_WORK_SCHEDULE = 'workSchedule';
    const VAL_OPEN_AT = 'openAt';
    const VAL_CLOSE_AT = 'closeAt';
    const MODAY = 'moday';
    const TUESDAY = 'tuesday';
    const WEDNESDAY = 'wednesday';
    const THURSDAY = 'thursday';
    const FRIDAY = 'friday';
    const SATURDAY = 'saturday';
    const SUNDAY = 'sunday';

    /** relations */
    const CATEGORIES = 'categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        self::COL_PHONE,
        self::COL_NAME,
        self::COL_ADDRESS,
        self::COL_WORK_SCHEDULE,
        self::COL_STATUS,
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
        self::VAL_OPEN_AT => 'datetime:H:i',
        self::VAL_CLOSE_AT => 'datetime:H:i',
        self::COL_WORK_SCHEDULE => 'array',
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
            self::COL_ID => 'numeric',
            self::COL_PHONE => 'required|numeric',
            self::COL_NAME => 'required',
            self::COL_ADDRESS => 'required',
            self::VAL_OPEN_AT => 'required|date_format:H:i',
            self::VAL_CLOSE_AT => 'required|date_format:H:i|after:openAt',
            self::COL_STATUS => 'nullable|numeric',
        ];
        $errorCode = [
            'required' => ':attribute is required.',
            'numeric' => ':attribute must be a number',
            'date_format' => ':attribute is in wrong format',
            'after' => 'Close time must be after open time.',
        ];

        return CommonModel::validate($data, $validatedFields, $errorCode);
    }

    public static function workScheduleGen(array $monday, array $tuesday, array $wednesday, array $thursday, array $priday, array $saturday, array $sunday)
    {
        return [
            'moday' => $monday,
            'tuesday' => $tuesday,
            'wednesday' => $wednesday,
            'thursday' => $thursday,
            'priday' => $priday,
            'saturday' => $saturday,
            'sunday' => $sunday,
        ];
    }

    /**
     * Get the user's file.
     */
    public function file()
    {
        return $this->morphOne(File::class, 'owner')->first();
    }

    /**
     * Get the store's categories.
     */
    public function categories()
    {
        return $this->hasMany(Category::class, Category::COL_STORE_ID, self::COL_ID);
    }
}
