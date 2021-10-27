<?php

namespace App\Models;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
    const COL_SERVICE_ID = 'serviceId';
    const COL_CITY = 'city';
    const COL_SERVICE_SLOTS = 'service_slots';

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
    const VAL_IMAGES = 'images';
    const VAL_SERVICE_SLOTS = 'serviceSlots';

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
        self::COL_CITY,
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
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['images'];

    /**
     * Get the user's avatar.
     *
     * @return string
     */
    public function getImagesAttribute()
    {
        $images = $this->files()->select(
            File::COL_ID . ' as fileId',
            File::COL_PATH . ' as filePath',
        )->get()->toArray();
        if (count($images) == 1) {
            $images = $images[0];
        } elseif (count($images) == 0) {
            $images = getenv('DEFAULT_STORE_IMAGE_URL');
        }
        return $images;
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
            self::COL_SERVICE_ID => 'numeric',
            self::COL_CITY => 'required',
        ];
        $errorCode = [
            'required' => ':attribute is required.',
            'numeric' => ':attribute must be a number',
            'date_format' => ':attribute is in wrong format',
            'after' => 'Close time must be after open time.',
        ];

        return CommonModel::validate($data, $validatedFields, $errorCode);
    }

    public static function checkExist($storeId) {
        return (bool)Store::find($storeId);
    }

    public static function getBookingDays($store)
    {
        $workSchedule = array_values($store->{Store::COL_WORK_SCHEDULE});
        $bookingDayWeek = [];
        $dayOfWeek  = Carbon::now()->dayOfWeek;
        for ($i = 0; $i < 3; $i++) {
            $scheduleInDay = $workSchedule[$dayOfWeek];
            array_push($bookingDayWeek, $scheduleInDay);
            $dayOfWeek += 1;
        }
        return $bookingDayWeek;
    }

    public static function genBookingTimePeriod($storeId, $openAtStr, $closeAtStr, int $nextDay = 0)
    {
        $openAt = new DateTime($openAtStr);
        $remainTimeToNextHour = 60 - $openAt->format('i');
        $startBookingTime = $openAt->modify("+ $remainTimeToNextHour minutes");

        $closeAt = new DateTime($closeAtStr);
        $remainTimeToNextHour = 60 - $closeAt->format('i');
        $endBookingTime = $closeAt->modify("- $remainTimeToNextHour minutes");

        $mapBookingTime = [];
        $arrayQueryDateTime = [];
        while ($endBookingTime > $startBookingTime) {
            $bookingTime = $startBookingTime->format('H:i');
            $queryBookingDate = $startBookingTime->format('Y-m-d H:i:s');
            if ($nextDay != 0) {
                $queryDate = new DateTime($queryBookingDate);
                $queryDate->modify("+ $nextDay days");
                $queryBookingDate = $queryDate->format('Y-m-d H:i:s');
            }
            $mapBookingTime[$bookingTime] = true;
            array_push($arrayQueryDateTime, $queryBookingDate);
            $startBookingTime->modify('+ 1 hours');
        }
        $countBookingTime = DB::table('service_orders')->select('order_date', DB::raw('count(id) as slots'))
            ->whereIn('order_date', $arrayQueryDateTime)
            ->where(ServiceOrder::COL_STATUS, ServiceOrder::CONFIRM)
            ->groupBy('order_date')->pluck('slots', 'order_date');
        $store = Store::find($storeId);
        $maxBookingSlots = (int)$store->{Store::COL_SERVICE_SLOTS};

        foreach ($countBookingTime as $orderDateTime => $count) {
            if ($count >= $maxBookingSlots) {
                $tmp = new DateTime($orderDateTime);
                $orderTime = $tmp->format('H:i');
                $mapBookingTime[$orderTime] = false;
            }
        }

        return $mapBookingTime;
    }

    /**
     * Get the user's file.
     */
    public function files()
    {
        return $this->morphMany(File::class, 'owner');
    }
}
