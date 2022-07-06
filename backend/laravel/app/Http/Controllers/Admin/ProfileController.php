<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\UpdateProfileRequest;
use App\Models\Admin;
use Auth;

class ProfileController extends Controller
{

  public function edit()
  {
  	return view('admin.profiles.edit');
  }

  public function update(UpdateProfileRequest $request)
  {
  	$data = $request->validated();

  	$admin = Admin::findOrFail(Auth::user()->id);

    $data['password'] = $data['password'] ? \Hash::make($data['password']) : Auth::user()->password;

  	$admin->update($data);

  	toast(__('Successfully updated'),'success');

  	return response(['status' => true]);
  }

  
}
