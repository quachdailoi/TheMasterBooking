<?php

namespace App\Http\Controllers;

use App\CodeAndMessage\ProductOrderMessage as POM;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\User;
use App\Models\UserOrder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductOrderController extends Controller
{
    /** Prefix */
    const PREFIX = 'order';

    /** Api url */
    const API_URL_CHECKOUT = '/checkout';

    /** Method */
    const METHOD_CHECKOUT  = 'checkout';

    /**
     * @functionName: checkout
     * @type:         public
     * @param:        Request $request
     * @return:       String(Json)
     */
    public function checkout(Request $request)
    {
        try {
            $address= $request->{UserOrder::COL_ADDRESS};
            $email = $request->{UserOrder::COL_EMAIL};
            $phone = $request->{UserOrder::COL_PHONE};
            $receiverName = $request->{UserOrder::VAL_RECEIVER_NAME};
            $shippingMethod = $request->{UserOrder::VAL_SHIPPING_METHOD};
            $paymentMethod = $request->{UserOrder::VAL_PAYMENT_METHOD};

            $validator = UserOrder::validator([
                UserOrder::COL_ADDRESS => $address,
                UserOrder::COL_EMAIL => $email,
                UserOrder::COL_PHONE => $phone,
                UserOrder::VAL_RECEIVER_NAME => $receiverName,
                UserOrder::VAL_SHIPPING_METHOD => $shippingMethod,
                UserOrder::VAL_PAYMENT_METHOD => $paymentMethod,
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
                UserOrder::COL_USER_ID => $currentUser->{User::COL_ID},
                UserOrder::COL_ORDER_DATE => now(),
                UserOrder::COL_ADDRESS => $address,
                UserOrder::COL_EMAIL => $email,
                UserOrder::COL_PHONE => $phone,
                UserOrder::COL_RECEIVER_NAME => $receiverName,
                UserOrder::COL_SHIPPING_METHOD => $shippingMethod,
                UserOrder::COL_PAYMENT_METHOD => $paymentMethod,
            ];
            $order = UserOrder::create($orderData);
            if (!$order) {
                DB::rollBack();
                return self::responseERR('ERR400xxx', 'Checkout failed.');
            }
            $orderId = $order->{UserOrder::COL_ID};
            // create product order
            // check enough quantity, calculate amount and prepare products in order, minus quantity in DB
            $products = Product::whereIn(Product::COL_ID, array_keys($cart))->get();
            $notEnoughQuantityProducts = [];
            $amount = 0;
            $dataOrderProducts = [];
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
                $orderProduct = [
                    ProductOrder::COL_ORDER_ID => $orderId,
                    ProductOrder::COL_PRODUCT_ID => $productId,
                ];
                array_push($dataOrderProducts, $orderProduct);
            }
            if (!empty($notEnoughQuantityProducts)) {
                DB::rollBack();
                return self::responseERR(POM::NOT_ENOUGH_QUANTITY_PRODUCT, POM::M_NOT_ENOUGH_QUANTITY_PRODUCT, $notEnoughQuantityProducts);
            }
            $order->{UserOrder::COL_AMOUNT} = $amount;
            if (!$order->save()) {
                DB::rollBack();
                return self::responseERR(POM::CHECKOUT_FAILED, POM::M_CHECKOUT_FAILED);
            }
            if (!ProductOrder::insert($dataOrderProducts)) {
                DB::rollBack();
                return self::responseERR(POM::CHECKOUT_FAILED, POM::M_CHECKOUT_FAILED);
            }
            DB::commit();
            //\Mail::to($email)->send(new \App\Mail\OrderDetailsMail('abc'));
            // clear cart
            $currentUser->{User::COL_CART} = null;
            $currentUser->save();
            return self::responseST(POM::CHECKOUT_SUCCESS, POM::M_CHECKOUT_SUCCESS, ['orderId' => $orderId]);
        } catch (Exception $ex) {
            return self::responseEX(POM::EXW_CHECKOUT, $ex->getMessage());
        }
    }
}
