<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->name),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',

                Rule::unique('folders', 'name')->where(function ($query) {
                    if ($this->parent_id === null) {
                        return $query->whereNull('parent_id');
                    }

                    return $query->where('parent_id', $this->parent_id);
                }),
            ],

            'parent_id' => [
                'nullable',
                'exists:folders,id',
            ],
        ];
    }
}