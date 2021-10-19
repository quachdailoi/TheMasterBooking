<?php

namespace App\CodeAndMessage;

class ServiceM
{
    // error code
    const NOT_FOUND_SERVICE_CATEGORY = 'ERR400033';

    //internal error code
    const EXW_GET_SERVICE_BY_CATEGORY = 'EX500019';

    // error message
    const M_NOT_FOUND_SERVICE_CATEGORY = 'Not found service category.';

    // success code
    const GET_SERVICE_BY_CATEGORY_SUCCESS = 'ST200020';

    // success message
    const M_GET_SERVICE_BY_CATEGORY_SUCCESS = 'Get services by category id successfully.';
}
