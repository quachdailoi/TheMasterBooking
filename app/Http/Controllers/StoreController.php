<?php

namespace App\Http\Controllers;

use App\CodeAndMessage\StoreMessage as SM;
use App\Models\File;
use App\Models\Store;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
    const API_URL_GET_CITIES_HAVE_STORE = '/get-cities-have-store';
    const API_URL_GET_STORE_BY_CITY = '/get-store-by-city';

    /** Method */
    const METHOD_GET_STORES = 'getStores';
    const METHOD_GET_STORE = 'getStore';
    const METHOD_CREATE_STORE = 'createStore';
    const METHOD_GET_CITIES_HAVE_STORE = 'getCitiesHaveStore';
    const METHOD_GET_STORE_BY_CITY = 'getStoreByCity';

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
            $city = $request->{Store::COL_CITY};

            $validator = Store::validator([
                Store::COL_PHONE => $phone,
                Store::COL_NAME => $name,
                Store::COL_ADDRESS => $address,
                Store::COL_CITY => $city,
            ]);
            if ($validator->fails()) {
                return self::responseIER($validator->errors())->first();
            }
            $request->validate([File::VAL_FILE => File::FILE_VALIDATIONS[File::IMAGE_TYPE]]);
            $dataStore = [
                Store::COL_PHONE => $phone,
                Store::COL_NAME => $name,
                Store::COL_ADDRESS => $address,
                Store::COL_CITY => $city,
                Store::COL_STATUS => 1,
                Store::COL_WORK_SCHEDULE => []
            ];
            DB::beginTransaction();
            $dataImages = [];
            $maxImages = (int) getenv('MAX_STORE_IMAGE');
            if ($maxImages == 0) {
                $maxImages = 1;
            }

            if (!$store = Store::create($dataStore)) {
                DB::rollBack();
                return self::responseERR(SM::CREATE_STORE_FAILED, SM::M_CREATE_STORE_FAILED);
            }
            for ($i = 0; $i < $maxImages; $i++) {
                $dataImage = [
                    File::COL_OWNER_ID => $store->{Store::COL_ID},
                    File::COL_OWNER_TYPE => Store::class,
                    File::COL_PATH => getenv('DEFAULT_SERVICE_IMAGE_URL'),
                    File::COL_TYPE => File::IMAGE_TYPE,
                    File::COL_CREATED_AT => now()
                ];
                array_push($dataImages, $dataImage);
            }
            if (!File::insert($dataImage)) {
                DB::rollBack();
                return self::responseERR(SM::CREATE_STORE_FAILED, SM::M_CREATE_STORE_FAILED);
            }
            if ($request->has('file')) {
                $fileId = $store->files->first()->{File::COL_ID};
                $request->fileId = $fileId;
                $request->type = File::IMAGE_TYPE;
                $fileController = new FileController();
                $responseSaveFile = $fileController->uploadFileS3($request)->getData();
                if ($responseSaveFile->code != 200) {
                    DB::rollBack();
                    return self::responseERR(SM::CREATE_STORE_FAILED, SM::M_CREATE_STORE_FAILED);
                }
            }
            DB::commit();
            $store = Store::find($store->{Store::COL_ID});
            return self::responseST(SM::CREATE_STORE_SUCCESS, SM::M_CREATE_STORE_SUCCESS, $store);
        } catch (Exception $ex) {
            DB::rollBack();
            return self::responseEX(SM::EXW_CREATE_STORE, $ex->getMessage());
        }
    }

    /**
     * @functionName: getCitiesHaveStore
     * @type:         public
     * @param:        Empty
     * @return:       String(Json)
     */
    public function getCitiesHaveStore()
    {
        try {
            $cities = Store::all()->pluck(Store::COL_CITY)->toArray();
            $cities = array_unique($cities);

            return self::responseST('ST200xxx', 'Get cities have store.', $cities);
        } catch (Exception $ex) {
            return self::responseEX('EX500xxx', $ex->getMessage());
        }
    }

    /**
     * @functionName: getStoreByCity
     * @type:         public
     * @param:        Request
     * @return:       String(Json)
     */
    public function getStoreByCity(Request $request)
    {
        try {
            $city = $request->{Store::COL_CITY};
            $validator = Store::validator([
                Store::COL_CITY => $city,
            ]);
            if ($validator->fails()) {
                return self::responseIER($validator->errors()->first());
            }
            $stores = Store::where(Store::COL_CITY, $city)->get();

            return self::responseST('ST200xxx', 'Get cities have store.', $stores);
        } catch (Exception $ex) {
            return self::responseEX('EX500xxx', $ex->getMessage());
        }
    }
}
