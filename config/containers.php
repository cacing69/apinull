<?php
return [
    // Mapping of interface or class name to a concrete instance
    \Repository\UserRepository::class =>  new \Repository\UserRepository(),
    \Repository\AuthRepository::class =>  new \Repository\AuthRepository(),
];
