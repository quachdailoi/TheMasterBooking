<?php

namespace App\CodeAndMessage;

class CategoryMessage
{
    // error code
    const NOT_FOUND_STORE = 'ERR400023';

    //internal error code
    const EXW_GET_CATEGORIES = 'EX500011';

    // error message
    const M_NOT_FOUND_STORE = 'Not found store';


    // success code
    const GET_CATEGORIES_SUCCESS = 'ST200011';

    // success message
    const M_GET_CATEGORIES_SUCCESS = 'Get categories successfully';
}
