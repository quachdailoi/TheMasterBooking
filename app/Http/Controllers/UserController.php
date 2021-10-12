<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VerifiedCode;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Twilio\Rest\Client;
use App\CodeAndMessage\UserMessage as UM;
use App\Models\Product;

class UserController extends Controller
{
    /** prefix */
    const PREFIX = 'user';

    /** Api url */
    const API_URL_LOGIN = '/authentication/login';
    const API_URL_REGISTER = '/authentication/register';
    const API_URL_SEND_CODE_TO = '/authentication/send-code-to';
    const API_URL_LOGOUT = '/logout';
    const API_URL_CHANGE_PASSWORD = '/change-password';
    const API_URL_RESET_PASSWORD = '/authentication/reset-password';
    const API_URL_GET_USER_PROFILE = '/get-profile';
    const API_URL_UPDATE_USER_PROFILE = '/update-profile';
    const API_URL_GET_CART = '/get-cart';

    /** Method */
    const METHOD_LOGIN = 'login';
    const METHOD_REGISTER = 'register';
    const METHOD_SEND_CODE_TO = 'sendCodeTo';
    const METHOD_LOGOUT = 'logout';
    const METHOD_CHANGE_PASSWORD = 'changePassword';
    const METHOD_RESET_PASSWORD = 'resetPassword';
    const METHOD_GET_PROFILE = 'getProfile';
    const METHOD_UPDATE_PROFILE = 'updateProfile';
    const METHOD_GET_CART = 'getCart';

    // type of verified code
    const TYPE_REGISTER = '0';
    const TYPE_FORGOT_PASSWORD = '1';

    /**
     * @functionName: register
     * @type:         public
     * @param:        Request $request
     * @return:       String(Json)
     */
    public function register(Request $request)
    {
        try {
            $userId = $request->{User::VAL_USER_ID}; //phone number or email address
            $code = $request->{User::VAL_CODE};
            $channel = $request->{User::VAL_CHANNEL}; //phone or email

            $name = $request->{User::COL_NAME};
            $password = $request->{User::COL_PASSWORD};
            $confirmPassword = $request->{User::VAL_CONFIRM_PASSWORD};
            $gender = $request->{User::COL_GENDER};
            $birthDay = $request->{User::COL_BIRTHDAY};

            $validator = User::validator([
                User::VAL_USER_ID => $userId,
                User::VAL_CODE => $code,
                User::VAL_CHANNEL => $channel,
                User::COL_NAME => $name,
                User::COL_PASSWORD => $password,
                User::VAL_CONFIRM_PASSWORD => $confirmPassword,
                User::COL_BIRTHDAY => $birthDay,
                User::COL_GENDER => $gender,
            ], $channel);
            if ($validator->fails()) {
                return self::responseIER($validator->errors()->first());
            }
            $existUser = User::where(User::COL_PHONE, $userId)
                ->orWhere(User::COL_EMAIL, $userId)->first();
            if ($existUser) {
                $detailsCode = UM::PHONE_NUMBER_EXIST;
                $message = UM::PHONE_NUMBER_EXIST;
                if ($channel === VerifiedCode::EMAIL_CHANNEL) {
                    $detailsCode = UM::EMAIL_ADDRESS_EXIST;
                    $message = UM::EMAIL_ADDRESS_EXIST;
                }
                return self::responseERR($detailsCode, $message);
            }

            $verifiedResult = $this->verifyCodeFunction($userId, $code, VerifiedCode::REGISTER_TYPE, $channel);
            if ($verifiedResult !== true) {
                return self::responseObject($verifiedResult);
            }
            $dataCreate = [
                User::COL_NAME => $name,
                User::COL_PASSWORD => bcrypt($password),
                User::COL_STATUS => User::ACTIVE_STATUS,
                User::COL_ROLE_ID => User::CUSTOMER_ROLE_ID,
            ];
            if ($channel == VerifiedCode::EMAIL_CHANNEL) {
                $dataCreate[User::COL_EMAIL] = $userId;
            } else {
                $dataCreate[User::COL_PHONE] = $userId;
            }

            $user = User::create($dataCreate);
            if (!$user) {
                return self::responseERR(UM::REGISTER_FAILED, UM::M_REGISTER_FAILED);
            }
            $tokenObj = $this->getToken($userId, $password);
            $data[User::ACCESS_TOKEN] = $tokenObj->access_token;
            return self::responseST(UM::REGISTER_SUCCESS, UM::M_REGISTER_SUCCESS, $data);
        } catch (Exception $ex) {
            return self::responseEX(UM::EXW_REGISTERING, $ex->getMessage());
        }
    }

