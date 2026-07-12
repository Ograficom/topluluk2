@extends('error', [
    'title' => __('Not Found'),
    'errorTitle' => __('Oops! Page not found'),
    'errorMsg' => __("The page you're looking for doesn't exist."),
    'errorCode' => '404',
    'homeLink' => true,
])
