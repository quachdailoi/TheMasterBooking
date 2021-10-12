<?php

namespace App\CodeAndMessage;

class ProductMessage
{
    // error code
    const NOT_FOUND_CATEGORY = 'ERR400xxx';
    const NOT_FOUND_PRODUCT = 'ERR400xxx';
    const ADD_TO_CART_FAILED = 'ERR400xxx';
    const CREATE_PRODUCT_FAILED = 'ERR400xxx';
    const UPDATE_PRODUCT_FAILED = 'ERR400xxx';
    const DELETE_PRODUCT_FAILED = 'ERR400xxx';

    //internal error code
    const EXW_GET_PRODUCTS = 'EX500xxx';
    const EXW_ADD_TO_CART = 'EX500xxx';
    const EXW_CREAT_PRODUCT = 'EX500xxx';
    const EXW_UPDATE_PRODUCT = 'EX500xxx';
    const EXW_DELETE_PRODUCT = 'EX500xxx';

    // error message
    const M_NOT_FOUND_CATEGORY = 'Not found category.';
    const M_NOT_FOUND_PRODUCT = 'Not found product.';
    const M_ADD_TO_CART_FAILED = 'Add to cart failed.';
    const M_CREATE_PRODUCT_FAILED = 'Create product failed.';
    const M_UPDATE_PRODUCT_FAILED = 'Update product failed.';
    const M_DELETE_PRODUCT_FAILED = 'Delete product failed.';

    // success code
    const GET_PRODUCTS_SUCCESS = 'ST200xxx';
    const ADD_TO_CART_SUCCESS = 'ST200xxx';
    const CREATE_PRODUCT_SUCCESS = 'ST200xxx';
    const UPDATE_PRODUCT_SUCCESS = 'ST200xxx';
    const DELETE_PRODUCT_SUCCESS = 'ST200xxx';

    // success message
    const M_GET_PRODUCTS_SUCCESS = 'Get products successfully';
    const M_ADD_TO_CART_SUCCESS = 'Add to cart successfully';
    const M_CREATE_PRODUCT_SUCCESS = 'Create product successfully.';
    const M_UPDATE_PRODUCT_SUCCESS = 'Update product successfully.';
    const M_DELETE_PRODUCT_SUCCESS = 'Delete product successfully.';
}
