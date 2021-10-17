<?php

namespace App\CodeAndMessage;

class StoreMessage
{
    // error code
    const NOT_FOUND_STORE = 'ERR400023';
    const INVALID_WORK_SCHEDULE_FORMAT = 'IER400xxx';
    const CREATE_STORE_FAILED = 'ERR400xxx';

    //internal error code
    const EXW_GET_STORES = 'EX500xxx';
    const EXW_CREATE_STORE = 'EX500xxx';

    // error message
    const M_NOT_FOUND_STORE = 'Not found store';
    const M_INVALID_WORK_SCHEDULE_FORMAT = 'Invalid work schedule format.';
    const M_CREATE_STORE_FAILED = 'Create store failed.';

    // success code
    const GET_STORES_SUCCESS = 'ST200xxx';
    const CREATE_STORE_SUCCESS = 'ST200xxx';

    // success message
    const M_GET_STORES_SUCCESS = 'Get store(s) successfully';
    const M_CREATE_STORE_SUCCESS = 'Create store successfully';
}
