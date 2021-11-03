<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\Role;
use App\Models\ServiceOrder;
use App\Models\Shift;
use App\Models\Skill;
use App\Models\User;
use App\Models\UserShift;
use App\Models\UserSkill;
use App\Models\VerifiedCode;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ManagerController extends Controller
{
    /** Prefix */
    const PREFIX = 'manager';

    /** Api url */
    const API_URL_CREATE_STAFF = 'staff/create';
    const API_URL_UPDATE_STAFF = 'staff/update/{staffId}';
    const API_URL_DELETE_STAFF = 'staff/delete/{staffId}';
    const API_URL_GET_ALL_STAFFS = 'staff/get-all';
    const API_URL_GET_STAFF_BY_ID = 'staff/get/{staffId}';

    const API_URL_CREATE_SHIFT = 'shift/create';
    const API_URL_UPDATE_SHIFT = 'shift/update/{shiftId}';
    const API_URL_DELETE_SHIFT = 'shift/delete/{shiftId}';
    const API_URL_GET_ALL_SHIFTS = 'shift/get-all';

    const API_URL_GET_SERVICE_ORDER = 'service-order/get';
    const API_URL_CONFIRM_SERVICE_ORDER = 'service-order/confirm/{orderId}';
    const API_URL_CANCEL_SERVICE_ORDER = 'service-order/cancel/{orderId}';

    const API_URL_GET_PRODUCT_ORDER = 'product-order/get';
    const API_URL_CANCEL_PRODUCT_ORDER = 'product-order/cancel/{orderId}';
    const API_URL_CONFIRM_PRODUCT_ORDER = 'product-order/confirm/{orderId}';

    const API_URL_GET_ALL_SKILLS = 'skill/get-all';

    /** Method */
    const METHOD_CREATE_STAFF = 'createStaff';
    const METHOD_UPDATE_STAFF = 'updateStaff';
    const METHOD_DELETE_STAFF = 'deleteStaff';
    const METHOD_GET_ALL_STAFFS = 'getAllStaffs';
    const METHOD_GET_STAFF_BY_ID = 'getStaffById';

    const METHOD_CREATE_SHIFT = 'createShift';
    const METHOD_UPDATE_SHIFT = 'updateShift';
    const METHOD_DELETE_SHIFT = 'deleteShift';
    const METHOD_GET_ALL_SHIFTS = 'getAllShifts';

    const METHOD_FILTER_SERVICE_ORDER = 'filterServiceOrder';
    const METHOD_CONFIRM_SERVICE_ORDER = 'comfirmServiceOrder';
    const METHOD_CANCEL_SERVICE_ORDER = 'cancelServiceOrder';

    const METHOD_FILTER_PRODUCT_ORDER = 'filterProductOrder';
    const METHOD_CANCEL_PRODUCT_ORDER = 'cancelProductOrder';
    const METHOD_CONFIRM_PRODUCT_ORDER = 'confirmProductOrder';

    const METHOD_GET_ALL_SKILLS = 'getAllSkills';

    /**
     * @functionName: createStaff
     * @type:         public
     * @param:        Rquest
     * @return:       String(Json)
     */
    public function createStaff(Request $request)
    {
        if (!$this->isManager()) {
            return self::responseERR(self::YOUR_ROLE_CANNOT_CALL_THIS_API, self::M_YOUR_ROLE_CANNOT_CALL_THIS_API);
        }
        try {
            $name = $request->{User::COL_NAME};
            $phone = $request->{User::COL_PHONE};
            $email = $request->{User::COL_EMAIL};
            $password = $request->{User::COL_PASSWORD};
            $gender = $request->{User::COL_GENDER};
            $birthDay = $request->{User::COL_BIRTHDAY};

            $validator = User::validator([
                User::COL_NAME => $name,
                User::VAL_USER_ID => $phone,
                User::COL_PASSWORD => $password,
                User::COL_GENDER => $gender,
                User::COL_BIRTHDAY => $birthDay,
            ], VerifiedCode::PHONE_CHANNEL);
            if ($validator->fails()) {
                return self::responseIER($validator->errors()->first());
            }

            $request->validate([File::VAL_FILE => File::FILE_VALIDATIONS[File::IMAGE_TYPE]]);

            $storeId = Auth::user()->{User::COL_STORE_ID};
            $dataCreate = [
                User::COL_NAME => $name,
                User::COL_PHONE => $phone,
                User::COL_EMAIL => $email,
                User::COL_PASSWORD => bcrypt($password),
                User::COL_GENDER => $gender,
                User::COL_BIRTHDAY => $birthDay,
                User::COL_ROLE_ID => User::STAFF_ROLE_ID,
                User::COL_STORE_ID => $storeId,
            ];
            DB::beginTransaction();
            $dataImages = [];
            $maxImages = (int) getenv('MAX_USER_IMAGE');
            if ($maxImages == 0) {
                $maxImages = 1;
            }
            $staff = User::create($dataCreate);
            if (!$staff) {
                return self::responseERR('ERR400xxx', 'Create staff failed1.');
            }
            $staffId = $staff->{User::COL_ID};
            for ($i = 0; $i < $maxImages; $i++) {
                $dataImage = [
                    File::COL_OWNER_ID => $staffId,
                    File::COL_OWNER_TYPE => User::class,
                    File::COL_PATH => getenv('DEFAULT_USER_AVATAR_URL'),
                    File::COL_TYPE => File::IMAGE_TYPE,
                    File::COL_CREATED_AT => now()
                ];
                array_push($dataImages, $dataImage);
            }
            if (!File::insert($dataImage)) {
                DB::rollBack();
                return self::responseERR('ERR400xxx', 'Create staff failed2.');
            }
            if ($request->has('file')) {
                $fileId = $staff->files->first()->{File::COL_ID};
                $request->fileId = $fileId;
                $request->type = File::IMAGE_TYPE;
                $fileController = new FileController();
                $responseSaveFile = $fileController->uploadFileS3($request)->getData();
                if ($responseSaveFile->code != 200) {
                    DB::rollBack();
                    return self::responseERR('ERR400xxx', 'Create staff failed3.');
                }
            }
            if ($request->has('shiftIds')) {
                $shiftIds = array_unique($request->shiftIds);
                $shiftFound = Shift::whereIn(Shift::COL_ID, $shiftIds)
                    ->where(Shift::COL_STORE_ID, $storeId)->pluck(Shift::COL_ID);
                if (count($shiftIds) != count($shiftFound)) {
                    DB::rollBack();
                    return self::responseERR('ERR400xxx', 'Update staff failed - there are invalid shifts.');
                }

                $dataInsert = [];
                foreach ($shiftIds as $shiftId) {
                    $data = [
                        UserShift::COL_USER_ID => $staffId,
                        UserShift::COL_SHIFT_ID => $shiftId,
                    ];
                    array_push($dataInsert, $data);
                }
                $rsInsert = UserShift::insert($dataInsert);
                if (!$rsInsert) {
                    DB::rollBack();
                    return self::responseERR('ERR400xxx', 'Update staff failed.');
                }
            }
            if ($request->has('skillIds')) {
                $skillIds = array_unique($request->skillIds);
                $skillFound = Skill::whereIn(Skill::COL_ID, $skillIds)
                    ->pluck(Skill::COL_ID);
                if (count($skillIds) != count($skillFound)) {
                    DB::rollBack();
                    return self::responseERR('ERR400xxx', 'Update staff failed - there are invalid skills.');
                }

                $dataInsert = [];
                foreach ($skillIds as $skillId) {
                    $data = [
                        UserSkill::COL_USER_ID => $staffId,
                        UserSkill::COL_SKILL_ID => $skillId,
                    ];
                    array_push($dataInsert, $data);
                }
                $rsInsert = UserSkill::insert($dataInsert);
                if (!$rsInsert) {
                    DB::rollBack();
                    return self::responseERR('ERR400xxx', 'Update staff failed.');
                }
            }
            DB::commit();
            $staff = User::find($staff->{User::COL_ID});
            return self::responseST('ST200xxx', 'Create staff successfully.', ['newStaff' => $staff]);
        } catch (Exception $ex) {
            DB::rollBack();
            return self::responseEX('EX500xxx', $ex->getMessage());
        }
    }

    /**
     * @functionName: getAllStaffs
     * @type:         public
     * @param:        Rquest
     * @return:       String(Json)
     */
    public function getAllStaffs(Request $request)
    {
        if (!$this->isManager()) {
            return self::responseERR(self::YOUR_ROLE_CANNOT_CALL_THIS_API, self::M_YOUR_ROLE_CANNOT_CALL_THIS_API);
        }
        try {
            $storeId = Auth::user()->{User::COL_STORE_ID};

            $staffs = User::where(User::COL_STORE_ID, $storeId)
                ->where(User::COL_ROLE_ID, User::STAFF_ROLE_ID)->get();

            $data = [
                'staffs' => $staffs,
            ];

            return self::responseST('ST200xxx', 'Get all staffs successfully.', $data);
        } catch (Exception $ex) {
            DB::rollBack();
            return self::responseEX('EX500xxx', $ex->getMessage());
        }
    }

    /**
     * @functionName: updateStaff
     * @type:         public
     * @param:        Request $request, $staffId
     * @return:       String(Json)
     */
    public function updateStaff(Request $request, $staffId)
    {
        if (!$this->isManager()) {
            return self::responseERR(self::YOUR_ROLE_CANNOT_CALL_THIS_API, self::M_YOUR_ROLE_CANNOT_CALL_THIS_API);
        }
        try {
            $name = $request->{User::COL_NAME};
            $phone = $request->{User::COL_PHONE};
            $email = $request->{User::COL_EMAIL};
            $password = $request->{User::COL_PASSWORD};
            $gender = $request->{User::COL_GENDER};
            $birthDay = $request->{User::COL_BIRTHDAY};

            $validator = User::validator([
                User::COL_NAME => $name,
                User::VAL_USER_ID => $phone,
                User::COL_PASSWORD => $password,
                User::COL_GENDER => $gender,
                User::COL_BIRTHDAY => $birthDay,
            ], VerifiedCode::PHONE_CHANNEL);
            if ($validator->fails()) {
                return self::responseIER($validator->errors()->first());
            }

            $request->validate([File::VAL_FILE => File::FILE_VALIDATIONS[File::IMAGE_TYPE]]);

            $storeId = Auth::user()->{User::COL_STORE_ID};
            $staff = User::where(User::COL_ID, $staffId)
                ->where(User::COL_ROLE_ID, User::STAFF_ROLE_ID)
                ->where(User::COL_STORE_ID, $storeId)
                ->first();
            if (!$staff) {
                return self::responseERR('ERR400xxx', 'Not found staff');
            }
            DB::beginTransaction();
            $staff->{User::COL_NAME} = $name;
            $staff->{User::COL_PHONE} = $phone;
            $staff->{User::COL_EMAIL} = $email;
            $staff->{User::COL_PASSWORD} = bcrypt($password);
            $staff->{User::COL_GENDER} = $gender;
            $staff->{User::COL_BIRTHDAY} = $birthDay;
            $rsSave = $staff->save();
            if (!$rsSave) {
                DB::rollBack();
                return self::responseERR('ERR400xxx', 'Update staff failed1.');
            }

            if ($request->has('file')) {
                $fileId = $staff->files->first()->{File::COL_ID};
                $request->fileId = $fileId;
                $request->type = File::IMAGE_TYPE;
                $fileController = new FileController();
                $responseSaveFile = $fileController->uploadFileS3($request)->getData();
                if ($responseSaveFile->code != 200) {
                    DB::rollBack();
                    return self::responseERR('ERR400xxx', 'Update staff failed2.');
                }
            }
            if ($request->has('shiftIds')) {
                $shiftIds = array_unique($request->shiftIds);
                $shiftFound = Shift::whereIn(Shift::COL_ID, $shiftIds)
                    ->where(Shift::COL_STORE_ID, $storeId)->pluck(Shift::COL_ID);
                if (count($shiftIds) != count($shiftFound)) {
                    DB::rollBack();
                    return self::responseERR('ERR400xxx', 'Update staff failed - there are invalid shifts.');
                }

                UserShift::where(UserShift::COL_USER_ID, $staffId)
                    ->whereIn(UserShift::COL_SHIFT_ID, $shiftIds)->delete();

                $dataInsert = [];
                foreach ($shiftIds as $shiftId) {
                    $data = [
                        UserShift::COL_USER_ID => $staffId,
                        UserShift::COL_SHIFT_ID => $shiftId,
                    ];
                    array_push($dataInsert, $data);
                }
                $rsInsert = UserShift::insert($dataInsert);
                if (!$rsInsert) {
                    DB::rollBack();
                    return self::responseERR('ERR400xxx', 'Update staff failed.');
                }
            }

            if ($request->has('skillIds')) {
                $skillIds = array_unique($request->skillIds);
                $skillFound = Skill::whereIn(Skill::COL_ID, $skillIds)
                    ->pluck(Skill::COL_ID);
                if (count($skillIds) != count($skillFound)) {
                    DB::rollBack();
                    return self::responseERR('ERR400xxx', 'Update staff failed - there are invalid skills.');
                }

                UserSkill::where(UserSkill::COL_USER_ID, $staffId)
                    ->whereIn(UserSkill::COL_SKILL_ID, $skillIds)->delete();

                $dataInsert = [];
                foreach ($skillIds as $skillId) {
                    $data = [
                        UserSkill::COL_USER_ID => $staffId,
                        UserSkill::COL_SKILL_ID => $skillId,
                    ];
                    array_push($dataInsert, $data);
                }
                $rsInsert = UserSkill::insert($dataInsert);
                if (!$rsInsert) {
                    DB::rollBack();
                    return self::responseERR('ERR400xxx', 'Update staff failed.');
                }
            }
            DB::commit();
            $staff = User::find($staffId);
            return self::responseST('ST200xxx', 'Update staff successfully.', ['newStaff' => $staff]);
        } catch (Exception $ex) {
            DB::rollBack();
            return self::responseEX('EX500xxx', $ex->getMessage());
        }
    }

    /**
     * @functionName: deleteStaff
     * @type:         public
     * @param:        int $staffId
     * @return:       String(Json)
     */
    public function deleteStaff(int $staffId)
    {
        if (!$this->isManager()) {
            return self::responseERR(self::YOUR_ROLE_CANNOT_CALL_THIS_API, self::M_YOUR_ROLE_CANNOT_CALL_THIS_API);
        }
        try {
            $validator = User::validator([
                User::COL_ID => $staffId,
            ]);
            if ($validator->fails()) {
                return self::responseIER($validator->errors()->first());
            }
            $storeId = Auth::user()->{User::COL_STORE_ID};
            $staff = User::where(User::COL_ID, $staffId)
                ->where(User::COL_ROLE_ID, User::STAFF_ROLE_ID)
                ->where(User::COL_STORE_ID, $storeId)
                ->first();
            if (!$staff) {
                return self::responseERR('ERR400xxx', 'Not found staff.');
            }
            DB::beginTransaction();
            if (!$staff->files()->delete() or !$staff->delete()) {
                DB::rollBack();
                return self::responseERR('ERR400xxx', 'Delete staff failed.');
            }
            DB::commit();
            return self::responseST('ST200xxx', 'Delete staff successfully.');
        } catch (Exception $ex) {
            DB::rollBack();
            return self::responseEX('EX500xxx', $ex->getMessage());
        }
    }

    /**
     * @functionName: getStaffById
     * @type:         public
     * @param:        int $staffId
     * @return:       String(Json)
     */
    public function getStaffById(int $staffId)
    {
        if (!$this->isManager()) {
            return self::responseERR(self::YOUR_ROLE_CANNOT_CALL_THIS_API, self::M_YOUR_ROLE_CANNOT_CALL_THIS_API);
        }
        try {
            $validator = User::validator([
                User::COL_ID => $staffId,
            ]);
            if ($validator->fails()) {
                return self::responseIER($validator->errors()->first());
            }
            $storeId = Auth::user()->{User::COL_STORE_ID};
            $staff = User::where(User::COL_ID, $staffId)
                ->where(User::COL_ROLE_ID, User::STAFF_ROLE_ID)
                ->where(User::COL_STORE_ID, $storeId)
                ->first();
            if (!$staff) {
                return self::responseERR('ERR400xxx', 'Not found staff.');
            }

            return self::responseST('ST200xxx', 'Get staff successfully.', ['staff' => $staff]);
        } catch (Exception $ex) {
            DB::rollBack();
            return self::responseEX('EX500xxx', $ex->getMessage());
        }
    }

    /**
     * @functionName: createShift
     * @type:         public
     * @param:        Rquest
     * @return:       String(Json)
     */
    public function createShift(Request $request)
    {
        if (!$this->isManager()) {
            return self::responseERR(self::YOUR_ROLE_CANNOT_CALL_THIS_API, self::M_YOUR_ROLE_CANNOT_CALL_THIS_API);
        }
        try {
            $startTime = $request->{Shift::VAL_START_TIME};
            $endTime = $request->{Shift::VAL_END_TIME};
            $dayInWeek = $request->{Shift::VAL_DAY_IN_WEEK};
            $shiftName = $request->{Shift::VAL_SHIFT_NAME};

            $validator = Shift::validator([
                Shift::VAL_START_TIME => $startTime,
                Shift::VAL_END_TIME => $endTime,
                Shift::VAL_DAY_IN_WEEK => $dayInWeek,
                Shift::VAL_SHIFT_NAME => $shiftName,
            ]);
            if ($validator->fails()) {
                return self::responseIER($validator->errors()->first());
            }

            $storeId = Auth::user()->{User::COL_STORE_ID};
            $dataCreate = [
                Shift::COL_START_TIME => $startTime,
                Shift::COL_END_TIME => $endTime,
                Shift::COL_DAY_IN_WEEK => $dayInWeek,
                Shift::COL_SHIFT_NAME => $shiftName,
                Shift::COL_STORE_ID => $storeId,
            ];

            $shift = Shift::create($dataCreate);
            if (!$shift) {
                return self::responseERR('ERR400xxx', 'Create shift failed.');
            }
            return self::responseST('ST200xxx', 'Create shift successfully.', ['newShift' => $shift]);
        } catch (Exception $ex) {
            return self::responseEX('EX500xxx', $ex->getMessage());
        }
    }

    /**
     * @functionName: getAllShifts
     * @type:         public
     * @param:        Empty
     * @return:       String(Json)
     */
    public function getAllShifts()
    {
        if (!$this->isManager()) {
            return self::responseERR(self::YOUR_ROLE_CANNOT_CALL_THIS_API, self::M_YOUR_ROLE_CANNOT_CALL_THIS_API);
        }
        try {
            $storeId = Auth::user()->{User::COL_STORE_ID};

            $shifts = Shift::where(Shift::COL_STORE_ID, $storeId)->get();

            $data = [
                'shifts' => Shift::mergeShift($shifts),
            ];

            return self::responseST('ST200xxx', 'Get all shifts successfully.', $data);
        } catch (Exception $ex) {
            DB::rollBack();
            return self::responseEX('EX500xx1', $ex->getMessage());
        }
    }

    /**
     * @functionName: updateShift
     * @type:         public
     * @param:        Request $request, $shiftId
     * @return:       String(Json)
     */
    public function updateShift(Request $request, $shiftId)
    {
        if (!$this->isManager()) {
            return self::responseERR(self::YOUR_ROLE_CANNOT_CALL_THIS_API, self::M_YOUR_ROLE_CANNOT_CALL_THIS_API);
        }
        try {
            $startTime = $request->{Shift::VAL_START_TIME};
            $endTime = $request->{Shift::VAL_END_TIME};
            $dayInWeek = $request->{Shift::VAL_DAY_IN_WEEK};
            $shiftName = $request->{Shift::VAL_SHIFT_NAME};

            $validator = Shift::validator([
                Shift::VAL_START_TIME => $startTime,
                Shift::VAL_END_TIME => $endTime,
                Shift::VAL_DAY_IN_WEEK => $dayInWeek,
                Shift::VAL_SHIFT_NAME => $shiftName,
            ]);
            if ($validator->fails()) {
                return self::responseIER($validator->errors()->first());
            }

            $shift = Shift::find($shiftId);
            if (!$shift) {
                return self::responseERR('ERR400xxx', 'Not found shift');
            }
            $shift->{Shift::COL_START_TIME} = $startTime;
            $shift->{Shift::COL_END_TIME} = $endTime;
            $shift->{Shift::COL_DAY_IN_WEEK} = $dayInWeek;
            $shift->{Shift::COL_SHIFT_NAME} = $shiftName;
            $rsSave = $shift->save();
            if (!$rsSave) {
                return self::responseERR('ERR400xxx', 'Update shift failed.');
            }

            return self::responseST('ST200xxx', 'Update shift successfully.', ['newShift' => $shift]);
        } catch (Exception $ex) {
            return self::responseEX('EX500xxx', $ex->getMessage());
        }
    }

    /**
     * @functionName: deleteShift
     * @type:         public
     * @param:        int $shiftId
     * @return:       String(Json)
     */
    public function deleteShift(int $shiftId)
    {
        if (!$this->isManager()) {
            return self::responseERR(self::YOUR_ROLE_CANNOT_CALL_THIS_API, self::M_YOUR_ROLE_CANNOT_CALL_THIS_API);
        }
        try {
            $validator = Shift::validator([
                Shift::COL_ID => $shiftId,
            ]);
            if ($validator->fails()) {
                return self::responseIER($validator->errors()->first());
            }
            $shift = Shift::find($shiftId);
            if (!$shift) {
                return self::responseERR('ERR400xxx', 'Not found shift.');
            }
            if (!$shift->delete()) {
                return self::responseERR('ERR400xxx', 'Delete shift failed.');
            }
            return self::responseST('ST200xxx', 'Delete shift successfully.');
        } catch (Exception $ex) {
            return self::responseEX('EX500xxx', $ex->getMessage());
        }
    }

    /**
     * @functionName: filterServiceOrder
     * @type:         public
     * @param:        Request $request
     * @return:       String(Json)
     */
    public function filterServiceOrder(Request $request)
    {
        if (!$this->isManager() and !$this->isAdmin() and !$this->isCustomer()) {
            return self::responseERR(self::YOUR_ROLE_CANNOT_CALL_THIS_API, self::M_YOUR_ROLE_CANNOT_CALL_THIS_API);
        }
        try {
            $today = new DateTime();
            $today = $today->format('Y-m-d');
            $itemPerPage = $request->input(ServiceOrder::VAL_ITEM_PER_PAGE, ServiceOrder::ITEM_PER_PAGE_DEFAULT);
            $page = $request->input(ServiceOrder::VAL_PAGE, ServiceOrder::PAGE_DEFAULT);
            $sortBy = $request->input(ServiceOrder::VAL_SORT_BY, ServiceOrder::COL_ID);
            $sortOrder = $request->input(ServiceOrder::VAL_SORT_ORDER, ServiceOrder::ASC_ORDER);
            $fromDate = $request->input(ServiceOrder::VAL_FROM_DATE, $today);
            $toDate = $request->input(ServiceOrder::VAL_TO_DATE, $today);
            $validator = ServiceOrder::validator([
                ServiceOrder::VAL_ITEM_PER_PAGE => $itemPerPage,
                ServiceOrder::VAL_PAGE => $page,
                ServiceOrder::VAL_SORT_BY => $sortBy,
                ServiceOrder::VAL_SORT_ORDER => $sortOrder,
                ServiceOrder::VAL_FROM_DATE => $fromDate,
                ServiceOrder::VAL_TO_DATE => $toDate,
            ]);
            if ($validator->fails()) {
                return self::responseIER($validator->errors()->first());
            }
            $currentUser = Auth::user();
            $query = ServiceOrder::query();
            $fromDate = $fromDate . ' 00:00:00';
            $toDate = $toDate . ' 23:59:59';

            $query = $query->whereBetween(ServiceOrder::COL_ORDER_DATE, [$fromDate, $toDate]);
            if ($this->isManager()) {
                $query = $query->where(ServiceOrder::COL_STORE_ID, $currentUser->{User::COL_STORE_ID});
            } elseif ($this->isCustomer()) {
                $query = $query->where(ServiceOrder::COL_USER_ID, $currentUser->{User::COL_ID});
            } elseif ($this->isAdmin() and $request->has('storeId')) {
                $query = $query->where(ServiceOrder::COL_STORE_ID, $request->storeId);
            }
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
                'orders' => $data,
            ];

            return self::responseST('ST200xxx', 'Get service orders successfully', $dataResponse);
        } catch (Exception $ex) {
            return self::responseEX('EX500xxx', $ex->getMessage());
        }
    }

    /**
     * @functionName: comfirmServiceOrder
     * @type:         public
     * @param:        int $orderId
     * @return:       String(Json)
     */
    public function comfirmServiceOrder(int $orderId)
    {
        if (!$this->isManager()) {
            return self::responseERR(self::YOUR_ROLE_CANNOT_CALL_THIS_API, self::M_YOUR_ROLE_CANNOT_CALL_THIS_API);
        }
        try {
            $validator = ServiceOrder::validator([
                ServiceOrder::COL_ID => $orderId,
            ]);
            if ($validator->fails()) {
                return self::responseIER($validator->errors()->first());
            }
            $order = ServiceOrder::find($orderId);
            if (!$order) {
                return self::responseERR('ERR400xxx', 'Not found order.');
            }
            $storeId = Auth::user()->{User::COL_STORE_ID};
            if ($storeId != $order->{ServiceOrder::COL_STORE_ID}) {
                return self::responseERR('ERR400xxx', 'This order not belong to your store.');
            }
            if ($order->{ServiceOrder::COL_STATUS} != ServiceOrder::NOT_COMFIRM) {
                return self::responseERR('ERR400xxx', 'Status of this order not valid for confirming.');
            }
            $order->{ServiceOrder::COL_STATUS} = ServiceOrder::CONFIRMED;
            if (!$order->save()) {
                return self::responseERR('ERR400xxx', 'Confirm service order failed.');
            }
            return self::responseST('ST200xxx', 'Confirm service order successfully.');
        } catch (Exception $ex) {
            return self::responseEX('EX500xxx', $ex->getMessage());
        }
    }

    /**
     * @functionName: filterProductOrder
     * @type:         public
     * @param:        Request $request
     * @return:       String(Json)
     */
    public function filterProductOrder(Request $request)
    {
        if (!$this->isAdmin() and !$this->isCustomer()) {
            return self::responseERR(self::YOUR_ROLE_CANNOT_CALL_THIS_API, self::M_YOUR_ROLE_CANNOT_CALL_THIS_API);
        }
        try {
            $today = new DateTime();
            $today = $today->format('Y-m-d');
            $itemPerPage = $request->input(ProductOrder::VAL_ITEM_PER_PAGE, ProductOrder::ITEM_PER_PAGE_DEFAULT);
            $page = $request->input(ProductOrder::VAL_PAGE, ProductOrder::PAGE_DEFAULT);
            $sortBy = $request->input(ProductOrder::VAL_SORT_BY, ProductOrder::COL_ID);
            $sortOrder = $request->input(ProductOrder::VAL_SORT_ORDER, ProductOrder::ASC_ORDER);
            $fromDate = $request->input(ProductOrder::VAL_FROM_DATE, $today);
            $toDate = $request->input(ProductOrder::VAL_TO_DATE, $today);
            $validator = ProductOrder::validator([
                ProductOrder::VAL_ITEM_PER_PAGE => $itemPerPage,
                ProductOrder::VAL_PAGE => $page,
                ProductOrder::VAL_SORT_BY => $sortBy,
                ProductOrder::VAL_SORT_ORDER => $sortOrder,
                ProductOrder::VAL_FROM_DATE => $fromDate,
                ProductOrder::VAL_TO_DATE => $toDate,
            ]);
            if ($validator->fails()) {
                return self::responseIER($validator->errors()->first());
            }
            $query = ProductOrder::query();
            $fromDate = $fromDate . ' 00:00:00';
            $toDate = $toDate . ' 23:59:59';
            $query = $query->whereBetween(ProductOrder::COL_ORDER_DATE, [$fromDate, $toDate]);
            $currentUser = Auth::user();
            if ($currentUser->{User::COL_ROLE_ID} == User::CUSTOMER_ROLE_ID) {
                $query = $query->where(ProductOrder::COL_USER_ID, $currentUser->{User::COL_ID});
            }
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
                'orders' => $data,
            ];

            return self::responseST('ST200xxx', 'Get product orders successfully', $dataResponse);
        } catch (Exception $ex) {
            return self::responseEX('EX500xxx', $ex->getMessage());
        }
    }

    /**
     * @functionName: getAllSkills
     * @type:         public
     * @param:        Empty
     * @return:       String(Json)
     */
    public function getAllSkills()
    {
        if (!$this->isAdmin() and !$this->isManager()) {
            return self::responseERR(self::YOUR_ROLE_CANNOT_CALL_THIS_API, self::M_YOUR_ROLE_CANNOT_CALL_THIS_API);
        }
        try {
            $skills = Skill::get();

            $data = [
                'skills' => $skills,
            ];

            return self::responseST('ST200xxx', 'Get all skills successfully.', $data);
        } catch (Exception $ex) {
            DB::rollBack();
            return self::responseEX('EX500xx1', $ex->getMessage());
        }
    }

    /**
     * @functionName: cancelProductOrder
     * @type:         public
     * @param:        int $orderId
     * @return:       String(Json)
     */
    public function cancelProductOrder(Request $request, $orderId)
    {
        if (!$this->isAdmin()) {
            return self::responseERR(self::YOUR_ROLE_CANNOT_CALL_THIS_API, self::M_YOUR_ROLE_CANNOT_CALL_THIS_API);
        }
        try {
            $orderId = (int) $orderId;
            $cancelReason = $request->{ProductOrder::VAL_CANCEL_REASON};
            $validator = ProductOrder::validator([
                ProductOrder::COL_ID => $orderId,
                ProductOrder::VAL_CANCEL_REASON => $cancelReason,
            ]);
            if ($validator->fails()) {
                return self::responseIER($validator->errors()->first());
            }
            $order = ProductOrder::find($orderId);

            $orderStatus = $order->{ProductOrder::COL_STATUS};
            if ($orderStatus == ProductOrder::ADMIN_CANCELED
                or $orderStatus == ProductOrder::COMPLETED
                or $orderStatus == ProductOrder::CUSTOMER_CANCELED) {
                return self::responseERR('ERR400xxx', 'This order was '
                    . ($orderStatus != ProductOrder::COMPLETED ? 'canceled.' : 'complete.'));
            }
            $order->{ProductOrder::COL_STATUS} = ProductOrder::ADMIN_CANCELED;
            $order->{ProductOrder::COL_CANCEL_REASON} = $cancelReason;
            DB::beginTransaction();
            if (!$order->save() or !ProductOrder::returnQuantityProduct($orderId)) {
                DB::rollBack();
                return self::responseERR('ERR400xxx', 'Cancel order failed.');
            }
            DB::commit();
            return self::responseST('ST200xxx', 'Cancel order successfully.');
        } catch (Exception $ex) {
            DB::rollBack();
            return self::responseEX('EX500xxx', $ex->getMessage());
        }
    }

    /**
     * @functionName: confirmProductOrder
     * @type:         public
     * @param:        int $orderId
     * @return:       String(Json)
     */
    public function confirmProductOrder($orderId)
    {
        if (!$this->isAdmin()) {
            return self::responseERR(self::YOUR_ROLE_CANNOT_CALL_THIS_API, self::M_YOUR_ROLE_CANNOT_CALL_THIS_API);
        }
        try {
            $orderId = (int) $orderId;
            $order = ProductOrder::find($orderId);

            $orderStatus = $order->{ProductOrder::COL_STATUS};
            if ($orderStatus != ProductOrder::NOT_CONFIRMED) {
                return self::responseERR('ERR400xxx', 'This order must be at not comfirm status.');
            }
            $order->{ProductOrder::COL_STATUS} = ProductOrder::CONFIRMED;
            if (!$order->save()) {
                return self::responseERR('ERR400xxx', 'Confirm order failed.');
            }
            return self::responseST('ST200xxx', 'Confirm order successfully.');
        } catch (Exception $ex) {
            return self::responseEX('EX500xxx', $ex->getMessage());
        }
    }

    /**
     * @functionName: cancelServiceOrder
     * @type:         public
     * @param:        int $orderId
     * @return:       String(Json)
     */
    public function cancelServiceOrder(Request $request, int $orderId)
    {
        if (!$this->isManager() and !$this->isAdmin()) {
            return self::responseERR(self::YOUR_ROLE_CANNOT_CALL_THIS_API, self::M_YOUR_ROLE_CANNOT_CALL_THIS_API);
        }
        try {
            $cancelReason = $request->{ServiceOrder::VAL_CANCEL_REASON};
            $validator = ServiceOrder::validator([
                ServiceOrder::COL_ID => $orderId,
                ServiceOrder::VAL_CANCEL_REASON => $cancelReason,
            ]);
            if ($validator->fails()) {
                return self::responseIER($validator->errors()->first());
            }
            $order = ServiceOrder::find($orderId);
            if (!$order) {
                return self::responseERR('ERR400xxx', 'Not found order.');
            }
            if ($this->isManager()) {
                $manager = Auth::user();
                $storeId =$manager->{User::COL_STORE_ID};
                if ($storeId != $order->{ServiceOrder::COL_STORE_ID}) {
                    return self::responseERR('ERR400xxx', 'This order is not belong to your store.');
                }
            }
            if ($order->{ServiceOrder::COL_STATUS} != ServiceOrder::NOT_COMFIRM
                and $order->{ServiceOrder::COL_STATUS} != ServiceOrder::CONFIRMED) {
                return self::responseERR('ERR400xxx', 'Status of this order not valid for cancel.');
            }
            $order->{ServiceOrder::COL_STATUS} = ServiceOrder::MANAGE_CANCEL;
            $order->{ServiceOrder::COL_CANCEL_REASON} = $cancelReason;
            if (!$order->save()) {
                return self::responseERR('ERR400xxx', 'Cancel service order failed.');
            }
            return self::responseST('ST200xxx', 'Cancel service order successfully.');
        } catch (Exception $ex) {
            return self::responseEX('EX500xxx', $ex->getMessage());
        }
    }
}
