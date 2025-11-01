<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Keycloak\Admin\KeycloakClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {

            $roles = collect(KeycloakClient()->getRealmRoles())->whereNotIn(
                'name',
                [
                    'uma_authorization',
                    'default-roles-documental',
                    'offline_access'
                ]
            )->values();

            return response()->json(['data' => $roles])->setStatusCode(Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo ocorreu mal, por favor contacte o administrador ' . $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

      /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function userRoles($id)
    {
        try {
            $groups = KeycloakClient()->getUserRoleMappings(['id'=>$id]);

            if(isset($groups['realmMappings'])){
                return response()->json(['data' => $groups['realmMappings']])->setStatusCode(Response::HTTP_OK);
            }else{
                return response()->json(['data' => []])->setStatusCode(Response::HTTP_OK);
            }
            
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo ocorreu mal, por favor contacte o administrador ' . $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
