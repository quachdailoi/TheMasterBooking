<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;

class CommonModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    /** Common column */
    const COL_ID = 'id';
    const COL_CREATED_AT = 'created_at';
    const COL_UPDATED_AT = 'updated_at';
    const COL_DELETED_AT = 'deleted_at';

    const INVALID_FIELD_CODE = 'INVALID_FIELD';
    const COMMON_MESSAGE_PATH = 'messages/common.';

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
    public static function validate(array $data, array $rule, array $message)
    {
        $filterRules = [];
        foreach (array_keys($data) as $keyField) {
            if (($rule[$keyField] ?? false)) {
                $filterRules[$keyField] = $rule[$keyField];
            }
        }
        return Validator::make($data, $filterRules, $message);
    }
}
