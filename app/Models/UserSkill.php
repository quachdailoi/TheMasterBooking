<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSkill extends CommonModel
{
    use HasFactory;

    protected $table = 'user_skills';

    /** Column of table */
    const COL_USER_ID = 'user_id';
    const COL_SKILL_ID = 'skill_id';

    //value of model
    const VAL_USER_ID = 'userId';
    const VAL_SKILL_ID = 'skillId';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        self::COL_SKILL_ID,
        self::COL_USER_ID,
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
            self::VAL_USER_ID => 'required|numeric',
            self::VAL_SKILL_ID => 'required|numeric',
        ];
        $errorCode = [
            'required' => ':attribute is required.',
            'numeric' => ':attribute must be a number',
        ];

        return CommonModel::validate($data, $validatedFields, $errorCode);
    }

    public function skill()
    {
        return $this->hasOne(Skill::class, Skill::COL_ID, self::COL_SKILL_ID);
    }
}
