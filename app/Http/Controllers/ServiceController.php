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
    const API_URL_CREATE_SERVICE = '/create';
    const API_URL_GET_ALL_SERVICES_WITH_CATEGORY = '/get-all-servicies-with-category';
    const API_URL_UPDATE_SERVICE = '/update-service/{serviceId}';
    const API_URL_DELETE_SERVICE = '/delete-service/{serviceId}';

    /** Method */
    const METHOD_GET_SERVICES = 'getServicesByCategory';
    const METHOD_CREATE_SERVICE = 'createService';
    const METHOD_GET_ALL_SERVICES_WITH_CATEGORY = 'getAllServicesWithCategory';
    const METHOD_UPDATE_SERVICE = 'updateService';
    const METHOD_DELETE_SERVICE = 'deleteService';

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
            $request->validate([File::VAL_FILE => File::FILE_VALIDATIONS[File::IMAGE_TYPE]]);
            $checkExist = ServiceCategory::isExist($data[Service::VAL_CATEGORY_ID]);
            if (!$checkExist) {
                return $checkExist;
            }
            DB::beginTransaction();
            $dataImages = [];
            $maxImages = (int) getenv('MAX_SERVICE_IMAGE');
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
                    File::COL_OWNER_TYPE => Service::class,
                    File::COL_PATH => getenv('DEFAULT_SERVICE_IMAGE_URL'),
                    File::COL_TYPE => File::IMAGE_TYPE,
                    File::COL_CREATED_AT => now()
                ];
                array_push($dataImages, $dataImage);
            }
            if (!File::insert($dataImage)) {
                DB::rollBack();
                return self::responseERR('ERR400xxx', 'Create service failed.');
            }
            if ($request->has('file')) {
                $fileId = $service->files->first()->{File::COL_ID};
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
            $service = Service::find($service->{Service::COL_ID});

            return self::responseST('ST200xxx', 'Create service success.', $service);
        } catch (Exception $ex) {
            DB::rollBack();
            return self::responseEX('EX500xxx', $ex->getMessage());
        }
    }

    /**
     * @functionName: getAllServicesWithCategory
     * @type:         public
     * @param:        Empty
     * @return:       String(Json)
     */
    public function getAllServicesWithCategory()
    {
        try {
            $services = ServiceCategory::with('allChildren.services')->whereNull(ServiceCategory::COL_PARENT_ID)->get();

            return self::responseST(ServiceM::GET_SERVICE_BY_CATEGORY_SUCCESS, ServiceM::M_GET_SERVICE_BY_CATEGORY_SUCCESS, $services);
        } catch (Exception $ex) {
            return self::responseEX(ServiceM::EXW_GET_SERVICE_BY_CATEGORY, $ex->getMessage());
        }
    }

    /**
     * @functionName: updateService
     * @type:         public
     * @param:        Request, int $serviceId
     * @return:       String(Json)
     */
    public function updateService(Request $request, $serviceId)
    {
        if (!$this->isAdmin() and !$this->isManager()) {
            return self::responseERR(self::YOUR_ROLE_CANNOT_CALL_THIS_API, self::M_YOUR_ROLE_CANNOT_CALL_THIS_API);
        }
        try {
            $name = $request->{Service::COL_NAME};
            $description = $request->{Service::COL_DESCRIPTION};
            $price = $request->{Service::COL_PRICE};
            $categoryId = $request->{Service::VAL_CATEGORY_ID};

            $validator = Service::validator([
                Service::COL_NAME => $name,
                Service::COL_PRICE => $price,
                Service::COL_DESCRIPTION => $description,
                Service::VAL_CATEGORY_ID => $categoryId,
            ]);
            if ($validator->fails()) {
                return self::responseIER($validator->errors()->first());
            }
            $request->validate([File::VAL_FILE => File::FILE_VALIDATIONS[File::IMAGE_TYPE]]);
            if (!Category::find($categoryId)) {
                return self::responseERR(ServiceM::NOT_FOUND_SERVICE_CATEGORY, ServiceM::M_NOT_FOUND_SERVICE_CATEGORY);
            }
            $service = Service::find($serviceId);
            if (!$service) {
                return self::responseERR('ERR400xxx', 'Not found service.');
            }
            DB::beginTransaction();
            $service->{Service::COL_NAME} = $name;
            $service->{Service::COL_PRICE} = $price;
            $service->{Service::COL_DESCRIPTION} = $description;
            $service->{Service::COL_CATEGORY_ID} = $categoryId;
            $rsSave = $service->save();
            if (!$rsSave) {
                DB::rollBack();
                return self::responseERR('ERR400xxx', 'Update service failed.');
            }
            if ($request->has('file')) {
                $fileId = $service->files->first()->{File::COL_ID};
                $request->fileId = $fileId;
                $request->type = File::IMAGE_TYPE;
                $fileController = new FileController();
                $responseSaveFile = $fileController->uploadFileS3($request)->getData();
                if ($responseSaveFile->code != 200) {
                    DB::rollBack();
                    return self::responseERR('ERR400xxx', 'Update service failed.');
                }
            }
            DB::commit();
            $service = Service::find($service->{Service::COL_ID});
            return self::responseST('ST200xxx', 'Update service successfully.', $service);
        } catch (Exception $ex) {
            DB::rollBack();
            return self::responseEX('EX500xxx', $ex->getMessage());
        }
    }

    /**
     * @functionName: deleteService
     * @type:         public
     * @param:        int $serviceId
     * @return:       String(Json)
     */
    public function deleteService(int $serviceId)
    {
        if (!$this->isAdmin() and !$this->isManager()) {
            return self::responseERR(self::YOUR_ROLE_CANNOT_CALL_THIS_API, self::M_YOUR_ROLE_CANNOT_CALL_THIS_API);
        }
        try {
            $validator = Service::validator([
                Service::COL_ID => $serviceId,
            ]);
            if ($validator->fails()) {
                return self::responseIER($validator->errors()->first());
            }
            $service = Service::find($serviceId);
            if (!$service) {
                return self::responseERR('ERR400xxx', 'Not found service.');
            }
            DB::beginTransaction();
            if (!$service->files()->delete() or !$service->delete()) {
                DB::rollBack();
                return self::responseERR('ERR400xxx', 'Delete service failed.');
            }
            DB::commit();
            return self::responseST('ST200xxx', 'Delete service successfully.');
        } catch (Exception $ex) {
            DB::rollBack();
            return self::responseEX('EX500xxx', $ex->getMessage());
        }
    }
}
