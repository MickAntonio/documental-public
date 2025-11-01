<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class EntidadeUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $query = [];

            if (request('search') && !isNullOrEmpty(request('search'))) {
                $query['search'] = request('search');
            }

            $users = KeycloakClient()->getUsers($query);

            return response()->json(['data' => $users])->setStatusCode(Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo ocorreu mal, por favor contacte o administrador ' . $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'username' => 'required|string|max:50',
                'email' => 'required|email|max:50',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return  response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $response = KeycloakClient()->createUser([
                'username' => $request->username,
                'email' => $request->email,
                'firstName' => $request->firstName,
                'lastName' => $request->lastName,
                'enabled' => $request->enabled,
                'credentials' => [
                    [
                        'type' => 'password',
                        'value' => $request->password,
                        'temporary' => true
                    ],
                ],
                'attributes' => [
                    'phone_number' => $request->phone_number,
                    'locale' => 'portuguese',
                    'avatar' => ''
                ],
                'groups' => $request->groups
            ]);

            $userInfo = KeycloakClient()->getUsers(['username' => $request->username, 'email' => $request->email])[0];

            KeycloakClient()->addGlobalRolesToUser([
                'id' => $userInfo['id'],
                'roles' => $request->roles
            ]);

            // Save keycloak user to local database
            $user = new User;
            $user->id = $userInfo['id'];
            $user->name = $userInfo['username'];
            $user->email = $userInfo['email'];
            $user->phone_number = $request->phone_number;
            $user->save();

            if (isset($response['content'])) {
                return response()->json(['message' => 'Utilizador "' . $request->username . '" adicionado com successo!'])->setStatusCode(Response::HTTP_CREATED);
            }

            return response()->json(['message' => $response['errorMessage']])->setStatusCode(Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo ocorreu mal, por favor contacte o administrador' . $th], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TipoDocumentos  $tipoDocumentos
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {

            $user = KeycloakClient()->getUser(['id' => $id]);

            if (isset($user['error'])) {
                return response()->json(['message' => 'Utilizador não foi econtrado'])->setStatusCode(Response::HTTP_NOT_FOUND);
            }

            return response()->json(['data' => $user])->setStatusCode(Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo ocorreu mal, por favor contacte o administrador'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TipoDocumentos  $tipoDocumentos
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {

            $validator = Validator::make($request->all(), [
                'username' => 'required|string|max:50',
                'email' => 'required|email|max:50'
            ]);

            if ($validator->fails()) {
                return  response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $response = KeycloakClient()->updateUser([
                'id' => $id,
                'username' => $request->username,
                'email' => $request->email,
                'firstName' => $request->firstName,
                'lastName' => $request->lastName,
                'enabled' => true,
                'attributes' => [
                    'phone_number' => $request->phone_number,
                    'locale' => 'portuguese',
                    'avatar' => ''
                ],
                'roles' => $request->roles
            ]);

            if (isset($request->groups)) {
                $userGroups = KeycloakClient()->getUserGroups(['id' => $id]);

                foreach ($userGroups as $value) {
                    KeycloakClient()->deleteUserFromGroup([
                        'id' => $id,
                        'groupId' => $value['id']
                    ]);
                }

                foreach ($request->groups as $value) {
                    KeycloakClient()->addUserToGroup([
                        'id' => $id,
                        'groupId' => $value
                    ]);
                }
            }

            if (isset($request->roles)) {

                $userRoles = collect(KeycloakClient()->getUserRoleMappings(['id' => $id]))->whereNotIn(
                    'name',
                    [
                        'uma_authorization',
                        'default-roles-documental',
                        'offline_access'
                    ]
                )->values();

                if (isset($userRoles['realmMappings'])) {

                    KeycloakClient()->deleteUserRealmRoleMappings([
                        'id' => $id,
                        'roles' => $userRoles['realmMappings']
                    ]);
                }

                KeycloakClient()->addGlobalRolesToUser([
                    'id' => $id,
                    'roles' => $request->roles
                ]);
            }

            // Update or Create if not exist keycloak user on local database
            $user = User::where('id', $id)->first();

            if($user==null){ 
                $user = new User; 
                $user->id = $id; 
            }
            
            $user->email = $request->email;
            $user->name = $request->username;
            $user->phone_number = $request->phone_number;
            $user->save();

            if (isset($response['content'])) {
                return response()->json(['message' => 'Utilizador "' . $request->username . '" actualizado com successo!'])->setStatusCode(Response::HTTP_CREATED);
            }

            return response()->json(['message' => $response['errorMessage']])->setStatusCode(Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo ocorreu mal, por favor contacte o administrador' . $th], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TipoDocumentos  $tipoDocumentos
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {

            $validator = Validator::make(['id' => $id], [
                'id'  => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $user = KeycloakClient()->getUser(['id' => $id]);

            if (isset($user['error'])) {
                return response()->json(['message' => 'Utilizador não foi econtrado'])->setStatusCode(Response::HTTP_NOT_FOUND);
            }

            KeycloakClient()->deleteUser(['id' => $id]);

            return response()->json(['data' => 'Utilizador excluído com sucesso!'])->setStatusCode(Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo ocorreu mal, por favor contacte o administrador'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
