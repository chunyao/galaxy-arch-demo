<?php

namespace Galaxy\Http\Bo;

use Mix\Validator\Validator;

class BaseBean extends Validator
{
    public function __construct(array $attributes, array $uploadedFiles = [])
    {
        $this->attributes = $attributes;
        $this->uploadedFiles = $uploadedFiles;
        foreach (  $this->attributes as $key => $val){
            $this->$key = $val;
        }
    }

    public function toArray(): array
    {
        $data = [];
        foreach ($this as $key => $val) {
            if (in_array($key, ['attributes', 'uploadedFiles', 'scenario', 'validators', 'errors'])) {
                continue;
            }
            $data[$key] = $val;
        }
        return $data;
    }
}