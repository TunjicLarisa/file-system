<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchFilesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $query = $this->input('q');

        if (is_string($query)) {
            $this->merge([
                'q' => trim($query),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q' => [
                'required',
                'string',
                'max:255',
            ],

            'all' => [
                'sometimes',
                'boolean',
            ],

            'folder_id' => [
                'nullable',
                'integer',
                'exists:folders,id',
            ],
        ];
    }
}
