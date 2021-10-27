<?php

namespace App\Http\Controllers;

use App\CodeAndMessage\ServiceM;
use App\Models\BookingService;
use App\Models\Service;
use App\Models\ServiceOrder;
use App\Models\Store;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceOrderController extends Controller
{
    /** Prefix */
    const PREFIX = 'service-order';

    /** Api url */
    const API_URL_ORDER = '/order';

    /** Method */
    const METHOD_ORDER = 'order';

    /**
     * @functionName: serviceOrder
     * @type:         public
     * @param:        Request $request
     * @return:       String(Json)
     */
    public function order(Request $request)
    {
        try {
            $storeId = $request->storeId;
            $serviceIds = $request->serviceIds;
            $orderDateTime = $request->orderDateTime;
            $name = $request->userName;
            $phone = $request->phone;
            $email = $request->email;
            $note = $request->note;

            $validator = BookingService::validator([
                'serviceIds' => $serviceIds
            ]);
            if ($validator->fails()) {
                return self::responseIER($validator->errors()->first());
            }
            $validator = ServiceOrder::validator([
                ServiceOrder::COL_ORDER_DATE => $orderDateTime,
                ServiceOrder::COL_EMAIL => $email,
                ServiceOrder::COL_PHONE => $phone,
                ServiceOrder::VAL_USER_NAME => $name,
                ServiceOrder::COL_NOTE => $note,
                ServiceOrder::VAL_STORE_ID => $storeId,
            ]);
            if ($validator->fails()) {
                return self::responseIER($validator->errors()->first());
            }

            if (!Store::checkExist($storeId)) {
                return self::responseERR('ERR400xxx', 'Store not exist.');
            }

            $totalServices = count($serviceIds);
            $amount = 0;
            $services = Service::whereIn(Service::COL_ID, $serviceIds)->get();
            $totalServiceFound = $services->count();
            if ($totalServices != $totalServiceFound) {
                return self::responseERR('ERR400xx', 'Service ids was wrong - there are ids not found.');
            }
            foreach ($services as $service) {
                $amount += $service->{Service::COL_PRICE};
            }
            $userId = Auth::user()->{User::COL_ID};
            DB::beginTransaction();
            $dataOrder = [
                ServiceOrder::COL_USER_ID => $userId,
                ServiceOrder::COL_AMOUNT => $amount,
                ServiceOrder::COL_ORDER_DATE => $orderDateTime,
                ServiceOrder::COL_EMAIL => $email,
                ServiceOrder::COL_PHONE => $phone,
                ServiceOrder::COL_USER_NAME => $name,
                ServiceOrder::COL_NOTE => $note,
                ServiceOrder::COL_STORE_ID => $storeId,
            ];
            $order = ServiceOrder::create($dataOrder);
            if (!$order) {
                DB::rollBack();
                return self::responseERR('ERR400xxx', 'Booking services failed.');
            }
            $orderId = $order->{ServiceOrder::COL_ID};
            $dataBookingServices = [];
            foreach ($serviceIds as $id) {
                $data = [
                    BookingService::COL_ORDER_ID => $orderId,
                    BookingService::COL_SERVICE_ID => $id,
                    BookingService::COL_CREATED_AT => Carbon::now(),
                ];
                array_push($dataBookingServices, $data);
            }
            $rsSave = BookingService::insert($dataBookingServices);
            if (!$rsSave) {
                DB::rollBack();
                return self::responseERR('ERR400xxx', 'Booking services failed.');
            }
            DB::commit();
            return self::responseST('ST200xxx', 'Booking service(s) successfully.');
        } catch (Exception $ex) {
            DB::rollBack();
            return self::responseEX('EX500xxx', $ex->getMessage());
        }
    }
}
