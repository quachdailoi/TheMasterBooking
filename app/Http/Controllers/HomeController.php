<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceCategory;
use Exception;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /** Prefix */
    const PREFIX = 'home';

    /** Api url */
    const API_URL_GET_DATA = '/get-data';
    const API_URL_GET_ALL_CATEGORIES_AND_PRODUCTS = '/get-all-categories-and-products';
    const API_URL_GET_ALL_CATEGORIES_AND_SERVICES = '/get-all-categories-and-services';

    /** Method */
    const METHOD_GET_DATA = 'getData';
    const METHOD_GET_ALL_CATEGORIES_AND_PRODUCTS = 'getAllCategoriesAndProducts';
    const METHOD_GET_ALL_CATEGORIES_AND_SERVICES = 'getAllCategoriesAndServices';

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
                $query->take(10);
            }])->get();

            $data['productCategories'] = $productCategories;

            $serviceCategories = ServiceCategory::with('allChildren')->get();
            $data['serviceCategories'] = $serviceCategories;

            return self::responseST('', '', $data);
        } catch (Exception $ex) {
            return self::responseEX('', $ex->getMessage());
        }
    }

    /**
     * @functionName: getAllCategoriesAndProducts
     * @type:         public
     * @param:        Empty
     * @return:       String(Json)
     */
    public function getAllCategoriesAndProducts()
    {
        try {
            $categories = Category::all();
            $products = Product::all();

            $data = [
                'categories' => $categories,
                'products' => $products,
            ];

            return self::responseST('ST200xxx', 'Get all categories and products successfully.', $data);
        } catch (Exception $ex) {
            return self::responseEX('EX500xxx', $ex->getMessage());
        }
    }

    /**
     * @functionName: getAllCategoriesAndServices
     * @type:         public
     * @param:        Empty
     * @return:       String(Json)
     */
    public function getAllCategoriesAndServices()
    {
        try {
            $categories = ServiceCategory::with('allChildren')->whereNull(ServiceCategory::COL_PARENT_ID)->get();
            $services = Service::all();

            $data = [
                'categories' => $categories,
                'services' => $services,
            ];

            return self::responseST('ST200xxx', 'Get all categories and services successfully.', $data);
        } catch (Exception $ex) {
            return self::responseEX('EX500xxx', $ex->getMessage());
        }
    }
}