    public function sendCodeTo(Request $request)
    {
        try {
            $input = $request->all();
            $validator = VerifiedCode::validator($input);
            if ($validator->fails()) {
                return self::responseIER($validator->errors()->first());
            }
            $receiver = $input[VerifiedCode::COL_RECEIVER];
            $type = $input[VerifiedCode::COL_TYPE];
            $channel = $input[VerifiedCode::COL_CHANNEL];
            $conditions = [
                VerifiedCode::COL_RECEIVER => $receiver,
                VerifiedCode::COL_TYPE => $type,
                VerifiedCode::COL_CHANNEL => $channel,
            ];
            $code = sprintf("%06d", mt_rand(1, 999999));
            if (!$response = $this->checkValidReceiverWithType($receiver, $type)) {
                return response()->json($response, 400);
            }
            // impact DB
            $verifiedCode = VerifiedCode::where($conditions)->first();
            if (!$verifiedCode) {
                $verifiedCode = VerifiedCode::create(array_merge($conditions, [VerifiedCode::COL_CODE => $code]));
                if (!$verifiedCode) {
                    return self::responseERR(UM::SEND_CODE_FAILED, UM::M_SEND_CODE_FAILED);
                }
                // send code to email or phone->
                $this->sendBy($channel, $receiver, $code);
                return self::responseST(UM::SEND_CODE_SUCCESS, UM::M_SEND_CODE_SUCESS, $code);
            }
            $timeSentCode = $verifiedCode->{VerifiedCode::COL_CREATED_AT};
            $now = new DateTime();
            $timeValid = $timeSentCode->modify('+ 30 seconds');
            if ($now < $timeValid) {
                return self::responseERR(UM::WAIT_TO_RESEND_CODE, UM::M_WAIT_TO_RESEND_CODE);
            }
            $verifiedCode->{VerifiedCode::COL_CODE} = $code;
            $verifiedCode->{VerifiedCode::COL_CREATED_AT} = Carbon::now();
            $verifiedCode->{VerifiedCode::COL_WAS_VERIFIED} = VerifiedCode::NOT_VERIFY_STATUS;
            if (!$verifiedCode->save()) {
                return self::responseERR(UM::SAVE_CODE_TO_DB_FAILED, UM::M_SAVE_CODE_TO_DB_FAILED);
            }
            $this->sendBy($channel, $receiver, $code);
            $response = [
                self::KEY_CODE => 200,
                self::KEY_DETAIL_CODE => UM::SEND_CODE_SUCCESS,
                self::KEY_DATA => $code,
                self::KEY_MESSAGE => UM::M_SEND_CODE_SUCESS,
            ];
            return self::responseST(UM::SEND_CODE_SUCCESS, UM::M_SEND_CODE_SUCESS, $code);
        } catch (Exception $ex) {
            if (str_contains($ex->getMessage(), '[HTTP 400] Unable to create record')) {
                return self::responseERR(UM::INVALID_PHONE_NUMBER, UM::M_INVALID_PHONE_NUMBER);
            }
            return self::responseEX(UM::EXW_SENDING_CODE, $ex->getMessage());
        }
    }

    private function sendBy($type, $receiver, $code)
    {
        $message = "(TheCutSpa) $code is your authentication code. The code will expire in 5 minnutes";
        if ($type == VerifiedCode::EMAIL_CHANNEL) {
            $details = [
                'code' => $code,
            ];
            \Mail::to($receiver)->send(new \App\Mail\VerificationMail($details));
            return;
        }
        $this->sendMessage($message, $receiver);
    }

