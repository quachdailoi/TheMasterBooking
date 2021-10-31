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
    const API_URL_CREATE_CATEGORY = '/create';
    const API_URL_UPDATE_CATEGORY = '/update/{categoryId}';

    /** Method */
    const METHOD_GET_ALL = 'getAll';
    const METHOD_CREATE = 'create';
    const METHOD_UPDATE = 'update';

    /**
     * @functionName: getAll
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

    /**
     * @functionName: create
     * @type:         public
     * @param:        Request
     * @return:       String(Json)
     */
    public function create(Request $request)
    {
        try {
            $name = $request->{Category::COL_NAME};
            $validator = Category::validator([
                Category::COL_NAME => $name,
            ]);
            if ($validator->fails()) {
                return self::responseIER($validator->errors()->first());
            }
            $dataCreate = [
                Category::COL_NAME => $name,
            ];
            if (!$category = Category::create($dataCreate)) {
                return self::responseERR('ERR400xxx', 'Create category for product failed.');
            }
            return self::responseST('ST200xxx', 'Create category for product successfully.', $category);
        } catch (Exception $ex) {
            return self::responseEX('EX500xxx', $ex->getMessage());
        }
    }

    /**
     * @functionName: update
     * @type:         public
     * @param:        Request $request, int $categoryId
     * @return:       String(Json)
     */
    public function update(Request $request, int $categoryId)
    {
        try {
            $category = Category::find($categoryId);
            if (!$category) {
                return self::responseERR('ERR400xxx', 'Update product category failed.');
            }
            $name = $request->{Category::COL_NAME};
            $validator = Category::validator([
                Category::COL_NAME => $name,
            ]);
            if ($validator->fails()) {
                return self::responseIER($validator->errors()->first());
            }
            $category->{Category::COL_NAME} = $name;
            if (!$category->save()) {
                return self::responseERR('ERR400xxx', 'Update product category failed.');
            }
            return self::responseST('ST200xxx', 'Update product category successfully.', $category);
        } catch (Exception $ex) {
            return self::responseEX('EX500xxx', $ex->getMessage());
        }
    }
}
