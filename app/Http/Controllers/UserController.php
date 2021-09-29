<?php

namespace App\Http\Controllers;

use App\Models\CommonModel;
use App\Models\User;
use App\Models\VerifiedCode;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Twilio\Rest\Client;

class UserController extends Controller
{
    /** Api url */
    const API_URL_LOGIN = '/authentication/login';
    const API_URL_REGISTER = '/authentication/register';
    const API_URL_SEND_CODE_TO = '/authentication/send-code-to';
    const API_URL_VERIFY_CODE = '/authentication/verify-code';
    const API_URL_LOGOUT = '/authentication/logout';

    /** Method */
    const METHOD_LOGIN = 'login';
    const METHOD_REGISTER = 'register';
    const METHOD_SEND_CODE_TO = 'sendCodeTo';
    const METHOD_VERIFY_CODE = 'verifyCode';
    const METHOD_LOGOUT = 'logout';

    // type of verified code
    const TYPE_REGISTER = '0';
    const TYPE_FORGOT_PASSWORD = '1';

    // Error code
    const CODE_PHONE_NUMBER_EXIST = 'ERR400001';
    const CODE_NOT_VERIFY_PHONE = 'ERR400002';
    const CODE_INTERNAL_ERROR_WHEN_REGISTERING = 'EX500001';
    const CODE_SEND_CODE_FAIL = 'ERR400003';
    const CODE_WAIT_TO_RESEND_CODE = 'ERR400004';
    const CODE_SAVE_CODE_TO_DB_FAIL = 'ERR400005';
    const CODE_WRONG_CODE = 'ERR400006';
    const CODE_EXPIRED_CODE = 'ERR400007';
    const CODE_VERIFY_CODE_FAIL = 'ERR400008';
    const CODE_INTERNAL_ERROR_WHEN_SENDING_CODE = 'EX500002';
    const CODE_INTERNAL_ERROR_WHEN_VERIFYING_CODE = 'EX500003';
    const CODE_MUST_ENTER_FIELDS_WHEN_LOGIN = 'IER400002';
    const CODE_WRONG_FIELD_WHEN_LOGIN = 'ERR400009';
    const CODE_INTERNAL_ERROR_WHEN_LOGIN = 'EX500004';
    const CODE_INVALID_PHONE_NUMBER = 'ERR400010';
    const CODE_INTERNAL_ERROR_WHEN_LOGOUT = 'EX500005';

    // Error message
    const MESSAGE_PHONE_NUMBER_EXIST = 'Phone number does exist.';
    const MESSAGE_NOT_VERIFY_PHONE = 'Phone number must be verifed before register.';
    const MESSAGE_SEND_CODE_FAIL = 'Send verified code failed.';
    const MESSAGE_WAIT_TO_RESEND_CODE = 'Wait 30 seconds to resend verification code.';
    const MESSAGE_SAVE_CODE_TO_DB_FAIL = 'Save verified code to DB failed.';
    const MESSAGE_WRONG_CODE = 'Wrong verification code.';
    const MESSAGE_EXPIRED_CODE = 'Code was expired, resend please.';
    const MESSAGE_VERIFY_CODE_FAIL = 'Verify code failed.';
    const MESSAGE_MUST_ENTER_FIELDS_WHEN_LOGIN = 'Must enter phone numer and password when login';
    const MESSAGE_WRONG_FIELD_WHEN_LOGIN = 'Phone number or password was wrong.';
    const MESSAGE_INVALID_PHONE_NUMBER = 'Invalid phone number - cannot send code';

    // Successful code
    const CODE_REGISTER_SUCCESS = 'ST200001';
    const CODE_SEND_CODE_SUCCESS = 'ST200002';
    const CODE_VERIFY_CODE_SUCESS = 'ST200003';
    const CODE_LOGIN_SUCCESS = 'ST200004';
    const CODE_LOGOUT_SUCCESS = 'ST200005';

    // Successful message
    const MESSAGE_REGISTER_SUCCESS = 'Register successfully.';
    const MESSAGE_SEND_CODE_SUCESS = 'Send verified code successfully.';
    const MESSAGE_VERIFY_CODE_SUCESS = 'Email/Phone was verfied successfully.';
    const MESSAGE_LOGIN_SUCCESS = 'Login successfully.';
    const MESSAGE_LOGOUT_SUCCESS = 'Logout successfully.';

