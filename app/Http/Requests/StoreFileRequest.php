<?php

namespace App\Http\Requests;

use App\Models\File;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreFileRequest extends ParentIdBaseRequest
{
    protected function prepareForValidation()
    {
        $paths = array_filter($this->relative_paths ?? [], fn($f) => $f != null);

        $this->merge([
            'file_paths' => $paths,
            'folder_name' => $this->detectFolderName($paths)
        ]);
    }

    protected function passedValidation()
    {
        $data = $this->validated();

        $this->replace([
            'file_tree' => $this->buildFileTree($this->file_paths, $data['files'])
        ]);
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
                'files.*' => [
                    'required',
                    'file',
                    function($attribute, $value, $fail){
                        if (!$this->folder_name) {
                            $file = File::query()->where('name', $value->getClientOriginalName())
                                                ->where('created_by', Auth::id())
                                                ->where('parent_id', $this->parent_id)
                                                ->whereNull('deleted_at')
                                                ->exists();
                            if ($file) {
                                $fail('File "'.$value->getClientOriginalName().'" already exists.');
                            }
                        }
                    }
                ],
                'folder_name' => [
                    'nullable',
                    'string',
                    function($attribute, $value, $fail){
                        if ($value) {
                            $file = File::query()->where('name', $value)
                                                ->where('created_by', Auth::id())
                                                ->where('parent_id', (int)$this->parent_id)
                                                ->whereNull('deleted_at')
                                                ->exists();
                            if ($file) {
                                $fail('Folder named "'.$value.'" already exists.');
                            }
                        }
                    }
                ]
            ]
        );
    }

    public function detectFolderName($path){
        if (!$path) {
            return null;
        }

        $parts = explode("/", $path[0]);
        return $parts[0];
    }

    private function buildFileTree($file_paths, $files)
    {
        $file_paths = array_slice($file_paths, 0, count($files));
        $file_paths = array_filter($file_paths, fn($f) => $f != null);

        $tree = [];

        /*
        *****tree example*******
        [
            test => [
                1.jpg
            ]
        ]
        */
        foreach ($file_paths as $index => $file_path) {
            $parts = explode('/', $file_path);
            $currentNode = &$tree;

            foreach ($parts as $i => $part) {
                if (!isset($currentNode[$part])) {
                    $currentNode[$part] = [];
                }

                if($i == count($parts) - 1){
                    $currentNode[$part] = $files[$index];
                }else {
                    $currentNode = &$currentNode[$part];
                }
            }
        }

        return $tree;
    }
}
