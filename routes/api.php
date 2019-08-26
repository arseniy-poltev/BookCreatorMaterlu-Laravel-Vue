<?php

Route::group(['prefix' => '/v1', 'middleware' => ['auth:api'], 'namespace' => 'Api\V1', 'as' => 'api.'], function () {
    Route::post('change-password', 'ChangePasswordController@changePassword')->name('auth.change_password');
    Route::apiResource('rules', 'RulesController', ['only' => ['index']]);
    Route::apiResource('books', 'BooksController');
    Route::apiResource('characters', 'CharactersController');
    Route::apiResource('pages', 'PagesController');
    Route::apiResource('permissions', 'PermissionsController');
    Route::apiResource('roles', 'RolesController');
    Route::apiResource('users', 'UsersController');

    Route::get('get-characters/{bId}',   'CharactersController@getCharacters');
    Route::get('get-pages/{bId}',        'PagesController@getPages');
    // Route::get('get-pages-svg/{cId}',        'PagesController@getPagesSVG');

    Route::get('books/{bId}/get-characters-info',   'CharactersController@getCharactersInfo');
    Route::get('books/{bId}/get-pages-svg',   'PagesController@getPagesSVG');
    Route::post('books/generate-pdf',   'BooksController@generatePDF');

    Route::post('upload-page', 'PagesController@savePage');
    Route::post('characters/make-json', 'CharactersController@makeLayerJSON');
    Route::post('characters/apply-svg', 'CharactersController@applySVG');

    Route::get('characters/{id}/get-json', 'CharactersController@getLayerJSON');
    Route::get('characters/{id}/load-svg', 'CharactersController@getSVG');

    Route::post('pages/change-order', 'PagesController@changeOrder');
    Route::post('pages/change-status', 'PagesController@changeStatus');
    Route::post('pages/apply-svg', 'PagesController@applySVG');
    Route::get('pages/{id}/check-page', 'PagesController@checkPage');

    Route::get('books/{id}/send', 'BooksController@sendBookToMaterlu');

    //Route::get('get-avatar', 'CharactersController@applySVGGet');
    // Route::get('get-avatar', function () {
    //     return '';
    // });

});