    /**
     * @functionName: register
     * @type:         public
     * @param:        Request $request
     * @return:       String(Json)
     */
    public function register(Request $request)
    {
        try {
            $phone = $request->{User::COL_PHONE};
            $name = $request->{User::COL_NAME};
            $password = $request->{User::COL_PASSWORD};
            $confirmPassword = $request->{User::VAL_CONFIRM_PASSWORD};
            $gender = $request->{User::COL_GENDER};
            $birthDay = $request->{User::COL_BIRTHDAY};

            $validator = User::validator([
                User::COL_PHONE => $phone,
                User::COL_NAME => $name,
                User::COL_PASSWORD => $password,
                User::VAL_CONFIRM_PASSWORD => $confirmPassword,
                User::COL_GENDER => $gender,
                User::COL_BIRTHDAY => $birthDay,
            ]);
            if ($validator->fails()) {
                $response = [
                    self::KEY_CODE => 400,
                    self::KEY_DETAIL_CODE => self::CODE_INVALID_FIELD,
                    self::KEY_MESSAGE => $validator->errors()->first(),
                ];
                return response()->json($response, 400);
            }
            $existUser = User::where(User::COL_PHONE, $phone)->first();
            if ($existUser) {
                $response = [
                    self::KEY_CODE => 400,
                    self::KEY_DETAIL_CODE => self::CODE_PHONE_NUMBER_EXIST,
                    self::KEY_MESSAGE => self::MESSAGE_PHONE_NUMBER_EXIST,
                ];
                return response()->json($response, 400);
            }
            $code = VerifiedCode::where([
                VerifiedCode::COL_RECEIVER => $phone,
                VerifiedCode::COL_TYPE => VerifiedCode::REGISTER_TYPE,
                VerifiedCode::COL_CHANNEL => VerifiedCode::PHONE_CHANNEL,
            ])->first();
            if (!($code->{VerifiedCode::COL_WAS_VERIFIED} ?? null)) {
                $response = [
                    self::KEY_CODE => 400,
                    self::KEY_DETAIL_CODE => self::CODE_NOT_VERIFY_PHONE,
                    self::KEY_MESSAGE => self::MESSAGE_NOT_VERIFY_PHONE,
                ];
                return response()->json($response, 400);
            }

            $dataCreate = [
                User::COL_PHONE => $phone,
                User::COL_NAME => $name,
                User::COL_PASSWORD => bcrypt($password),
                User::COL_GENDER => $gender,
                User::COL_BIRTHDAY => $birthDay,
                User::COL_STATUS => User::ACTIVE_STATUS,
                User::COL_ROLE_ID => User::CUSTOMER_ROLE_ID,
            ];
            $user = User::create($dataCreate);
            $tokenObj = $this->getToken($user->{User::COL_PHONE}, $password);
            $data[User::ACCESS_TOKEN] = $tokenObj->access_token;
            $response = [
                self::KEY_CODE => 200,
                self::KEY_DETAIL_CODE => self::CODE_REGISTER_SUCCESS,
                self::KEY_DATA => $data,
                self::KEY_MESSAGE => self::MESSAGE_REGISTER_SUCCESS,
            ];
            return response()->json($response, 200);
        } catch (Exception $ex) {
            $response = [
                self::KEY_CODE => 500,
                self::KEY_DETAIL_CODE => self::CODE_INTERNAL_ERROR_WHEN_REGISTERING,
                self::KEY_MESSAGE => $ex->getMessage(),
            ];
            return response()->json($response, 500);
        }
    }

