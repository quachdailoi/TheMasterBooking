<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ServiceCategory;
use Exception;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /** Prefix */
    const PREFIX = 'home';

    /** Api url */
    const API_URL_GET_DATA = '/get-data';

    /** Method */
    const METHOD_GET_DATA = 'getData';

    /**
     * @functionName: getData
     * @type:         public
     * @param:        Request
     * @return:       String(Json)
     */
    public function getData(Request $request)
    {
        try {
            $productCategories = Category::with(['products' => function ($query) {
                $query->take(5);
            }])->get();

            $data['productCategories'] = $productCategories;

            $serviceCategories = ServiceCategory::with('allChildren')->get();
            $data['serviceCategories'] = $serviceCategories;

            return self::responseST('', '', $data);
        } catch (Exception $ex) {
            return self::responseEX('', $ex->getMessage());
        }
    }
}
