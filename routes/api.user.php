<?php

Route::group(
    [],
    function () {
        Route::post('register', 'User\UserController@register')->name('userregister');
        Route::post('login', 'User\UserController@login')->name('userlogin');
    }
);

Route::group(
    [
        'middleware' => ['api', 'auth:api']
    ],
    function () {
        Route::post('logout', 'User\UserController@logout')->name('logout');

        Route::group(
            [
                'prefix' => 'refrige'
            ], function () {
                Route::post('addBottles', 'Refrige\RefrigeController@addBottles')->name('addbottles');

                Route::post('subBottles', 'Refrige\RefrigeController@subtractBottles')->name('subbottles');
            }
        );
    }
);
