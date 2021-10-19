<?php

namespace App\Models;

use App\Traits\SelfReferenceTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends CommonModel
{
    use HasFactory, SelfReferenceTrait;

    protected $table = 'service_categories';

    /** Column of table */
    const COL_NAME = 'name';
    const COL_PARENT_ID = 'parent_id';

    /** value of model */
    const VAL_PARENT_ID = 'parentId';
    const VAL_IMAGES = 'images';

    /** relations */

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        self::COL_NAME,
        self::COL_PARENT_ID,
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
            $images = getenv('DEFAULT_SERVICE_CATEGORY_IMAGE_URL');
        }
        return $images;
    }

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
            self::COL_ID => 'numeric',
            self::COL_NAME => 'required',
            self::VAL_PARENT_ID => 'required|numeric',
        ];
        $errorCode = [
            'required' => ':attribute is required.',
            'numeric' => ':attribute must be a number',
        ];

        return CommonModel::validate($data, $validatedFields, $errorCode);
    }

    /**
     * Get the services
     */
    public function services()
    {
        return $this->hasMany(Service::class, Service::COL_SERVICE_ID, self::COL_ID);
    }

    /**
     * Get the user's file.
     */
    public function files()
    {
        return $this->morphMany(File::class, 'owner');
    }
}
