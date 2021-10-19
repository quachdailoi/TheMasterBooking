<?php

namespace App\Http\Controllers;

use App\CodeAndMessage\ServiceM;
use App\Models\Category;
use App\Models\Service;
use App\Models\ServiceCategory;
use Exception;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /** Prefix */
    const PREFIX = 'service';

    /** Api url */
    const API_URL_GET_SERVICES = '/get-services-by-category';

    /** Method */
    const METHOD_GET_SERVICES = 'getServicesByCategory';


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
}
