<?php

namespace App\Http\Controllers;

use App\CodeAndMessage\CategoryMessage as CM;
use App\Models\Category;
use App\Models\Store;
use Exception;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /** Prefix */
    const PREFIX = 'category';

    /** Api url */
    const API_URL_GET_CATEGORY_BY_STORE_ID = '/get-categories-by-store-id';

    /** Method */
    const METHOD_GET_CATEGORY_BY_STORE_ID = 'getCategoryByStoreId';

    /**
     * @functionName: register
     * @type:         public
     * @param:        Request $request
     * @return:       String(Json)
     */
    public function getCategoryByStoreId(Request $request)
    {
        try {
            $storeId = $request->{Category::VAL_STORE_ID};

            $validator = Category::validator([
                Category::COL_STORE_ID => $storeId,
            ]);
            if ($validator->fails()) {
                return self::responseIER($validator->errors()->first());
            }
            $store = Store::find($storeId);
            if (!$store) {
                return self::responseERR(CM::NOT_FOUND_STORE, CM::M_NOT_FOUND_STORE);
            }
            $categories = $store->{Store::CATEGORIES};

            return self::responseST(CM::GET_CATEGORIES_SUCCESS, CM::M_GET_CATEGORIES_SUCCESS, $categories);
        } catch (Exception $ex) {
            return self::responseEX(CM::EXW_GET_CATEGORIES, $ex->getMessage());
        }
    }
}
