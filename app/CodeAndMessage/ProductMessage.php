<?php

namespace App\CodeAndMessage;

class ProductMessage
{
    // error code
    const NOT_FOUND_CATEGORY = 'ERR400024';
    const NOT_FOUND_PRODUCT = 'ERR400025';
    const ADD_TO_CART_FAILED = 'ERR400026';
    const CREATE_PRODUCT_FAILED = 'ERR400027';
    const UPDATE_PRODUCT_FAILED = 'ERR400028';
    const DELETE_PRODUCT_FAILED = 'ERR400029';

    //internal error code
    const EXW_GET_PRODUCTS = 'EX500012';
    const EXW_ADD_TO_CART = 'EX500013';
    const EXW_CREAT_PRODUCT = 'EX500014';
    const EXW_UPDATE_PRODUCT = 'EX500015';
    const EXW_DELETE_PRODUCT = 'EX500016';

    // error message
    const M_NOT_FOUND_CATEGORY = 'Not found category.';
    const M_NOT_FOUND_PRODUCT = 'Not found product.';
    const M_ADD_TO_CART_FAILED = 'Add to cart failed.';
    const M_CREATE_PRODUCT_FAILED = 'Create product failed.';
    const M_UPDATE_PRODUCT_FAILED = 'Update product failed.';
    const M_DELETE_PRODUCT_FAILED = 'Delete product failed.';

    // success code
    const GET_PRODUCTS_SUCCESS = 'ST200012';
    const ADD_TO_CART_SUCCESS = 'ST200013';
    const CREATE_PRODUCT_SUCCESS = 'ST200014';
    const UPDATE_PRODUCT_SUCCESS = 'ST200015';
    const DELETE_PRODUCT_SUCCESS = 'ST200016';

    // success message
    const M_GET_PRODUCTS_SUCCESS = 'Get products successfully.';
    const M_ADD_TO_CART_SUCCESS = 'Add to cart successfully.';
    const M_CREATE_PRODUCT_SUCCESS = 'Create product successfully.';
    const M_UPDATE_PRODUCT_SUCCESS = 'Update product successfully.';
    const M_DELETE_PRODUCT_SUCCESS = 'Delete product successfully.';
}
