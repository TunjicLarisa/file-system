<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFileRequest extends FormRequest
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

                Rule::unique('files', 'name')
                    ->where(function ($query) {
                        if ($this->folder_id === null) {
                            return $query
                                ->whereNull('folder_id')
                                ->whereNull('deleted_at');
                        }

                        return $query
                            ->where('folder_id', $this->folder_id)
                            ->whereNull('deleted_at');
                    }),
            ],

            'folder_id' => [
                'nullable',

                Rule::exists('folders', 'id')
                    ->whereNull('deleted_at'),
            ],
        ];
    }
}