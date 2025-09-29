<?php

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $routes): void {

    $routes->setRouteClass(DashedRoute::class);
    $routes->scope('/', function (RouteBuilder $builder): void {
        $builder->connect('/', ['controller' => 'Pages', 'action' => 'display', 'home']);
        $builder->connect('/pages/*', 'Pages::display');

        // Step 1: Registration page
        $builder->connect('/register', ['controller' => 'Verifications', 'action' => 'register']);
        // Step 2: Send OTP (AJAX endpoint)
        $builder->connect('/verifications/send-otp', ['controller' => 'Verifications', 'action' => 'sendOtp'])->setMethods(['POST']);
        // Step 3: Verify page (enter OTP)
        $builder->connect('/verify', ['controller' => 'Verifications', 'action' => 'verify']);
        // Step 4: Verify OTP (AJAX endpoint)
        $builder->connect('/verifications/verify-otp', ['controller' => 'Verifications', 'action' => 'verifyOtp'])->setMethods(['POST']);
        // Step 5: Success page
        $builder->connect('/success', ['controller' => 'Verifications', 'action' => 'success']);

        $builder->fallbacks();
    });
};
