<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{

  public function home()
  {
  	return view('admin.dashboard');
  }

  public function destroy(Request $request)
  {
      $id = $request->id;  

      $prefix = ($request->p) ? $request->p : 'App\\Models\\';
      $modelname = $prefix.$request->md;

      $res = $modelname::where($request->c,$id)->first();
      $res->delete();

      return response()->json(['status'=>true,'message'=>__("Successfully Deleted.")]);
  }

  public function destroySelected(Request $request)
  {
      $ids = explode(",",$request->ids);

      $prefix = ($request->p) ? $request->p : 'App\\Models\\';
      $modelname = $prefix.$request->md;

      $ids = $modelname::whereIn($request->c,$ids)->get()->pluck('id');

      $modelname::destroy($ids);
      
      return response()->json(['status'=>true,'message'=>__("Successfully Deleted.")]);
  }

  public function truncate(Request $request)
  {
      $prefix = ($request->p) ? $request->p : 'App\\Models\\';
      $modelname = $prefix.$request->md;

      $ids = $modelname::get()->pluck('id');
      $modelname::destroy($ids);

      return response()->json(['status'=>true,'message'=>__("Successfully Deleted.")]);
  }

  // Update Table Rows
  public function updateRows(Request $request)
  {
      $data = explode(',',$request->ids);
      $catId = $request->catId;
      $count = $request->min;

      $prefix = ($request->p) ? $request->p : 'App\\Models\\';
      $modelname = $prefix.$request->md;

      // Find ids
      foreach ($data as $dat) {
          $modelname::where('id', $dat)->update(['position' => $count++]);
      }

      return response()->json(['status' => true]);
  }
}
