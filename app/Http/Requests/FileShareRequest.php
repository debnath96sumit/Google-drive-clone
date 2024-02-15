<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileShareRequest extends FilesActionRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'email' => 'required|email',
            // 'ids.*' => Rule::exists('files', 'id')->where(function($query){
            //     $query->where('created_by', '=', Auth::id());
            // })
        ]);
    }
}
