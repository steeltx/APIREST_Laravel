<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TransformInput
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $transformer)
    {
        $transformerInput = [];
        foreach($request->request->all() as $input => $value)
        {
            $transformerInput[$transformer::originalAttribute($input)] = $value;
        }

        $request->replace($transformerInput);

        $response = $next($request);

        if(isset($response->exception) && $response->exception instanceof ValidationException){
            $data = $response->getData();

            $transformerErros = [];
            foreach($data->error as $field => $error){
                $transformerField = $transformer::transformedAttribute($field);
                $transformerErros[$transformerField] = str_replace($field, $transformerField, $error);
            }
            $data->error = $transformerErros;
            $response->setData($data);
        }
        return $response;
    }
}
