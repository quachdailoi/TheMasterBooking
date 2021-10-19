<?php

namespace App\Http\Controllers;

use App\CodeAndMessage\ServiceCategoryMessage as SCM;
use App\Models\Service;
use App\Models\ServiceCategory;
use Exception;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    /** Prefix */
    const PREFIX = 'service-category';

    /** Api url */
    const API_URL_GET_CATEGORIES = '/get-all';

    /** Method */
    const METHOD_GET_ALL = 'getAll';

    /**
     * @functionName: getAll
     * @type:         public
     * @param:        Empty
     * @return:       String(Json)
     */
    public function getAll()
    {
        try {
            $cates = ServiceCategory::with('allChildren')->whereNull(ServiceCategory::COL_PARENT_ID)->get();
            return self::responseST(SCM::GET_SERVICE_CATEGORIES_SUCCESS, SCM::M_GET_SERVICE_CATEGORIES_SUCCESS, $cates);
        } catch (Exception $ex) {
            return self::responseEX(SCM::EXW_GET_SERVICE_CATEGORIES, $ex->getMessage());
        }
    }
}
