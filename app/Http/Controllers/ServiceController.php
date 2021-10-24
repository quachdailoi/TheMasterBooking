<?php

namespace App\Http\Controllers;

use App\CodeAndMessage\ServiceM;
use App\Models\Category;
use App\Models\File;
use App\Models\Service;
use App\Models\ServiceCategory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    /** Prefix */
    const PREFIX = 'service';

    /** Api url */
    const API_URL_GET_SERVICES = '/get-services-by-category';
    const API_URL_CREATE_SERVICE = '/create-service';

    /** Method */
    const METHOD_GET_SERVICES = 'getServicesByCategory';
    const METHOD_CREATE_SERVICE = 'createService';


    /**
     * @functionName: getServicesByCategory
     * @type:         public
     * @param:        Empty
     * @return:       String(Json)
     */
    public function getServicesByCategory(Request $request)
    {
        try {
            $categoryId = $request->{Service::VAL_CATEGORY_ID};
            $validator = Service::validator([
                Service::VAL_CATEGORY_ID => $categoryId,
            ]);
            if ($validator->fails()) {
                return self::responseIER($validator->errors())->first();
            }
            $categoryS = ServiceCategory::find($categoryId);
            if (!$categoryS) {
                return self::responseERR(ServiceM::NOT_FOUND_SERVICE_CATEGORY, ServiceM::M_NOT_FOUND_SERVICE_CATEGORY);
            }
            $services = Service::where(Service::COL_CATEGORY_ID, $categoryId)->get();

            return self::responseST(ServiceM::GET_SERVICE_BY_CATEGORY_SUCCESS, ServiceM::M_GET_SERVICE_BY_CATEGORY_SUCCESS, $services);
        } catch (Exception $ex) {
            return self::responseEX(ServiceM::EXW_GET_SERVICE_BY_CATEGORY, $ex->getMessage());
        }
    }

    /**
     * @functionName: createServicesByCategory
     * @type:         public
     * @param:        Empty
     * @return:       String(Json)
     */
    public function createService(Request $request)
    {
        if (!$this->isAdmin() and !$this->isManager()) {
            return self::responseERR(self::YOUR_ROLE_CANNOT_CALL_THIS_API, self::M_YOUR_ROLE_CANNOT_CALL_THIS_API);
        }
        try {
            $data = [
                Service::VAL_CATEGORY_ID => $request->{Service::VAL_CATEGORY_ID},
                Service::COL_NAME => $request->{Service::COL_NAME},
                Service::COL_DESCRIPTION => $request->{Service::COL_DESCRIPTION},
                Service::COL_PRICE => $request->{Service::COL_PRICE},
            ];
            $validator = Service::validator($data);
            if ($validator->fails()) {
                return self::responseIER($validator->errors())->first();
            }
            ServiceCategory::isExist($data[Service::VAL_CATEGORY_ID]);
            DB::beginTransaction();
            $dataImages = [];
            $maxImages = (int) getenv('MAX_PRODUCT_IMAGE');
            if ($maxImages == 0) {
                $maxImages = 1;
            }
            $dataCreate = [
                Service::COL_CATEGORY_ID => $request->{Service::VAL_CATEGORY_ID},
                Service::COL_NAME => $request->{Service::COL_NAME},
                Service::COL_DESCRIPTION => $request->{Service::COL_DESCRIPTION},
                Service::COL_PRICE => $request->{Service::COL_PRICE},
            ];
            if (!$service = Service::create($dataCreate)) {
                return self::responseERR('ERR400xxx', 'Create service failed.');
            }
            for ($i = 0; $i < $maxImages; $i++) {
                $dataImage = [
                    File::COL_OWNER_ID => $service->{Service::COL_ID},
                    File::COL_OWNER_TYPE => Store::class,
                    File::COL_PATH => getenv('DEFAULT_PRODUCT_IMAGE_URL'),
                    File::COL_TYPE => File::IMAGE_TYPE,
                    File::COL_CREATED_AT => now()
                ];
                array_push($dataImages, $dataImage);
            }
            if (!File::insert($dataImage)) {
                DB::rollBack();
                return self::responseERR('ERR400xxx', 'Create service failed.');
            }
            DB::commit();

            return self::responseST('ST200xxx', 'Create service success.', $service);
        } catch (Exception $ex) {
            DB::rollBack();
            return self::responseEX('EX500xxx', $ex->getMessage());
        }
    }
}
