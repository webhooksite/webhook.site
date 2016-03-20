<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Factory;

abstract class Request extends FormRequest
{
    ///**
    // * @return mixed
    // *///
    //protected function getValidatorInstance()
    //{
    //    $factory = $this->container->make(Factory::class);
//
    //    if (method_exists($this, 'validator')) {
    //        return $this->container->call([$this, 'validator'], compact('factory'));
    //    }
//
    //    $rules = $this->container->call([$this, 'rules']);
//
    //    return $factory->make(
    //        $this->all(),
    //        $rules,
    //        $this->messages()
    //    );
    //}
}
