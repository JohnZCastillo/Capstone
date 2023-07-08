<?php

namespace App\controller;

class UserController {

    public function home($request, $response, $args) {
        
        $response->getBody()->write('hello beshy');
    
        return $response;
    }

 
}