    private function checkValidReceiverWithType(string $receiver, int $type)
    {
        $isExistUser = (bool) User::where(User::COL_EMAIL, $receiver)->orWhere(User::COL_PHONE, $receiver)
            ->first();
        if ($isExistUser and $type === self::TYPE_REGISTER) {
            $response = [
                self::KEY_CODE => 400,
                self::KEY_DETAIL_CODE => UM::PHONE_OR_EMAIL_DUPLICATED,
                self::KEY_MESSAGE => UM::M_PHONE_OR_EMAIL_DUPLICATED,
            ];
            return $response;
        } elseif (!$isExistUser and $type === self::TYPE_FORGOT_PASSWORD) {
            $response = [
                self::KEY_CODE => 400,
                self::KEY_DETAIL_CODE => UM::PHONE_NUMBER_EXIST,
                self::KEY_MESSAGE => UM::M_PHONE_NUMBER_EXIST,
            ];
            return $response;
        }
        return true;
    }

    /**
     * Sends sms to user using Twilio's programmable sms client
     * @param String $message Body of sms
     * @param String $recipients string or array of phone number of recepient
     */
    private function sendMessage($message, $recipients)
    {
        $account_sid = getenv("TWILIO_SID");
        $auth_token = getenv("TWILIO_AUTH_TOKEN");
        $twilio_number = getenv("TWILIO_NUMBER");
        $client = new Client($account_sid, $auth_token);
        return $client->messages->create(
            $recipients,
            ['from' => $twilio_number, 'body' => $message]
        );
    }

    /**
     * @functionName: login
     * @type:         public
     * @param:        Request $request
     * @return:       String(Json)
     */
    public function login(Request $request)
    {
        $userId = $request->{User::VAL_USER_ID};
        $password = $request->{User::COL_PASSWORD};

        $validator = Validator::make(
            [
                User::VAL_USER_ID => $userId,
                User::COL_PASSWORD => $password,
            ],
            [
                User::VAL_USER_ID => 'required',
                User::COL_PASSWORD => 'required',
            ]
        );
        if ($validator->fails()) {
            return self::responseIER(UM::M_MUST_ENTER_FIELDS_WHEN_LOGIN, UM::MUST_ENTER_FIELDS_WHEN_LOGIN);
        }

        if (!$this->checkLogin($userId, $password)) {
            return self::responseERR(UM::WRONG_FIELD_WHEN_LOGIN, UM::M_WRONG_FIELD_WHEN_LOGIN);
        }
        try {
            $loginedUser = Auth::user();
            $data = [];
            $tokenObj = $this->getToken($userId, $password);
            $data[self::KEY_TOKEN] = $tokenObj->access_token;
            $data[self::KEY_TOKEN_EXPIRE_IN] = $tokenObj->expires_in;
            $data[self::KEY_REFRESH_TOKEN] = $tokenObj->refresh_token;
            $data[self::KEY_REFRESH_TOKEN_EXPIRE_IN] = Carbon::now()->addDay(30)->diffInSeconds();

            $data['user'] = $loginedUser;
            return self::responseST(UM::LOGIN_SUCCESS, UM::M_LOGIN_SUCCESS, $data);
        } catch (Exception $ex) {
            return self::responseEX(UM::EXW_LOGIN, $ex->getMessage());
        }
    }

    private function checkLogin($userId, $password)
    {
        $userWithEmail = User::where(User::COL_EMAIL, $userId)->first();
        if ($userWithEmail) {
            return Auth::attempt([User::COL_EMAIL => $userId, User::COL_PASSWORD => $password]);
        }
        $userWithPhone = User::where(User::COL_PHONE, $userId)->first();
        if ($userWithPhone) {
            return Auth::attempt([User::COL_PHONE => $userId, User::COL_PASSWORD => $password]);
        }
        return false;
    }

    private function getToken($email, $password)
    {
        $client = DB::table('oauth_clients')
            ->where('password_client', true)
            ->first();
        $data = [
            'grant_type' => 'password',
            'username' => $email,
            'password' => $password,
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'scope' => ''
        ];
        $request = Request::create('/oauth/token', 'POST', $data);
        $content = json_decode(app()->handle($request)->getContent());

        return $content;
    }

    /**
     * @functionName: login
     * @type:         public
     * @param:        Empty
     * @return:       String(Json)
     */
    public function logout()
    {
        try {
            Auth::user()->token()->revoke() ?? null;
            return self::responseST(UM::LOGOUT_SUCCESS, UM::M_LOGOUT_SUCCESS);
        } catch (Exception $ex) {
            return self::responseEX(UM::EXW_LOGOUT, $ex->getMessage());
        }
    }

