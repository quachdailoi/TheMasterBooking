<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends CommonModel
{
    use HasFactory;

    protected $table = 'services';

    /** Column of table */
    const COL_NAME = 'name';
    const COL_DESCRIPTION = 'description';
    const COL_PRICE = 'price';
    const COL_CATEGORY_ID = 'category_id';

    /** value of model */
    const VAL_CATEGORY_ID = 'categoryId';
    const VAL_IMAGES = 'images';

    /** relations */

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        self::COL_NAME,
        self::COL_DESCRIPTION,
        self::COL_PRICE,
        self::COL_CATEGORY_ID,
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
            $images = getenv('DEFAULT_SERVICE_IMAGE_URL');
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
            self::COL_DESCRIPTION => 'required',
            self::COL_PRICE => 'required|numeric',
            self::VAL_CATEGORY_ID => 'required|numeric',
        ];
        $errorCode = [
            'required' => ':attribute is required.',
            'numeric' => ':attribute must be a number',
        ];

        return CommonModel::validate($data, $validatedFields, $errorCode);
    }

    /**
     * Get the service category
     */
    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, ServiceCategory::COL_ID, self::COL_CATEGORY_ID);
    }

    /**
     * Get the user's file.
     */
    public function files()
    {
        return $this->morphMany(File::class, 'owner');
    }
}
