<?php

namespace App\Http\Controllers;

use App\CodeAndMessage\StoreMessage as SM;
use App\Models\Store;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Socket;

class StoreController extends Controller
{
    /** Prefix */
    const PREFIX = 'store';

    /** Api url */
    const API_URL_GET_STORES = '/get-stores';
    const API_URL_GET_STORE = '/get-store/{storeId}';
    const API_URL_CREATE_STORE = '/create';

    /** Method */
    const METHOD_GET_STORES = 'getStores';
    const METHOD_GET_STORE = 'getStore';
    const METHOD_CREATE_STORE = 'createStore';

    /**
     * @functionName: getStores
     * @type:         public
     * @param:        Empty
     * @return:       String(Json)
     */
    public function getStores()
    {
        try {
            $stores = Store::all();

            return self::responseST(SM::GET_STORES_SUCCESS, SM::M_GET_STORES_SUCCESS, $stores);
        } catch (Exception $ex) {
            return self::responseEX(SM::EXW_GET_STORES, $ex->getMessage());
        }
    }

    /**
     * @functionName: getStores
     * @type:         public
     * @param:        Empty
     * @return:       String(Json)
     */
    public function getStore($storeId)
    {
        try {
            $store = Store::find($storeId);
            if (!$store) {
                return self::responseERR(SM::NOT_FOUND_STORE, SM::M_NOT_FOUND_STORE);
            }

            return self::responseST(SM::GET_STORES_SUCCESS, SM::M_GET_STORES_SUCCESS, $store);
        } catch (Exception $ex) {
            return self::responseEX(SM::EXW_GET_STORES, $ex->getMessage());
        }
    }

    /**
     * @functionName: createStore
     * @type:         public
     * @param:        Request
     * @return:       String(Json)
     */
    public function createStore(Request $request)
    {
        if (!$this->isAdmin()) {
            return self::responseERR(self::YOUR_ROLE_CANNOT_CALL_THIS_API, self::M_YOUR_ROLE_CANNOT_CALL_THIS_API);
        }
        try {
            $phone = $request->{Store::COL_PHONE};
            $name = $request->{Store::COL_NAME};
            $address = $request->{Store::COL_ADDRESS};
            $workSchedule = $request->{Store::VAL_WORK_SCHEDULE};

            $validator = Store::validator([
                Store::COL_PHONE => $phone,
                Store::COL_NAME => $name,
                Store::COL_ADDRESS => $address,
                Store::VAL_WORK_SCHEDULE => $workSchedule,
            ]);
            if ($validator->fails()) {
                return self::responseIER($validator->errors())->first();
            }
            if (gettype($workSchedule) != 'array') {
                return self::responseERR(SM::INVALID_WORK_SCHEDULE_FORMAT, SM::M_INVALID_WORK_SCHEDULE_FORMAT);
            }
            foreach ($workSchedule as $day => $time) {
                if (gettype($time) != 'array') {
                    return self::responseERR(SM::INVALID_WORK_SCHEDULE_FORMAT, SM::M_INVALID_WORK_SCHEDULE_FORMAT);
                }
                $validate = Store::validator($time);
                //return $time;
                if ($validate->fails()) {
                    return self::responseIER($validate->errors()->first());
                }
            }
            $dataStore = [
                Store::COL_PHONE => $phone,
                Store::COL_NAME => $name,
                Store::COL_ADDRESS => $address,
                Store::COL_WORK_SCHEDULE => $workSchedule,
                Store::COL_STATUS => 1,
            ];
            if (!Store::create($dataStore)) {
                return self::responseERR(SM::CREATE_STORE_FAILED, SM::M_CREATE_STORE_FAILED);
            }

            return self::responseST(SM::CREATE_STORE_SUCCESS, SM::M_CREATE_STORE_SUCCESS);
        } catch (Exception $ex) {
            return self::responseEX(SM::EXW_CREATE_STORE, $ex->getMessage());
        }
    }
}
