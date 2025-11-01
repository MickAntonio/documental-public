<?php

use Keycloak\Admin\KeycloakClient;

function KeycloakClient($realm=null){

    if($realm=='ENTIDADES'){
        $client = KeycloakClient::factory([
            'realm' => env('ENTIDADE_REALM_NAME'),
            'username' => env('ENTIDADE_REALM_ADMIN_USER'),
            'password' => env('ENTIDADE_REALM__ADMIN_PASSWORD'),
            'client_id' => env('ENTIDADE_REALM_CLIENT_ID'),
            'baseUri' => env('ENTIDADE_REALM_BASE_URL'),
            'verify' => false,
            'timeout' => 30,
            'connect_timeout' => 30
        ]);
    }else{
        $client = KeycloakClient::factory([
            'realm' => env('REALM_NAME'),
            'username' => env('REALM_ADMIN_USER'),
            'password' => env('REALM__ADMIN_PASSWORD'),
            'client_id' => env('REALM_CLIENT_ID'),
            'baseUri' => env('REALM_BASE_URL'),
            'verify' => false,
            'timeout' => 30,
            'connect_timeout' => 30
        ]);
    }
   

    return $client;
}

