<?php

namespace App\Http\Controllers;

use App\Models\CommonModel;
use App\Models\File;
use App\Models\User;
use App\Models\VerifiedCode;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Twilio\Rest\Client;

class UserController extends Controller
{
    /** Api url */
    const API_URL_LOGIN = '/authentication/login';
    const API_URL_REGISTER = '/authentication/register';
    const API_URL_SEND_CODE_TO = '/authentication/send-code-to';
    const API_URL_LOGOUT = '/authentication/logout';
    const API_URL_CHANGE_PASSWORD = '/authentication/change-password';
    const API_URL_RESET_PASSWORD = '/authentication/reset-password';
    const API_URL_GET_USER_PROFILE = '/user/get-profile';
    const API_URL_UPDATE_USER_PROFILE = '/user/update-profile';

    /** Method */
    const METHOD_LOGIN = 'login';
    const METHOD_REGISTER = 'register';
    const METHOD_SEND_CODE_TO = 'sendCodeTo';
    const METHOD_LOGOUT = 'logout';
    const METHOD_CHANGE_PASSWORD = 'changePassword';
    const METHOD_RESET_PASSWORD = 'resetPassword';
    const METHOD_GET_PROFILE = 'getProfile';
    const METHOD_UPDATE_PROFILE = 'updateProfile';

    // type of verified code
    const TYPE_REGISTER = '0';
    const TYPE_FORGOT_PASSWORD = '1';

    // Error code
    const CODE_PHONE_NUMBER_EXIST = 'ERR400001';
    const CODE_INTERNAL_ERROR_WHEN_REGISTERING = 'EX500001';
    const CODE_SEND_CODE_FAIL = 'ERR400002';
    const CODE_WAIT_TO_RESEND_CODE = 'ERR400003';
    const CODE_SAVE_CODE_TO_DB_FAIL = 'ERR400004';
    const CODE_WRONG_CODE = 'ERR400005';
    const CODE_EXPIRED_CODE = 'ERR400006';
    const CODE_VERIFY_CODE_FAIL = 'ERR400007';
    const CODE_INTERNAL_ERROR_WHEN_SENDING_CODE = 'EX500002';
    const CODE_MUST_ENTER_FIELDS_WHEN_LOGIN = 'IER400002';
    const CODE_WRONG_FIELD_WHEN_LOGIN = 'ERR400008';
    const CODE_INTERNAL_ERROR_WHEN_LOGIN = 'EX500003';
    const CODE_INVALID_PHONE_NUMBER = 'ERR400009';
    const CODE_INTERNAL_ERROR_WHEN_LOGOUT = 'EX500004';
    const CODE_PHONE_OR_EMAIL_DUPLICATED = 'ERR400010';
    const CODE_PHONE_OR_EMAIL_NOT_EXIST = 'ERR400011';
    const CODE_WRONG_CURRENT_PASSWORD = 'ERR400012';
    const CODE_CHANGE_PASSWORD_FAIL = 'ERR400013';
    const CODE_INTERNAL_ERROR_WHEN_CHANGING_PASSWORD = 'EX500005';
    const CODE_RET_PASSWORD_FAIL = 'ERR400014';
    const CODE_INTERNAL_ERROR_WHEN_RESETING_PASSWORD = 'EX500006';
    const CODE_EMAIL_ADDRESS_EXIST = 'ERR400015';
    const CODE_REGISTER_FAIL = 'ERR400016';
    const CODE_INTERNAL_ERROR_WHEN_GETTING_USER_PROFILE = 'EX500007';
    const CODE_UPDATE_USER_PROFILE_FAIL = 'ERR400023';
    const CODE_INTERNAL_ERROR_WHEN_UPDATING_USER_PROFILE = 'EX500008';

