<?php

namespace App\Http\Requests\PosSessionRequests;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class StorePosSessionRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            //'company_id' => ['required', 'exists:companies,id'],

            'pos_terminal_id' => ['required', 'exists:pos_terminals,id'],

        //    'opened_by_user_id' => ['required', 'exists:users,id'],

          //  'session_number' => ['required', 'string', 'max:100'],

            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
