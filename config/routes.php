<?php

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $routes): void {

    $routes->setRouteClass(DashedRoute::class);
    $routes->scope('/', function (RouteBuilder $builder): void {
        $builder->connect('/', ['controller' => 'Pages', 'action' => 'display', 'home']);
        $builder->connect('/pages/*', 'Pages::display');

        // Verification Routes
        $builder->connect('/register', ['controller' => 'Verifications', 'action' => 'register']);
        $builder->connect('/verify', ['controller' => 'Verifications', 'action' => 'verify']);
        $builder->connect('/success', ['controller' => 'Verifications', 'action' => 'success']);

        // AJAX endpoints
        $builder->connect('/verifications/send-otp',['controller' => 'Verifications', 'action' => 'sendOtp'])->setMethods(['POST']);
        $builder->connect('/verifications/resend-otp',['controller' => 'Verifications', 'action' => 'resendOtp'])->setMethods(['POST']);

        $builder->fallbacks();
    });
};
