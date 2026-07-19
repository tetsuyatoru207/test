<?php

$app->get('/booking', 'ClientBookingController@index');
$app->get('/booking/data', 'ClientBookingController@getData');
$app->post('/booking/add', 'ClientBookingController@add');
$app->post('/booking/remove', 'ClientBookingController@remove');
$app->post('/booking/process', 'ClientBookingController@process');
$app->post('/booking/cancel', 'ClientBookingController@cancel');
