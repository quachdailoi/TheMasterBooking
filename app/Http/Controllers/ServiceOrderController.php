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
    const API_URL_ORDER = '/booking';
    const API_URL_GET_ORDER_DETAILS = '/get-details/{orderId}';

    /** Method */
    const METHOD_ORDER = 'order';
    const METHOD_GET_ORDER_DETAILS = 'getOrderDetails';

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

            $validator = ServiceOrder::validator([
                ServiceOrder::COL_ORDER_DATE => $orderDateTime,
                ServiceOrder::COL_EMAIL => $email,
                ServiceOrder::COL_PHONE => $phone,
                ServiceOrder::VAL_USER_NAME => $name,
                ServiceOrder::COL_NOTE => $note,
                ServiceOrder::VAL_STORE_ID => $storeId,
                'serviceIds' => $serviceIds,
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
                ServiceOrder::COL_SERVICES => $services,
            ];
            $order = ServiceOrder::create($dataOrder);
            if (!$order) {
                DB::rollBack();
                return self::responseERR('ERR400xxx', 'Booking services failed.');
            }
            $orderId = $order->{ServiceOrder::COL_ID};

            DB::commit();
            return self::responseST('ST200xxx', 'Booking service(s) successfully.', ['orderId' => $orderId]);
        } catch (Exception $ex) {
            DB::rollBack();
            return self::responseEX('EX500xxx', $ex->getMessage());
        }
    }

    /**
     * @functionName: getOrderDetails
     * @type:         public
     * @param:        int $orderId
     * @return:       String(Json)
     */
    public function getOrderDetails($orderId)
    {
        try {
            $orderId = (int) $orderId;
            $order = ServiceOrder::find($orderId);
            $currentUserId = Auth::user()->{User::COL_ID};
            if ($order->{ServiceOrder::COL_USER_ID} != $currentUserId) {
                return self::responseERR('ERR400xxx', 'This is not your order.');
            }
            return self::responseST('ST200xxx', 'Get order details successfully.', $order);
        } catch (Exception $ex) {
            return self::responseEX('EX500xxx', $ex->getMessage());
        }
    }
}
