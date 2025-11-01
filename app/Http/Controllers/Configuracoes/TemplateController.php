<?php

namespace App\Http\Controllers\Configuracoes;

use App\Models\Template;
use App\Http\Controllers\Controller;
use App\Http\Requests\TemplateRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class TemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $template = Template::where('id', '>', 0);

            if (request('nome') && !isNullOrEmpty(request('nome'))) {
                $template->where('nome', 'like', '%' . request('nome') . '%');
            }

            if (request('activo') && !isNullOrEmpty(request('activo'))) {
                $template->where('estado', request('estado'));
            }

            if (request('descricao') && !isNullOrEmpty(request('descricao'))) {
                $template->where('descricao', 'like', '%' . request('descricao') . '%');
            }

            if (request('dtInitial') && !isNullOrEmpty(request('dtInitial')) && request('dtEnd') && !isNullOrEmpty(request('dtEnd'))) {
                $from = date_format(date_create(request('dtInitial') . '00:00:00'), 'Y-m-d H:i:s');
                $to = date_format(date_create(request('dtEnd') . '23:59:59'), 'Y-m-d H:i:s');
                $template->whereBetween('created_at', [$from, $to]);
            }

            if (request('value') && request('column') && !isNullOrEmpty(request('value')) && !isNullOrEmpty(request('column'))) {
                $template->where(request('column'), 'like', '%' . request('value') . '%');
            }

            if (request('order') && !isNullOrEmpty(request('order'))) {
                if (request('sort') && !isNullOrEmpty(request('sort'))) {
                    $template->orderBy(request('sort'), request('order'));
                }
            }

            $template = $template->paginate(request('size'), ['*'], 'page', request('page') + 1);

            return response()->json(repage($template))->setStatusCode(Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()],  Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\TemplateRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TemplateRequest $request)
    {
        try {

            $template = new Template();
            $template->nome      = $request->nome;
            $template->activo    = $request->activo;
            $template->descricao = $request->descricao;
            $template->template  = $request->template;
            $template->save();


            return response()->json(['data' => $template])->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador ' . $th], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  $id $template
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $template = Template::find($id);

            if ($template != null) {
                return response()->json(['data' => $template], Response::HTTP_OK);
            } else {
                return response()->json(['message' => 'Template não encotrado'], Response::HTTP_NOT_FOUND);
            }
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\TemplateRequest  $request
     * @param  $id $template
     * @return \Illuminate\Http\Response
     */
    public function update(TemplateRequest $request, $id)
    {
        try {
            $template = Template::find($id);

            if ($template != null) {
                $template->nome      = $request->nome;
                $template->activo    = $request->activo;
                $template->descricao = $request->descricao;
                $template->template  = $request->template;
                $template->save();

                return response()->json(['data' => $template], Response::HTTP_CREATED);
            } else {
                return response()->json(['message' => 'Template não encotrado'], Response::HTTP_NOT_FOUND);
            }
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador ' . $th], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  $id $template
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {

            $validator = Validator::make(['id' => $id], [
                'id'  => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $template = template::find($id);

            if ($template == null) {
                return response()->json(['message' => 'Template não encontrado'], Response::HTTP_NOT_FOUND);
            }

            $template->delete();
            return response()->json(['message' => 'Template excluido'], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