    /**
     * @functionName: changePassword
     * @type:         public
     * @param:        Request $request
     * @return:       String(Json)
     */
    public function changePassword(Request $request)
    {
        try {
            $currentPassword = $request->{User::VAL_CURRENT_PASSWORD};
            $newPassword = $request->{User::VAL_NEW_PASSWORD};
            $confirmNewPassword = $request->{User::VAL_CONFIRM_NEW_PASSWORD};

            $validate = User::validator([
                User::VAL_CURRENT_PASSWORD => $currentPassword,
                User::VAL_NEW_PASSWORD => $newPassword,
                User::VAL_CONFIRM_NEW_PASSWORD => $confirmNewPassword,
            ]);

            if ($validate->fails()) {
                return self::responseIER($validate->errors()->first());
            }

            if (!Hash::check($currentPassword, Auth::user()->{User::COL_PASSWORD})) {
                return self::responseERR(UM::WRONG_CURRENT_PASSWORD, UM::M_WRONG_CURRENT_PASSWORD);
            }

            $currentUser = Auth::user();
            $currentUser->{User::COL_PASSWORD} = bcrypt($newPassword);

            if (!$currentUser->save()) {
                return self::responseERR(UM::CHANGE_PASSWORD_FAILED, UM::M_CHANGE_PASSWORD_FAILED);
            }
            return self::responseST(UM::CHANGE_PASSWORD_SUCCESS, UM::M_CHANGE_PASSWORD_SUCCESS);
        } catch (Exception $ex) {
            return self::responseEX(UM::EXW_CHANGING_PASSWORD, $ex->getMessage());
        }
    }

    /**
     * @functionName: resetPassword
     * @type:         public
     * @param:        Request $request
     * @return:       String(Json)
     */
    public function resetPassword(Request $request)
    {
        try {
            $receiver = $request->{User::VAL_RECEIVER};
            $code = $request->{User::VAL_CODE};
            $channel = $request->{User::VAL_CHANNEL};

            $newPassword = $request->{User::VAL_NEW_PASSWORD};
            $confirmNewPassword = $request->{User::VAL_CONFIRM_NEW_PASSWORD};

            $validate = User::validator([
                User::VAL_CODE => $code,
                User::VAL_NEW_PASSWORD => $newPassword,
                User::VAL_CONFIRM_NEW_PASSWORD => $confirmNewPassword,
                User::VAL_RECEIVER => $receiver,
            ], $channel);

            if ($validate->fails()) {
                return self::responseIER($validate->errors()->first());
            }
            $rs = $this->verifyCodeFunction($receiver, $code, VerifiedCode::RESET_PASSWORD_TYPE, $channel);
            if ($rs !== true) {
                return self::responseObject($rs);
            }

            $userNameType = User::COL_PHONE;
            if ($channel === VerifiedCode::EMAIL_CHANNEL) {
                $userNameType = User::COL_EMAIL;
            }

            $user = User::where($userNameType, $receiver)->first();
            $user->{User::COL_PASSWORD} = bcrypt($newPassword);

            if (!$user->save()) {
                return self::responseERR(UM::RESET_PASSWORD_FAILED, UM::M_RESET_PASSWORD_FAILED);
            }
            return self::responseST(UM::RESET_PASSWORD_SUCCESS, UM::M_RESET_PASSWORD_SUCCESS);
        } catch (Exception $ex) {
            return self::responseEX(UM::EXW_RESETING_PASSWORD, $ex->getMessage());
        }
    }

