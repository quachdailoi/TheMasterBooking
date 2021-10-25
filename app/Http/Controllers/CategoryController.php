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
    const API_URL_GET_ALL = '/get-all';

    /** Method */
    const METHOD_GET_ALL = 'getAll';

    /**
     * @functionName: register
     * @type:         public
     * @param:        Empty
     * @return:       String(Json)
     */
    public function getAll()
    {
        try {
            $categories = Category::all();

            return self::responseST(CM::GET_CATEGORIES_SUCCESS, CM::M_GET_CATEGORIES_SUCCESS, $categories);
        } catch (Exception $ex) {
            return self::responseEX(CM::EXW_GET_CATEGORIES, $ex->getMessage());
        }
    }
}
