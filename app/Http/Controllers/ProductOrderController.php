<?php

namespace App\Http\Controllers;

use App\CodeAndMessage\ProductOrderMessage as POM;
use App\Jobs\SendOrderDetailsMail;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\User;
use App\Models\UserOrder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ProductOrderController extends Controller
{
    /** Prefix */
    const PREFIX = 'product-order';

    /** Api url */
    const API_URL_CHECKOUT = '/checkout';
    const API_URL_GET_ORDER_DETAILS = '/get-details/{orderId}';
    const API_URL_CANCEL_PRODUCT_ORDER = '/cancel/{orderId}';

    /** Method */
    const METHOD_CHECKOUT = 'checkout';
    const METHOD_GET_ORDER_DETAILS = 'getOrderDetails';
    const METHOD_CANCEL_ORDER = 'cancelOrder';

    /**
     * @functionName: checkout
     * @type:         public
     * @param:        Request $request
     * @return:       String(Json)
     */
    public function checkout(Request $request)
    {
        try {
            $address= $request->{ProductOrder::COL_ADDRESS};
            $email = $request->{ProductOrder::COL_EMAIL};
            $phone = $request->{ProductOrder::COL_PHONE};
            $receiverName = $request->{ProductOrder::VAL_RECEIVER_NAME};
            $shippingMethod = $request->{ProductOrder::VAL_SHIPPING_METHOD};
            $paymentMethod = $request->{ProductOrder::VAL_PAYMENT_METHOD};
            $note = $request->{ProductOrder::COL_NOTE};

            $validator = ProductOrder::validator([
                ProductOrder::COL_ADDRESS => $address,
                ProductOrder::COL_EMAIL => $email,
                ProductOrder::COL_PHONE => $phone,
                ProductOrder::VAL_RECEIVER_NAME => $receiverName,
                ProductOrder::VAL_SHIPPING_METHOD => $shippingMethod,
                ProductOrder::VAL_PAYMENT_METHOD => $paymentMethod,
                ProductOrder::COL_NOTE => $note,
            ]);
            if ($validator->fails()) {
                return self::responseIER($validator->errors()->first());
            }
            $currentUser = Auth::user();
            $cart = $currentUser->{User::COL_CART};
            if (empty($cart)) {
                return self::responseERR(POM::CART_IS_EMPTY, POM::M_CART_IS_EMPTY);
            }
            DB::beginTransaction();
            // create user order
            $orderData = [
                ProductOrder::COL_USER_ID => $currentUser->{User::COL_ID},
                ProductOrder::COL_ORDER_DATE => now(),
                ProductOrder::COL_ADDRESS => $address,
                ProductOrder::COL_EMAIL => $email,
                ProductOrder::COL_PHONE => $phone,
                ProductOrder::COL_RECEIVER_NAME => $receiverName,
                ProductOrder::COL_SHIPPING_METHOD => $shippingMethod,
                ProductOrder::COL_PAYMENT_METHOD => $paymentMethod,
                ProductOrder::COL_NOTE => $note,
                ProductOrder::COL_STATUS => ProductOrder::NOT_CONFIRMED,
            ];

            // create product order
            // check enough quantity, calculate amount and prepare products in order, minus quantity in DB
            $products = Product::whereIn(Product::COL_ID, array_keys($cart))->get();
            $notEnoughQuantityProducts = [];
            $amount = 0;

            foreach ($products as $product) {
                $productId = $product->{Product::COL_ID};
                if ($product->{Product::COL_QUANTITY} < $cart[$productId]) {
                    array_push($notEnoughQuantityProducts, $product);
                }
                $amount += $product->{Product::COL_PRICE} * $cart[$productId];
                $product->{Product::COL_QUANTITY} -= $cart[$productId];
                if (!$product->save()) {
                    DB::rollBack();
                    return self::responseERR(POM::CHECKOUT_FAILED, POM::CHECKOUT_FAILED);
                }
                $product->{Product::COL_QUANTITY} = $cart[$productId];
            }
            if (!empty($notEnoughQuantityProducts)) {
                DB::rollBack();
                return self::responseERR(POM::NOT_ENOUGH_QUANTITY_PRODUCT, POM::M_NOT_ENOUGH_QUANTITY_PRODUCT, $notEnoughQuantityProducts);
            }
            $orderData[ProductOrder::COL_AMOUNT] = $amount;
            $orderData[ProductOrder::COL_PRODUCTS] = $products;
            $order = ProductOrder::create($orderData);
            if (!$order) {
                DB::rollBack();
                return self::responseERR('ERR400xxx', 'Checkout failed.');
            }
            DB::commit();
            $orderId = $order->{ProductOrder::COL_ID};
            $order->{ProductOrder::COL_SHIPPING_METHOD}
                = ProductOrder::SHIPPING_MAP[$order->{ProductOrder::COL_SHIPPING_METHOD}];
            $order->{ProductOrder::COL_PAYMENT_METHOD}
                = ProductOrder::PAYMENT_MAP[$order->{ProductOrder::COL_PAYMENT_METHOD}];
            $details = [
                'order' => $order,
                'products' => $products,
                'user' => Auth::user(),
                'email' => $email,
            ];

            Mail::to($email)->send(new \App\Mail\OrderDetailsMail($details));
            //dispatch(new SendOrderDetailsMail($details));
            // clear cart
            $currentUser->{User::COL_CART} = null;
            $currentUser->save();
            return self::responseST(POM::CHECKOUT_SUCCESS, POM::M_CHECKOUT_SUCCESS, ['orderId' => $orderId]);
        } catch (Exception $ex) {
            return self::responseEX(POM::EXW_CHECKOUT, $ex->getMessage());
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
            $order = ProductOrder::find($orderId);
            $currentUserId = Auth::user()->{User::COL_ID};
            if ($order->{ProductOrder::COL_USER_ID} != $currentUserId) {
                return self::responseERR('ERR400xxx', 'This is not your order.');
            }
            return self::responseST('ST200xxx', 'Get order details successfully.', $order);
        } catch (Exception $ex) {
            return self::responseEX('EX500xxx', $ex->getMessage());
        }
    }

    /**
     * @functionName: cancelOrder
     * @type:         public
     * @param:        int $orderId
     * @return:       String(Json)
     */
    public function cancelOrder(Request $request, $orderId)
    {
        if (!$this->isCustomer()) {
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
            $currentUserId = Auth::user()->{User::COL_ID};
            if ($order->{ProductOrder::COL_USER_ID} != $currentUserId) {
                return self::responseERR('ERR400xxx', 'This is not your order.');
            }

            $orderStatus = $order->{ProductOrder::COL_STATUS};
            if ($orderStatus == ProductOrder::ADMIN_CANCELED
                or $orderStatus == ProductOrder::COMPLETED
                or $orderStatus == ProductOrder::CUSTOMER_CANCELED) {
                return self::responseERR('ERR400xxx', 'This order was '
                    . ($orderStatus != ProductOrder::COMPLETED ? 'canceled.' : 'complete.'));
            }
            $order->{ProductOrder::COL_STATUS} = ProductOrder::CUSTOMER_CANCELED;
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
}
