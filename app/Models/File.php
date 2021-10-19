<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class File extends CommonModel
{
    use HasFactory;

    protected $table = 'files';

    /** Column of table */
    const COL_PATH = 'path';
    const COL_OWNER_ID = 'owner_id';
    const COL_OWNER_TYPE = 'owner_type';
    const COL_STATUS = 'status';
    const COL_TYPE = 'type';

    /** value of model */
    const VAL_OWNER_TYPE = 'ownerType';
    const VAL_OWNER_ID = 'ownerId';
    const VAL_FILE_ID = 'fileId';
    const VAL_IMAGE_FILE = 'imageFile';
    const VAL_FILE = 'file';
    const VAL_INDEX = 'index';

    /** file type */
    const IMAGE_TYPE = 0;
    const VIDEO_TYPE = 1;
    const OTHERS_TYPE = 2;

    /** file status */
    const INACTIVE_TYPE = 0;
    const ACTIVE_TYPE = 1;

    /** file validation */
    const FILE_VALIDATIONS = [
        'required|image|mimes:jpeg,png,jpg,gif,svg|mimetypes:image/gif,image/jpeg,image/png|max:2048',
        'required|max:2048|mimes:video/x-ms-asf,video/x-flv,video/mp4,application/x-mpegURL,video/MP2T,video/3gpp',
        'required|max:2048'
    ];

    /** owner file type */
    const USER_TYPE = 'user';
    const PRODUCT_TYPE = 'product';
    const SERVICE_TYPE = 'service';
    const STORE_TYPE = 'store';
    const SERVICE_CATEGORY_TYPE = 'service_category';

    /** Owner types */
    const OWNER_TYPE_KEYS = [
        User::class => File::USER_TYPE,
        Product::class => File::PRODUCT_TYPE,
        Service::class => File::SERVICE_TYPE,
        Store::class => File::STORE_TYPE,
        ServiceCategory::class => File::SERVICE_TYPE,
    ];

    /** Owner type right for custmer */
    const CUSTOMER_ONWNER_TYPE_RIGHT = [
        User::class."" => true,
    ];

    /** Owner type right for manager */
    const MANAGER_OWNER_TYPE_RIGHT = [
        Product::class => true,
        Service::class => true,
        ServiceCategory::class => true,
        Store::class => true,
    ];

    /** Owner type right for admin */
    const ADMIN_OWNER_TYPE_RIGHT = [
        User::class => true,
        Product::class => true,
        Service::class => true,
        Store::class => true,
        ServiceCategory::class => true,
    ];

    const OWNER_TYPE_RIGHT = [
        Role::CUSTOMER_ROLE => self::CUSTOMER_ONWNER_TYPE_RIGHT,
        Role::MANAGER_ROLE => self::MANAGER_OWNER_TYPE_RIGHT,
        Role::ADMIN_ROLE => self::ADMIN_OWNER_TYPE_RIGHT,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        self::COL_ID,
        self::COL_PATH,
        self::COL_OWNER_ID,
        self::COL_OWNER_TYPE,
        self::COL_STATUS,
        self::COL_TYPE,
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
            self::COL_ID => 'nullable|numeric',
            self::COL_PATH => 'required',
            self::COL_STATUS => 'nullable|numeric|between:0,1',
            self::COL_TYPE => 'required|numeric|between:0,2',
            self::VAL_INDEX => 'nullable|numeric',
            self::VAL_FILE_ID => 'required|numeric',
        ];
        $errorCode = [
            'required' => ':attribute is required.',
            'numeric' => ':attribute must be a number',
            'between' => ':attribute size must be in range :min - :max',
            'image' => 'file must be an image',
        ];

        return CommonModel::validate($data, $validatedFields, $errorCode);
    }

    public static function getS3FileUrl($lastPartPath)
    {
        return Storage::disk('s3')->url($lastPartPath);
    }

    /**
     * Get the parent imageable model (user or post).
     */
    public function owner()
    {
        return $this->morphTo();
    }
}