    // Error message
    const MESSAGE_PHONE_NUMBER_EXIST = 'Phone number does exist.';
    const MESSAGE_SEND_CODE_FAIL = 'Send verified code failed.';
    const MESSAGE_WAIT_TO_RESEND_CODE = 'Wait 30 seconds to resend verification code.';
    const MESSAGE_SAVE_CODE_TO_DB_FAIL = 'Save verified code to DB failed.';
    const MESSAGE_WRONG_CODE = 'Wrong verification code.';
    const MESSAGE_EXPIRED_CODE = 'Code was expired, resend please.';
    const MESSAGE_VERIFY_CODE_FAIL = 'Verify code failed.';
    const MESSAGE_MUST_ENTER_FIELDS_WHEN_LOGIN = 'Required phone numer or email address and password.';
    const MESSAGE_WRONG_FIELD_WHEN_LOGIN = 'Phone number/Email address or password was wrong.';
    const MESSAGE_INVALID_PHONE_NUMBER = 'Invalid phone number - cannot send code.';
    const MESSAGE_PHONE_OR_EMAIL_DUPLICATED = 'Email or Phone was duplicated.';
    const MESSAGE_PHONE_OR_EMAIL_NOT_EXIST = 'Email or Phone does not exist.';
    const MESSAGE_WRONG_CURRENT_PASSWORD = 'Wrong current passworded.';
    const MESSAGE_CHANGE_PASSWORD_FAIL = 'Change password failed.';
    const MESSAGE_RESET_PASSWORD_FAIL = 'Reset password failed.';
    const MESSAGE_EMAIL_ADDRESS_EXIST = 'Email address does exist.';
    const MESSAGE_REGISTER_FAIL = 'Register failed.';
    const MESSAGE_UPDATE_USER_PROFILE_FAIL = 'Update user profile failed.';

    // Successful code
    const CODE_REGISTER_SUCCESS = 'ST200001';
    const CODE_SEND_CODE_SUCCESS = 'ST200002';
    const CODE_LOGIN_SUCCESS = 'ST200003';
    const CODE_LOGOUT_SUCCESS = 'ST200004';
    const CODE_CHANGE_PASSWORD_SUCCESS = 'ST200005';
    const CODE_RESET_PASSWORD_SUCCESS = 'ST200006';
    const CODE_GET_USER_PROFILE_SUCCESS = 'ST200007';
    const CODE_UPDATE_USER_PROFILE_SUCCESS = 'ST200008';

