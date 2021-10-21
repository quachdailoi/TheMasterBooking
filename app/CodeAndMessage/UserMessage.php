<?php

namespace App\CodeAndMessage;

class UserMessage
{
    // error code
    const PHONE_NUMBER_EXIST = 'ERR400001';
    const SEND_CODE_FAILED = 'ERR400002';
    const WAIT_TO_RESEND_CODE = 'ERR400003';
    const SAVE_CODE_TO_DB_FAILED = 'ERR400004';
    const WRONG_CODE = 'ERR400005';
    const EXPIRED_CODE = 'ERR400006';
    const VERIFY_CODE_FAILED = 'ERR400007';
    const MUST_ENTER_FIELDS_WHEN_LOGIN = 'IER400002';
    const WRONG_FIELD_WHEN_LOGIN = 'ERR400008';
    const INVALID_PHONE_NUMBER = 'ERR400009';
    const PHONE_OR_EMAIL_DUPLICATED = 'ERR400010';
    const PHONE_OR_EMAIL_NOT_EXIST = 'ERR400011';
    const WRONG_CURRENT_PASSWORD = 'ERR400012';
    const CHANGE_PASSWORD_FAILED = 'ERR400013';
    const RESET_PASSWORD_FAILED = 'ERR400014';
    const EMAIL_ADDRESS_EXIST = 'ERR400015';
    const REGISTER_FAILED = 'ERR400016';
    const UPDATE_USER_PROFILE_FAILED = 'ERR400023';
    const INVALID_CART_PARAMETER = 'IER400xxx';
    const CART_HAVE_INVALID_PRODUCT_ID = 'IER400xxx';
    const CART_HAVE_INVALID_QUANTITY = 'IER400xxx';
    const UPDATE_CART_FAILED = 'ERR400xxx';

    //internal error code
    const EXW_REGISTERING = 'EX500001';
    const EXW_SENDING_CODE = 'EX500002';
    const EXW_LOGIN = 'EX500003';
    const EXW_LOGOUT = 'EX500004';
    const EXW_CHANGING_PASSWORD = 'EX500005';
    const EXW_RESETING_PASSWORD = 'EX500006';
    const EXW_GETTING_USER_PROFILE = 'EX500007';
    const EXW_UPDATING_USER_PROFILE = 'EX500008';
    const EXW_GET_CART = 'EX5000022';
    const EXW_UPDATE_CART = 'EX500xxx';

    // error message
    const M_PHONE_NUMBER_EXIST = 'Phone number does exist.';
    const M_SEND_CODE_FAILED = 'Send verified code failed.';
    const M_WAIT_TO_RESEND_CODE = 'Wait 30 seconds to resend verification code.';
    const M_SAVE_CODE_TO_DB_FAILED = 'Save verified code to DB failed.';
    const M_WRONG_CODE = 'Wrong verification code.';
    const M_EXPIRED_CODE = 'Code was expired, resend please.';
    const M_VERIFY_CODE_FAILED = 'Verify code failed.';
    const M_MUST_ENTER_FIELDS_WHEN_LOGIN = 'Required phone numer or email address and password.';
    const M_WRONG_FIELD_WHEN_LOGIN = 'Phone number/Email address or password was wrong.';
    const M_INVALID_PHONE_NUMBER = 'Invalid phone number - cannot send code.';
    const M_PHONE_OR_EMAIL_DUPLICATED = 'Email or Phone was duplicated.';
    const M_PHONE_OR_EMAIL_NOT_EXIST = 'Email or Phone does not exist.';
    const M_WRONG_CURRENT_PASSWORD = 'Wrong current passworded.';
    const M_CHANGE_PASSWORD_FAILED = 'Change password failed.';
    const M_RESET_PASSWORD_FAILED = 'Reset password failed.';
    const M_EMAIL_ADDRESS_EXIST = 'Email address does exist.';
    const M_REGISTER_FAILED = 'Register failed.';
    const M_UPDATE_USER_PROFILE_FAILED = 'Update user profile failed.';
    const M_INVALID_CART_PARAMETER = 'Invalid cart parameter.';
    const M_CART_HAVE_INVALID_PRODUCT_ID = 'Cart have invalid product id.';
    const M_CART_HAVE_INVALID_QUANTITY = 'Product quantity is invalid.';
    const M_UPDATE_CART_FAILED = 'Update cart failed.';

    // success code
    const REGISTER_SUCCESS = 'ST200001';
    const SEND_CODE_SUCCESS = 'ST200002';
    const LOGIN_SUCCESS = 'ST200003';
    const LOGOUT_SUCCESS = 'ST200004';
    const CHANGE_PASSWORD_SUCCESS = 'ST200005';
    const RESET_PASSWORD_SUCCESS = 'ST200006';
    const GET_USER_PROFILE_SUCCESS = 'ST200007';
    const UPDATE_USER_PROFILE_SUCCESS = 'ST200008';
    const GET_CART_SUCCESS = 'ST200017';
    const UPDATE_CART_SUCCESS = 'ST200xxx';

    // success message
    const M_REGISTER_SUCCESS = 'Register successfully.';
    const M_SEND_CODE_SUCESS = 'Send verified code successfully.';
    const M_LOGIN_SUCCESS = 'Login successfully.';
    const M_LOGOUT_SUCCESS = 'Logout successfully.';
    const M_CHANGE_PASSWORD_SUCCESS = 'Change password successfully.';
    const M_RESET_PASSWORD_SUCCESS = 'Reset password successfully.';
    const M_GET_USER_PROFILE_SUCCESS = 'Get user profile successfully.';
    const M_UPDATE_USER_PROFILE_SUCCESS = 'Update user profile successfully.';
    const M_GET_CART_SUCCESS = 'Get cart successfully.';
    const M_UPDATE_CART_SUCCESS = 'Update cart success';
}
