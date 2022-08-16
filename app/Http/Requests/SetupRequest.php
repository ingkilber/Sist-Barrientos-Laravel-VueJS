<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'database_connection' => 'required|in:mysql,pgsql,sqlsrv',
            'database_hostname' => 'required|min:3',
            'database_port' => 'required|min:3',
            'database_name' => 'required',
            'database_username' => 'required',
            'database_password' => 'required',
            'code' => 'required|min:3'
        ];
    }
}