    // Successful message
    const MESSAGE_REGISTER_SUCCESS = 'Register successfully.';
    const MESSAGE_SEND_CODE_SUCESS = 'Send verified code successfully.';
    const MESSAGE_LOGIN_SUCCESS = 'Login successfully.';
    const MESSAGE_LOGOUT_SUCCESS = 'Logout successfully.';
    const MESSAGE_CHANGE_PASSWORD_SUCCESS = 'Change password successfully.';
    const MESSAGE_RESET_PASSWORD_SUCCESS = 'Reset password successfully.';
    const MESSAGE_GET_USER_PROFILE_SUCCESS = 'Get user profile successfully.';
    const MESSAGE_UPDATE_USER_PROFILE_SUCCESS = 'Update user profile successfully.';

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
                $response = [
                    self::KEY_CODE => 400,
                    self::KEY_DETAIL_CODE => self::CODE_INVALID_FIELD,
                    self::KEY_MESSAGE => $validator->errors()->first(),
                ];
                return response()->json($response, 400);
            }
            $existUser = User::where(User::COL_PHONE, $userId)
                ->orWhere(User::COL_EMAIL, $userId)->first();
            if ($existUser) {
                $detailsCode = self::CODE_PHONE_NUMBER_EXIST;
                $message = self::MESSAGE_PHONE_NUMBER_EXIST;
                if ($channel === VerifiedCode::EMAIL_CHANNEL) {
                    $detailsCode = self::CODE_EMAIL_ADDRESS_EXIST;
                    $message = self::MESSAGE_EMAIL_ADDRESS_EXIST;
                }
                $response = [
                    self::KEY_CODE => 400,
                    self::KEY_DETAIL_CODE => $detailsCode,
                    self::KEY_MESSAGE => $message,
                ];
                return response()->json($response, 400);
            }

            $verifiedResult = $this->verifyCodeFunction($userId, $code, VerifiedCode::REGISTER_TYPE, $channel);
            if ($verifiedResult !== true) {
                return response()->json($verifiedResult, $verifiedResult[self::KEY_CODE]);
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
                $response = [
                    self::KEY_CODE => 400,
                    self::KEY_DETAIL_CODE => self::CODE_REGISTER_FAIL,
                    self::KEY_MESSAGE => self::MESSAGE_REGISTER_FAIL,
                ];
                return response()->json($response, 400);
            }
            $tokenObj = $this->getToken($userId, $password);
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
                $this->sendBy($channel, $receiver, $code);
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
            $this->sendBy($channel, $receiver, $code);
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
                self::KEY_DETAIL_CODE => self::CODE_PHONE_OR_EMAIL_DUPLICATED,
                self::KEY_MESSAGE => self::MESSAGE_PHONE_OR_EMAIL_DUPLICATED,
            ];
            return $response;
        } elseif (!$isExistUser and $type === self::TYPE_FORGOT_PASSWORD) {
            $response = [
                self::KEY_CODE => 400,
                self::KEY_DETAIL_CODE => self::CODE_PHONE_NUMBER_EXIST,
                self::KEY_MESSAGE => self::MESSAGE_PHONE_NUMBER_EXIST,
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
            $response = [
                self::KEY_CODE => 400,
                self::KEY_DETAIL_CODE => self::CODE_MUST_ENTER_FIELDS_WHEN_LOGIN,
                self::KEY_MESSAGE => self::MESSAGE_MUST_ENTER_FIELDS_WHEN_LOGIN,
            ];
            return response()->json($response, 400);
        }

        if (!$this->checkLogin($userId, $password)) {
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
            $tokenObj = $this->getToken($userId, $password);
            $data[self::KEY_TOKEN] = $tokenObj->access_token;
            $data[self::KEY_TOKEN_EXPIRE_IN] = $tokenObj->expires_in;
            $data[self::KEY_REFRESH_TOKEN] = $tokenObj->refresh_token;
            $data[self::KEY_REFRESH_TOKEN_EXPIRE_IN] = Carbon::now()->addDay(30)->diffInSeconds();

            $data['user'] = $loginedUser;
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
                $response = [
                    self::KEY_CODE => 400,
                    self::KEY_DETAIL_CODE => self::CODE_INVALID_FIELD,
                    self::KEY_MESSAGE => $validate->errors()->first(),
                ];
                return response()->json($response, 400);
            }

            if (!Hash::check($currentPassword, Auth::user()->{User::COL_PASSWORD})) {
                $response = [
                    self::KEY_CODE => 400,
                    self::KEY_DETAIL_CODE => self::CODE_WRONG_CURRENT_PASSWORD,
                    self::KEY_MESSAGE => self::MESSAGE_WRONG_CURRENT_PASSWORD,
                ];
                return response()->json($response, 400);
            }

            $currentUser = Auth::user();
            $currentUser->{User::COL_PASSWORD} = bcrypt($newPassword);

            if (!$currentUser->save()) {
                $response = [
                    self::KEY_CODE => 400,
                    self::KEY_DETAIL_CODE => self::CODE_CHANGE_PASSWORD_FAIL,
                    self::KEY_MESSAGE => self::MESSAGE_CHANGE_PASSWORD_FAIL,
                ];
                return response()->json($response, 400);
            }
            $response = [
                self::KEY_CODE => 200,
                self::KEY_DETAIL_CODE => self::CODE_CHANGE_PASSWORD_SUCCESS,
                self::KEY_DATA => [],
                self::KEY_MESSAGE => self::MESSAGE_CHANGE_PASSWORD_SUCCESS,
            ];
            return response()->json($response, 200);
        } catch (Exception $ex) {
            $response = [
                self::KEY_CODE => 500,
                self::KEY_DETAIL_CODE => self::CODE_INTERNAL_ERROR_WHEN_CHANGING_PASSWORD,
                self::KEY_MESSAGE => $ex->getMessage(),
            ];
            return response()->json($response, 500);
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
                $response = [
                    self::KEY_CODE => 400,
                    self::KEY_DETAIL_CODE => self::CODE_INVALID_FIELD,
                    self::KEY_MESSAGE => $validate->errors()->first(),
                ];
                return response()->json($response, 400);
            }
            $rs = $this->verifyCodeFunction($receiver, $code, VerifiedCode::RESET_PASSWORD_TYPE, $channel);
            if ($rs !== true) {
                return response()->json($rs, $rs[self::KEY_CODE]);
            }

            $userNameType = User::COL_PHONE;
            if ($channel === VerifiedCode::EMAIL_CHANNEL) {
                $userNameType = User::COL_EMAIL;
            }

            $user = User::where($userNameType, $receiver)->first();
            $user->{User::COL_PASSWORD} = bcrypt($newPassword);

            if (!$user->save()) {
                $response = [
                    self::KEY_CODE => 400,
                    self::KEY_DETAIL_CODE => self::CODE_RET_PASSWORD_FAIL,
                    self::KEY_MESSAGE => self::MESSAGE_RESET_PASSWORD_FAIL,
                ];
                return response()->json($response, 400);
            }
            $response = [
                self::KEY_CODE => 200,
                self::KEY_DETAIL_CODE => self::CODE_RESET_PASSWORD_SUCCESS,
                self::KEY_DATA => [],
                self::KEY_MESSAGE => self::MESSAGE_RESET_PASSWORD_SUCCESS,
            ];
            return response()->json($response, 200);
        } catch (Exception $ex) {
            $response = [
                self::KEY_CODE => 500,
                self::KEY_DETAIL_CODE => self::CODE_INTERNAL_ERROR_WHEN_RESETING_PASSWORD,
                self::KEY_MESSAGE => $ex->getMessage(),
            ];
            return response()->json($response, 500);
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
                self::KEY_DETAIL_CODE => self::CODE_WRONG_CODE,
                self::KEY_MESSAGE => self::MESSAGE_WRONG_CODE,
            ];
            return $response;
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
            return $response;
        }
        $existedCode->{VerifiedCode::COL_WAS_VERIFIED} = VerifiedCode::VERIFIED_STATUS;
        if (!$existedCode->save()) {
            $response = [
                self::KEY_CODE => 400,
                self::KEY_DETAIL_CODE => self::CODE_VERIFY_CODE_FAIL,
                self::KEY_MESSAGE => self::MESSAGE_VERIFY_CODE_FAIL,
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

            $response = [
                self::KEY_CODE => 200,
                self::KEY_DETAIL_CODE => self::CODE_GET_USER_PROFILE_SUCCESS,
                self::KEY_DATA => $currentUser,
                self::KEY_MESSAGE => self::MESSAGE_GET_USER_PROFILE_SUCCESS,
            ];
            return response()->json($response, 200);
        } catch (Exception $ex) {
            $response = [
                self::KEY_CODE => 500,
                self::KEY_DETAIL_CODE => self::CODE_INTERNAL_ERROR_WHEN_GETTING_USER_PROFILE,
                self::KEY_MESSAGE => $ex->getMessage(),
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * @functionName: getProfile
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
                User::VAL_AVATAR => $request->{User::VAL_AVATAR},
            ];
            $validate = User::validator($data);
            if ($validate->fails()) {
                $response = [
                    self::KEY_CODE => 400,
                    self::KEY_DETAIL_CODE => self::CODE_INVALID_FIELD,
                    self::KEY_MESSAGE => $validate->errors()->first(),
                ];
                return response()->json($response, 400);
            }
            DB::beginTransaction();
            $currentUser = Auth::user();
            $currentUser->{User::COL_NAME} = $data[User::COL_NAME];
            $currentUser->{User::COL_GENDER} = $data[User::COL_GENDER];
            $currentUser->{User::COL_BIRTHDAY} = $data[User::COL_BIRTHDAY];
            $rs1 = $currentUser->save();

            $rs2 = ($currentUser->{User::VAL_AVATAR} = $data[User::VAL_AVATAR]);

            if (!$rs1 or !$rs2) {
                DB::rollBack();
                $response = [
                    self::KEY_CODE => 400,
                    self::KEY_DETAIL_CODE => self::CODE_UPDATE_USER_PROFILE_FAIL,
                    self::KEY_MESSAGE => self::MESSAGE_UPDATE_USER_PROFILE_FAIL,
                ];
                return response()->json($response, 400);
            }
            DB::commit();
            $response = [
                self::KEY_CODE => 200,
                self::KEY_DETAIL_CODE => self::CODE_UPDATE_USER_PROFILE_SUCCESS,
                self::KEY_DATA => $currentUser,
                self::KEY_MESSAGE => self::MESSAGE_UPDATE_USER_PROFILE_SUCCESS,
            ];
            return response()->json($response, 200);
        } catch (Exception $ex) {
            DB::rollBack();
            $response = [
                self::KEY_CODE => 500,
                self::KEY_DETAIL_CODE => self::CODE_INTERNAL_ERROR_WHEN_UPDATING_USER_PROFILE,
                self::KEY_MESSAGE => $ex->getMessage(),
            ];
            return response()->json($response, 500);
        }
    }
}
