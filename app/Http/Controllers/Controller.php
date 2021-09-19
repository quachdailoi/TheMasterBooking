<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /** key */
    const KEY_DATA = 'data';
    const KEY_MESSAGE = 'message';
    const KEY_CODE = 'code';
    const KEY_DETAIL_CODE = 'detailsCode';
    const KEY_TOKEN = 'token';
    const KEY_TOKEN_EXPIRE_IN = 'tokenExpireIn';
    const KEY_REFRESH_TOKEN = 'refreshToken';
    const KEY_REFRESH_TOKEN_EXPIRE_IN = 'refreshTokenExpireIn';

    const CODE_INVALID_FIELD = 'IER400001';
}
