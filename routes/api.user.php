<?php

Route::group(
    [],
    function () {
        Route::post('register', 'User\UserController@register')->name('register');
        Route::post('login', 'User\UserController@login')->name('login');
    }
);

Route::group(
    [
        'middleware' => ['api', 'auth:api']
    ],
    function () {
        Route::post('logout', 'User\UserController@logout')->name('logout');

        Route::post('log', 'Admin\AdminController@log')->name('log');
        Route::post('overview', 'Admin\AdminController@overview')->name('overview');
        Route::post('refrige-photo', 'Admin\AdminController@photo')->name('refrige-photO');

        Route::group(
            [
                'prefix' => 'refrige'
            ], function () {
                Route::post('addBottles', 'Refrige\RefrigeController@addBottles')->name('addbottles');

                Route::post('subBottles', 'Refrige\RefrigeController@subtractBottles')->name('subbottles');

                Route::post('transaction', 'Refrige\RefrigeController@transaction')->name('transaction');
            }
        );
    }
);
