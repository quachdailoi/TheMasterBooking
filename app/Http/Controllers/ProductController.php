<?php

namespace App\Http\Controllers;

use App\CodeAndMessage\ProductMessage as PM;
use App\Models\Category;
use App\Models\File;
use App\Models\Product;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /** Prefix */
    const PREFIX = 'product';

    /** Api url */
    const API_URL_GET_PRODUCTS = '/get-products';
    const API_URL_ADD_TO_CART = '/add-to-cart/{productId}';
    const API_URL_REMOVE_FROM_CART = '/remove-from-cart/{productId}';
    const API_URL_CREATE_PRODUCT = '/create-product';
    const API_URL_UPDATE_PRODUCT = '/update-product/{productId}';
    const API_URL_DELETE_PRODUCT = '/delete-product/{productId}';
    const API_URL_GET_ALL = '/get-all';

    /** Method */
    const METHOD_GET_PRODUCTS = 'getProducts';
    const METHOD_ADD_TO_CART = 'addToCart';
    const METHOD_REMOVE_FROM_CART = 'removeFromCart';
    const METHOD_CREATE_PRODUCT = 'createProduct';
    const METHOD_UPDATE_PRODUCT = 'updateProduct';
    const METHOD_DELETE_PRODUCT = 'deleteProduct';
    const METHOD_GET_ALL = 'getAll';

    /**
     * @functionName: getProducts
     * @type:         public
     * @param:        Request $request
     * @return:       String(Json)
     */
    public function getProducts(Request $request)
    {
        try {
            $categoryId = $request->{Product::VAL_CATEGORY_ID};
            $itemPerPage = $request->input(Product::VAL_ITEM_PER_PAGE, Product::ITEM_PER_PAGE_DEFAULT);
            $page = $request->input(Product::VAL_PAGE, Product::PAGE_DEFAULT);
            $searchValue = $request->input(Product::VAL_SEARCH_VALUE, "");
            $sortBy = $request->input(Product::VAL_SORT_BY, Product::COL_ID);
            $sortOrder = $request->input(Product::VAL_SORT_ORDER, Product::ASC_ORDER);

            $validator = Product::validator([
                Product::COL_CATEGORY_ID => $categoryId,
                Product::VAL_ITEM_PER_PAGE => $itemPerPage,
                Product::VAL_PAGE => $page,
            ]);
            if ($validator->fails()) {
                return self::responseIER($validator->errors()->first());
            }

            $query = Product::query();
            if ($categoryId and !Category::find($categoryId)) {
                $query = $query->where(Product::COL_CATEGORY_ID, $categoryId);
            }
            $searchValue = strtolower($searchValue);
            $query = $query->where(DB::raw("LOWER(".Product::COL_NAME.")"), "like", "%$searchValue%");
            $copyQuery = $query;
            $count = $query->count();
            $maxPages = ceil($count/$itemPerPage);
            if ($page < 1) {
                $page = 1;
            }
            if ($page > $maxPages) {
                $page = $maxPages;
            }
            $skip = ($page - 1) * $itemPerPage;

            $data = $copyQuery->orderBy($sortBy, $sortOrder)
                ->skip($skip)->take($itemPerPage)->get();
            $dataResponse = [
                'maxOfPage' => $maxPages,
                'products' => $data,
            ];

            return self::responseST(PM::GET_PRODUCTS_SUCCESS, PM::M_GET_PRODUCTS_SUCCESS, $dataResponse);
        } catch (Exception $ex) {
            return self::responseEX(PM::EXW_GET_PRODUCTS, $ex->getMessage());
        }
    }

    /**
     * @functionName: addToCart
     * @type:         public
     * @param:        Empty
     * @return:       String(Json)
     */
    public function addToCart($productId)
    {
        try {
            $product = Product::find($productId);
            if (!$product) {
                return self::responseERR(PM::NOT_FOUND_PRODUCT, PM::M_NOT_FOUND_PRODUCT);
            }
            $currentUser = Auth::user();
            $currentUserCart = $currentUser->{User::COL_CART};
            $currentUserCart[$productId] = ($currentUserCart[$productId] ?? 0) + 1;
            $currentUser->{User::COL_CART} = $currentUserCart;
            if (!$currentUser->save()) {
                return self::responseERR(PM::ADD_TO_CART_FAILED, PM::M_ADD_TO_CART_FAILED);
            }

            return self::responseST(PM::ADD_TO_CART_SUCCESS, PM::M_ADD_TO_CART_SUCCESS, $currentUserCart);
        } catch (Exception $ex) {
            return self::responseEX(PM::EXW_ADD_TO_CART, $ex->getMessage());
        }
    }

    /**
     * @functionName: addToCart
     * @type:         public
     * @param:        Empty
     * @return:       String(Json)
     */
    public function removeFromCart($productId)
    {
        try {
            $product = Product::find($productId);
            if (!$product) {
                return self::responseERR(PM::NOT_FOUND_PRODUCT, PM::M_NOT_FOUND_PRODUCT);
            }
            $currentUser = Auth::user();
            $currentUserCart = $currentUser->{User::COL_CART};
            unset($currentUserCart[$productId]);
            if (empty($currentUserCart)) {
                $currentUserCart = null;
            }
            $currentUser->{User::COL_CART} = $currentUserCart;
            if (!$currentUser->save()) {
                return self::responseERR(PM::ADD_TO_CART_FAILED, PM::M_ADD_TO_CART_FAILED);
            }

            return self::responseST(PM::ADD_TO_CART_SUCCESS, PM::M_ADD_TO_CART_SUCCESS, $currentUserCart);
        } catch (Exception $ex) {
            return self::responseEX(PM::EXW_ADD_TO_CART, $ex->getMessage());
        }
    }

    /**
     * @functionName: createProduct
     * @type:         public
     * @param:        Rquest
     * @return:       String(Json)
     */
    public function createProduct(Request $request)
    {
        if (!$this->isAdmin() and !$this->isManager()) {
            return self::responseERR(self::YOUR_ROLE_CANNOT_CALL_THIS_API, self::M_YOUR_ROLE_CANNOT_CALL_THIS_API);
        }
        try {
            $name = $request->{Product::COL_NAME};
            $quantity = $request->{Product::COL_QUANTITY};
            $price = $request->{Product::COL_PRICE};
            $description = $request->{Product::COL_DESCRIPTION};
            $categoryId = $request->{Product::VAL_CATEGORY_ID};

            $validator = Product::validator([
                Product::COL_NAME => $name,
                Product::COL_QUANTITY => $quantity,
                Product::COL_PRICE => $price,
                Product::COL_DESCRIPTION => $description,
                Product::COL_CATEGORY_ID => $categoryId,
            ]);
            if ($validator->fails()) {
                return self::responseIER($validator->errors()->first());
            }
            $request->validate([File::VAL_FILE => File::FILE_VALIDATIONS[File::IMAGE_TYPE]]);
            if (!Category::find($categoryId)) {
                return self::responseERR(PM::NOT_FOUND_CATEGORY, PM::M_NOT_FOUND_CATEGORY);
            }
            $dataCreate = [
                Product::COL_NAME => $name,
                Product::COL_QUANTITY => $quantity,
                Product::COL_PRICE => $price,
                Product::COL_DESCRIPTION => $description,
                Product::COL_CATEGORY_ID => $categoryId,
            ];
            DB::beginTransaction();
            $dataImages = [];
            $maxImages = (int) getenv('MAX_PRODUCT_IMAGE');
            if ($maxImages == 0) {
                $maxImages = 1;
            }
            $product = Product::create($dataCreate);
            if (!$product) {
                return self::responseERR(PM::CREATE_PRODUCT_FAILED, PM::M_CREATE_PRODUCT_FAILED);
            }
            for ($i = 0; $i < $maxImages; $i++) {
                $dataImage = [
                    File::COL_OWNER_ID => $product->{Product::COL_ID},
                    File::COL_OWNER_TYPE => Product::class,
                    File::COL_PATH => getenv('DEFAULT_PRODUCT_IMAGE_URL'),
                    File::COL_TYPE => File::IMAGE_TYPE,
                    File::COL_CREATED_AT => now()
                ];
                array_push($dataImages, $dataImage);
            }
            if (!File::insert($dataImage)) {
                DB::rollBack();
                return self::responseERR(PM::CREATE_PRODUCT_FAILED, PM::M_CREATE_PRODUCT_FAILED);
            }
            if ($request->has('file')) {
                $fileId = $product->files->first()->{File::COL_ID};
                $request->fileId = $fileId;
                $request->type = File::IMAGE_TYPE;
                $fileController = new FileController();
                $responseSaveFile = $fileController->uploadFileS3($request)->getData();
                if ($responseSaveFile->code != 200) {
                    DB::rollBack();
                    return self::responseERR(PM::CREATE_PRODUCT_FAILED, PM::M_CREATE_PRODUCT_FAILED);
                }
            }
            DB::commit();
            $product = Product::find($product->{Product::COL_ID});
            return self::responseST(PM::CREATE_PRODUCT_SUCCESS, PM::M_CREATE_PRODUCT_SUCCESS, $product);
        } catch (Exception $ex) {
            DB::rollBack();
            return self::responseEX(PM::EXW_CREAT_PRODUCT, $ex->getMessage());
        }
    }

    /**
     * @functionName: updateProduct
     * @type:         public
     * @param:        Request
     * @return:       String(Json)
     */
    public function updateProduct(Request $request, $productId)
    {
        if (!$this->isAdmin() and !$this->isManager()) {
            return self::responseERR(self::YOUR_ROLE_CANNOT_CALL_THIS_API, self::M_YOUR_ROLE_CANNOT_CALL_THIS_API);
        }
        try {
            $name = $request->{Product::COL_NAME};
            $quantity = $request->{Product::COL_QUANTITY};
            $price = $request->{Product::COL_PRICE};
            $description = $request->{Product::COL_DESCRIPTION};
            $categoryId = $request->{Product::VAL_CATEGORY_ID};
            $status = $request->{Product::COL_STATUS};

            $validator = Product::validator([
                Product::COL_NAME => $name,
                Product::COL_QUANTITY => $quantity,
                Product::COL_PRICE => $price,
                Product::COL_DESCRIPTION => $description,
                Product::COL_CATEGORY_ID => $categoryId,
            ]);
            if ($validator->fails()) {
                return self::responseIER($validator->errors()->first());
            }
            $request->validate([File::VAL_FILE => File::FILE_VALIDATIONS[File::IMAGE_TYPE]]);
            if (!Category::find($categoryId)) {
                return self::responseERR(PM::NOT_FOUND_CATEGORY, PM::M_NOT_FOUND_CATEGORY);
            }
            $product = Product::find($productId);
            if (!$product) {
                return self::responseERR(PM::NOT_FOUND_PRODUCT, PM::M_NOT_FOUND_PRODUCT);
            }
            DB::beginTransaction();
            $product->{Product::COL_NAME} = $name;
            $product->{Product::COL_QUANTITY} = $quantity;
            $product->{Product::COL_PRICE} = $price;
            $product->{Product::COL_DESCRIPTION} = $description;
            $product->{Product::COL_CATEGORY_ID} = $categoryId;
            $product->{Product::COL_STATUS} = $status;
            $rsSave = $product->save();
            if (!$rsSave) {
                DB::rollBack();
                return self::responseERR(PM::UPDATE_PRODUCT_FAILED, PM::M_UPDATE_PRODUCT_FAILED);
            }
            if ($request->has('file')) {
                $fileId = $product->files->first()->{File::COL_ID};
                $request->fileId = $fileId;
                $request->type = File::IMAGE_TYPE;
                $fileController = new FileController();
                $responseSaveFile = $fileController->uploadFileS3($request)->getData();
                if ($responseSaveFile->code != 200) {
                    DB::rollBack();
                    return self::responseERR(PM::UPDATE_PRODUCT_FAILED, PM::M_UPDATE_PRODUCT_FAILED);
                }
            }
            DB::commit();
            $product = Product::find($product->{Product::COL_ID});
            return self::responseST(PM::UPDATE_PRODUCT_SUCCESS, PM::M_UPDATE_PRODUCT_SUCCESS, $product);
        } catch (Exception $ex) {
            DB::rollBack();
            return self::responseEX(PM::EXW_UPDATE_PRODUCT, $ex->getMessage());
        }
    }

    /**
     * @functionName: deleteProduct
     * @type:         public
     * @param:        Request
     * @return:       String(Json)
     */
    public function deleteProduct(int $productId)
    {
        if (!$this->isAdmin() and !$this->isManager()) {
            return self::responseERR(self::YOUR_ROLE_CANNOT_CALL_THIS_API, self::M_YOUR_ROLE_CANNOT_CALL_THIS_API);
        }
        try {
            $validator = Product::validator([
                Product::COL_ID => $productId,
            ]);
            if ($validator->fails()) {
                return self::responseIER($validator->errors()->first());
            }
            $product = Product::find($productId);
            if (!$product) {
                return self::responseERR(PM::NOT_FOUND_PRODUCT, PM::M_NOT_FOUND_PRODUCT);
            }
            DB::beginTransaction();
            if (!$product->files()->delete() or !$product->delete()) {
                DB::rollBack();
                return self::responseERR(PM::DELETE_PRODUCT_FAILED, PM::M_DELETE_PRODUCT_FAILED);
            }
            DB::commit();
            return self::responseST(PM::DELETE_PRODUCT_SUCCESS, PM::M_DELETE_PRODUCT_SUCCESS);
        } catch (Exception $ex) {
            DB::rollBack();
            return self::responseEX(PM::EXW_DELETE_PRODUCT, $ex->getMessage());
        }
    }

    /**
     * @functionName: getAll
     * @type:         public
     * @param:        Request
     * @return:       String(Json)
     */
    public function getAll()
    {
        try {
            $products = Product::all();
            return self::responseST(PM::GET_PRODUCTS_SUCCESS, PM::M_GET_PRODUCTS_SUCCESS, $products);
        } catch (Exception $ex) {
            return self::responseEX(PM::EXW_GET_PRODUCTS, $ex->getMessage());
        }
    }
}