    public function sendCodeTo(Request $request)
    {
        try {
            $input = $request->all();
            $validator = VerifiedCode::validator($input);
            if ($validator->fails()) {
                $response = [
                    self::KEY_CODE => 400,
                    self::KEY_DETAIL_CODE => self::CODE_INVALID_FIELD,
                    self::KEY_MESSAGE => $validator->errors()->first(),
                ];
                return response()->json($response, 400);
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
            $message = "(TheCutSpa) $code is your authentication code. The code will expire in 5 minnutes";
            // impact DB
            $verifiedCode = VerifiedCode::where($conditions)->first();
            if (!$verifiedCode) {
                $verifiedCode = VerifiedCode::create(array_merge($conditions, [VerifiedCode::COL_CODE => $code]));
                if (!$verifiedCode) {
                    $response = [
                        self::KEY_CODE => 400,
                        self::KEY_DETAIL_CODE => self::CODE_SEND_CODE_FAIL,
                        self::KEY_MESSAGE => self::MESSAGE_SEND_CODE_FAIL,
                    ];
                    return response()->json($response, 400);
                }
                // send code to email or phone->
                $this->sendMessage($message, $receiver);
                $response = [
                    self::KEY_CODE => 200,
                    self::KEY_DETAIL_CODE => self::CODE_SEND_CODE_SUCCESS,
                    self::KEY_DATA => $code,
                    self::KEY_MESSAGE => self::MESSAGE_SEND_CODE_SUCESS,
                ];
                return response()->json($response, 200);
            }
            $timeSentCode = $verifiedCode->{VerifiedCode::COL_CREATED_AT};
            $now = new DateTime();
            $timeValid = $timeSentCode->modify('+ 30 seconds');
            if ($now < $timeValid) {
                $response = [
                    self::KEY_CODE => 400,
                    self::KEY_DETAIL_CODE => self::CODE_WAIT_TO_RESEND_CODE,
                    self::KEY_MESSAGE => self::MESSAGE_WAIT_TO_RESEND_CODE,
                ];
                return response()->json($response, 400);
            }
            $verifiedCode->{VerifiedCode::COL_CODE} = $code;
            $verifiedCode->{VerifiedCode::COL_CREATED_AT} = Carbon::now();
            $verifiedCode->{VerifiedCode::COL_WAS_VERIFIED} = VerifiedCode::NOT_VERIFY_STATUS;
            if (!$verifiedCode->save()) {
                $response = [
                    self::KEY_CODE => 400,
                    self::KEY_DETAIL_CODE => self::CODE_SAVE_CODE_TO_DB_FAIL,
                    self::KEY_MESSAGE => self::MESSAGE_SAVE_CODE_TO_DB_FAIL,
                ];
                return response()->json($response, 400);
            }
            $this->sendMessage($message, $receiver);
            $response = [
                self::KEY_CODE => 200,
                self::KEY_DETAIL_CODE => self::CODE_SEND_CODE_SUCCESS,
                self::KEY_DATA => $code,
                self::KEY_MESSAGE => self::MESSAGE_SEND_CODE_SUCESS,
            ];
            return response()->json($response, 200);
        } catch (Exception $ex) {
            if (str_contains($ex->getMessage(), '[HTTP 400] Unable to create record')) {
                $response = [
                    self::KEY_CODE => 400,
                    self::KEY_DETAIL_CODE => self::CODE_INVALID_PHONE_NUMBER,
                    self::KEY_MESSAGE => self::MESSAGE_INVALID_PHONE_NUMBER,
                ];
                return response()->json($response, 400);
            }
            $response = [
                self::KEY_CODE => 500,
                self::KEY_DETAIL_CODE => self::CODE_INTERNAL_ERROR_WHEN_SENDING_CODE,
                self::KEY_MESSAGE => $ex->getMessage(),
            ];
            return response()->json($response, 500);
        }
    }

    private function checkValidReceiverWithType(string $receiver, int $type)
    {
        $isExistUser = (bool) User::where(User::COL_EMAIL, $receiver)->orWhere(User::COL_PHONE, $receiver)
            ->first();
        if ($isExistUser and $type === self::TYPE_REGISTER) {
            $response = [
                self::KEY_CODE => 400,
                self::KEY_DETAIL_CODE => 'ERR400xxx',
                self::KEY_MESSAGE => 'Email or Phone was duplicated.',
            ];
            return $response;
        } elseif (!$isExistUser and $type === self::TYPE_FORGOT_PASSWORD) {
            $response = [
                self::KEY_CODE => 400,
                self::KEY_DETAIL_CODE => 'ERR400xxx',
                self::KEY_MESSAGE => 'Email or Phone does not exist.',
            ];
            return $response;
        }
        return true;
    }

    /**
     * @functionName: verifyCode
     * @type:         public
     * @param:        Request $request
     * @return:       String(Json)
     */
    public function verifyCode(Request $request)
    {
        try {
            $input = $request->all();
            $validator = VerifiedCode::validator($input);
            if ($validator->fails()) {
                $response = [
                    self::KEY_CODE => 400,
                    self::KEY_DETAIL_CODE => self::CODE_INVALID_FIELD,
                    self::KEY_MESSAGE => $validator->errors(),
                ];
                return response()->json($response, 400);
            }
            $receiver = $input[VerifiedCode::COL_RECEIVER];
            $type = $input[VerifiedCode::COL_TYPE];
            $channel = $input[VerifiedCode::COL_CHANNEL];
            $code = $input[VerifiedCode::COL_CODE];
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
                    self::KEY_DETAIL_CODE => self::CODE_WRONG_CODE,
                    self::KEY_MESSAGE => self::MESSAGE_WRONG_CODE,
                ];
                return response()->json($response, 400);
            }
            $timeSentCode = $existedCode->{VerifiedCode::COL_CREATED_AT};
            $timeValidForVerification = $timeSentCode->modify('+ 5 minutes');
            $now = new DateTime();
            if ($now > $timeValidForVerification) {
                $response = [
                    self::KEY_CODE => 400,
                    self::KEY_DETAIL_CODE => self::CODE_EXPIRED_CODE,
                    self::KEY_MESSAGE => self::MESSAGE_EXPIRED_CODE,
                ];
                return response()->json($response, 400);
            }
            $existedCode->{VerifiedCode::COL_WAS_VERIFIED} = VerifiedCode::VERIFIED_STATUS;
            if (!$existedCode->save()) {
                $response = [
                    self::KEY_CODE => 400,
                    self::KEY_DETAIL_CODE => self::CODE_VERIFY_CODE_FAIL,
                    self::KEY_MESSAGE => self::MESSAGE_VERIFY_CODE_FAIL,
                ];
                return response()->json($response, 400);
            }
            $response = [
                self::KEY_CODE => 200,
                self::KEY_DETAIL_CODE => self::CODE_VERIFY_CODE_SUCESS,
                self::KEY_DATA => [],
                self::KEY_MESSAGE => self::MESSAGE_VERIFY_CODE_SUCESS,
            ];
            return response()->json($response, 200);
        } catch (Exception $ex) {
            $response = [
                self::KEY_CODE => 500,
                self::KEY_DETAIL_CODE => self::CODE_INTERNAL_ERROR_WHEN_VERIFYING_CODE,
                self::KEY_MESSAGE => $ex->getMessage(),
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Sends sms to user using Twilio's programmable sms client
     * @param String $message Body of sms
     * @param Number $recipients string or array of phone number of recepient
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
        $phone = $request->{User::COL_PHONE};
        $password = $request->{User::COL_PASSWORD};

        $validator = Validator::make(
            [
                User::COL_PHONE => $phone,
                User::COL_PASSWORD => $password,
            ],
            [
                User::COL_PHONE => 'required',
                User::COL_PASSWORD => 'required',
            ]
        );
        if ($validator->fails()) {
            $response = [
                self::KEY_CODE => 400,
                self::KEY_DETAIL_CODE => self::CODE_MUST_ENTER_FIELDS_WHEN_LOGIN,
                self::KEY_MESSAGE => self::MESSAGE_MUST_ENTER_FIELDS_WHEN_LOGIN,
            ];
            return response()->json($response, 400);
        }

        if (!Auth::attempt([User::COL_PHONE => $phone, User::COL_PASSWORD => $password])) {
            $response = [
                self::KEY_CODE => 400,
                self::KEY_DETAIL_CODE => self::CODE_WRONG_FIELD_WHEN_LOGIN,
                self::KEY_MESSAGE => self::MESSAGE_WRONG_FIELD_WHEN_LOGIN,
            ];
            return response()->json($response, 400);
        }
        try {
            $loginedUser = Auth::user();
            $data = [];
            $tokenObj = $this->getToken($loginedUser->{User::COL_PHONE}, $password);
            $data[self::KEY_TOKEN] = $tokenObj->access_token;
            $data[self::KEY_TOKEN_EXPIRE_IN] = $tokenObj->expires_in;
            $data[self::KEY_REFRESH_TOKEN] = $tokenObj->refresh_token;
            $data[self::KEY_REFRESH_TOKEN_EXPIRE_IN] = Carbon::now()->addDay(30)->diffInSeconds();

            $userData = [
                User::COL_NAME => $loginedUser->{User::COL_NAME},
                User::COL_EMAIL => $loginedUser->{User::COL_EMAIL},
                User::COL_PHONE => $loginedUser->{User::COL_PHONE},
                User::COL_ROLE_ID => $loginedUser->{User::COL_ROLE_ID},
            ];

            $data['user'] = $userData;
            $response = [
                self::KEY_CODE => 200,
                self::KEY_DETAIL_CODE => self::CODE_LOGIN_SUCCESS,
                self::KEY_DATA => $data,
                self::KEY_MESSAGE => self::MESSAGE_LOGIN_SUCCESS,
            ];
            return response()->json($response, 200);
        } catch (Exception $ex) {
            $response = [
                self::KEY_CODE => 500,
                self::KEY_DETAIL_CODE => self::CODE_INTERNAL_ERROR_WHEN_LOGIN,
                self::KEY_MESSAGE => $ex->getMessage(),
            ];
            return response()->json($response, 500);
        }
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

            $response = [
                self::KEY_CODE => 200,
                self::KEY_DETAIL_CODE => self::CODE_LOGOUT_SUCCESS,
                self::KEY_DATA => [],
                self::KEY_MESSAGE => self::MESSAGE_LOGOUT_SUCCESS,
            ];
            return response()->json($response, 200);
        } catch (Exception $ex) {
            $response = [
                self::KEY_CODE => 500,
                self::KEY_DETAIL_CODE => self::CODE_INTERNAL_ERROR_WHEN_LOGOUT,
                self::KEY_MESSAGE => $ex->getMessage(),
            ];
            return response()->json($response, 500);
        }
    }
}
