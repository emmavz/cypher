<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Admin;
use Auth;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
        // return is_admin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $method = $this->getMethod();
        $rules = Admin::rules();

        if($method == 'GET' || $method == 'POST'){ //store
            
        }else{ // update
           $rules['password'][0] = 'nullable';
           $rules['email'][4] = 'unique:admins,email,'.Auth::user()->id;
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            
        ];
    }
}
