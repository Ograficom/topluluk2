<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Login security failure mode
    |--------------------------------------------------------------------------
    |
    | true: VPN/Tor risk lists cannot be verified and no last-known-good cache
    | exists => login is blocked instead of silently allowing the request.
    |
    */
    'fail_closed' => (bool) env('LOGIN_SECURITY_FAIL_CLOSED', true),
];
