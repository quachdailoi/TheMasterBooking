<?php

namespace App\Http\Controllers;

use App\CodeAndMessage\ServiceCategoryMessage as SCM;
use App\Models\File;
use App\Models\Service;
use App\Models\ServiceCategory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceCategoryController extends Controller
{
    /** Prefix */
    const PREFIX = 'service-category';

    /** Api url */
    const API_URL_GET_CATEGORIES = '/get-all';
    const API_URL_CREATE = '/create';

    /** Method */
    const METHOD_GET_ALL = 'getAll';
    const METHOD_CREATE= 'create';

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

    /**
     * @functionName: createServiceCategory
     * @type:         public
     * @param:        Request
     * @return:       String(Json)
     */
    public function create(Request $request)
    {
        if (!$this->isAdmin() and !$this->isManager()) {
            return self::responseERR(self::YOUR_ROLE_CANNOT_CALL_THIS_API, self::M_YOUR_ROLE_CANNOT_CALL_THIS_API);
        }
        try {
            $data = [
                ServiceCategory::COL_NAME => $request->{ServiceCategory::COL_NAME},
                ServiceCategory::VAL_PARENT_ID => $request->{ServiceCategory::VAL_PARENT_ID},
            ];
            $validator = ServiceCategory::validator($data);
            if ($validator->fails()) {
                return self::responseIER($validator->errors()->first());
            }
            $request->validate([File::VAL_FILE => File::FILE_VALIDATIONS[File::IMAGE_TYPE]]);
            $checkExist = ServiceCategory::isExist($data[ServiceCategory::VAL_PARENT_ID]);
            if (!$checkExist) {
                return $checkExist;
            }
            DB::beginTransaction();
            $dataImages = [];
            $maxImages = (int) getenv('MAX_PRODUCT_IMAGE');
            if ($maxImages == 0) {
                $maxImages = 1;
            }
            $dataCreate = [
                ServiceCategory::COL_NAME => $request->{ServiceCategory::COL_NAME},
                ServiceCategory::COL_PARENT_ID => $request->{ServiceCategory::VAL_PARENT_ID},
            ];
            if (!$serviceCate = ServiceCategory::create($dataCreate)) {
                return self::responseERR('ERR400xxx', 'Create service category failed.');
            }
            for ($i = 0; $i < $maxImages; $i++) {
                $dataImage = [
                    File::COL_OWNER_ID => $serviceCate->{ServiceCategory::COL_ID},
                    File::COL_OWNER_TYPE => ServiceCategory::class,
                    File::COL_PATH => getenv('DEFAULT_SERVICE_CATEGORY_IMAGE_URL'),
                    File::COL_TYPE => File::IMAGE_TYPE,
                    File::COL_CREATED_AT => now()
                ];
                array_push($dataImages, $dataImage);
            }
            if (!File::insert($dataImage)) {
                DB::rollBack();
                return self::responseERR('ERR400xxx', 'Create service category failed.');
            }
            if ($request->has('file')) {
                $fileId = $serviceCate->files->first()->{File::COL_ID};
                $request->fileId = $fileId;
                $request->type = File::IMAGE_TYPE;
                $fileController = new FileController();
                $responseSaveFile = $fileController->uploadFileS3($request)->getData();
                if ($responseSaveFile->code != 200) {
                    DB::rollBack();
                    return self::responseERR('ERR400xxx', 'Create service failed.');
                }
            }
            DB::commit();
            $serviceCate = ServiceCategory::find($serviceCate->{Service::COL_ID});
            return self::responseST('ERR400xxx', 'Create service category successfully.', $serviceCate);
        } catch (Exception $ex) {
            return self::responseEX('EX500xxx', $ex->getMessage());
        }
    }
}
