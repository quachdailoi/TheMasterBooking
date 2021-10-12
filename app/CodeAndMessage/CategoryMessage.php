<?php

namespace App\CodeAndMessage;

class CategoryMessage
{
    // error code
    const NOT_FOUND_STORE = 'ERR400xxx';

    //internal error code
    const EXW_GET_CATEGORIES = 'EX500xxx';

    // error message
    const M_NOT_FOUND_STORE = 'Not found store';


    // success code
    const GET_CATEGORIES_SUCCESS = 'ST200xxx';

    // success message
    const M_GET_CATEGORIES_SUCCESS = 'Get categories successfully';
}