    private function verifyCodeFunction($receiver, $code, $type, $channel)
    {
        $conditions = [
            VerifiedCode::COL_RECEIVER => $receiver,
            VerifiedCode::COL_TYPE => $type,
            VerifiedCode::COL_CHANNEL => $channel,
            VerifiedCode::COL_CODE => $code,
        ];
        $existedCode = VerifiedCode::where($conditions)->first();
        if (!$existedCode) {
            $response = [
                self::KEY_CODE => 400,
                self::KEY_DETAIL_CODE => UM::WRONG_CODE,
                self::KEY_MESSAGE => UM::M_WRONG_CODE,
            ];
            return $response;
        }
        $timeSentCode = $existedCode->{VerifiedCode::COL_CREATED_AT};
        $timeValidForVerification = $timeSentCode->modify('+ 5 minutes');
        $now = new DateTime();
        if ($now > $timeValidForVerification) {
            $response = [
                self::KEY_CODE => 400,
                self::KEY_DETAIL_CODE => UM::EXPIRED_CODE,
                self::KEY_MESSAGE => UM::M_EXPIRED_CODE,
            ];
            return $response;
        }
        $existedCode->{VerifiedCode::COL_WAS_VERIFIED} = VerifiedCode::VERIFIED_STATUS;
        if (!$existedCode->save()) {
            $response = [
                self::KEY_CODE => 400,
                self::KEY_DETAIL_CODE => UM::VERIFY_CODE_FAILED,
                self::KEY_MESSAGE => UM::M_VERIFY_CODE_FAILED,
            ];
            return $response;
        }
        return true;
    }

    /**
     * @functionName: getProfile
     * @type:         public
     * @param:        empty
     * @return:       String(Json)
     */
    public function getProfile()
    {
        try {
            $currentUser= Auth::user();

            return self::responseST(UM::GET_USER_PROFILE_SUCCESS, UM::M_GET_USER_PROFILE_SUCCESS, $currentUser);
        } catch (Exception $ex) {
            return self::responseEX(UM::EXW_GETTING_USER_PROFILE, $ex->getMessage());
        }
    }

    /**
     * @functionName: updateProfile
     * @type:         public
     * @param:        Request $request
     * @return:       String(Json)
     */
    public function updateProfile(Request $request)
    {
        try {
            $data = [
                User::COL_NAME => $request->{User::COL_NAME},
                User::COL_GENDER => $request->{User::COL_GENDER},
                User::COL_BIRTHDAY => $request->{User::COL_BIRTHDAY},
            ];
            $validate = User::validator($data);
            if ($validate->fails()) {
                return self::responseIER($validate->errors()->first());
            }
            DB::beginTransaction();
            $currentUser = Auth::user();
            $currentUser->{User::COL_NAME} = $data[User::COL_NAME];
            $currentUser->{User::COL_GENDER} = $data[User::COL_GENDER];
            $currentUser->{User::COL_BIRTHDAY} = $data[User::COL_BIRTHDAY];
            $rs1 = $currentUser->save();

            if (!$rs1) {
                DB::rollBack();
                return self::responseERR(UM::UPDATE_USER_PROFILE_FAILED, UM::M_UPDATE_USER_PROFILE_FAILED);
            }
            DB::commit();
            return self::responseST(UM::UPDATE_USER_PROFILE_SUCCESS, UM::M_UPDATE_USER_PROFILE_SUCCESS, $currentUser);
        } catch (Exception $ex) {
            DB::rollBack();
            return self::responseEX(UM::EXW_UPDATING_USER_PROFILE, $ex->getMessage());
        }
    }

    /**
     * @functionName: getCart
     * @type:         public
     * @param:        Empty
     * @return:       String(Json)
     */
    public function getCart()
    {
        try {
            $currentUser = Auth::user();
            $cart = $currentUser->{User::COL_CART};
            $dataResponse = [];
            if ($cart) {
                $productIds = array_keys($cart);
                $products = Product::whereIn(Product::COL_ID, $productIds)->get();
                foreach ($products as $product) {
                    $productId = $product->{Product::COL_ID};
                    $quantity = $cart[$productId];
                    $dataCart = [
                        Product::COL_ID => $productId,
                        Product::VAL_QUANTITY => $quantity,
                        Product::VAL_IMAGE => $product[Product::VAL_IMAGE],
                        Product::VAL_AMOUNT => $quantity * $product[Product::COL_PRICE],
                    ];
                    array_push($dataResponse, $dataCart);
                }
            }
            return self::responseST(UM::GET_CART_SUCCESS, UM::M_GET_CART_SUCCESS, $dataResponse);
        } catch (Exception $ex) {
            return self::responseEX(UM::EXW_GET_CART, $ex->getMessage());
        }
    }
}
