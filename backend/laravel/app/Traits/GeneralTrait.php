<?php

namespace App\Traits;

trait GeneralTrait
{

    public function deleteSelected($request, $modal, $prefix = null)
    {
        $prefix = ($prefix) ? $prefix : 'App\\Models\\';
        $modelName = $prefix . $modal;

        $ids = explode(",", $request->ids);

        $ids = $modelName::whereIn('id', $ids)->get()->pluck('id');

        $modelName::destroy($ids);

        return true;
    }
}
