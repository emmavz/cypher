<?php

use App\Models\User;
use Illuminate\Support\Str;

function generateReferral()
{
    $length = 5;
    do {
        $referral = Str::random($length);
    } while (User::where('referral_token', $referral)->exists());

    return $referral;
}

function stake_format($number)
{
    return round($number, 4);
}

function d_format($datetime)
{
    return date('d/m/Y h:i:s a', strtotime($datetime));
}

function navActive($string)
{

    if (Route::currentRouteName() == $string) {
        return 'nav-link-active';
    }
    return null;
}

function treeActive($string)
{
    if (strpos(Route::currentRouteName(), $string) === 0) {
        return 'menu-open';
    }
    return null;
}

function getLocale()
{
    return session()->has('locale') ? session()->get('locale') : config('translatable.locales')[0];
}

function getDirection($lang = null)
{
    $lang = $lang ? $lang : getLocale();
    return in_array($lang, config('translatable.direction')) ? 'rtl' : '';
}

function permission($permission, $status = false)
{
    if (Auth::user()->hasRole('Super Admin')) {
        return true;
    }

    if (Auth::user()->hasAnyPermission($permission)) {

        return true;
    }

    if ($status) {
        return false;
    }

    abort(403, 'This action is unauthorized.');
}

function is_admin()
{
    if (Auth::user()->hasRole('Super Admin')) {
        return true;
    }
    abort(403, 'This action is unauthorized.');
}

function deleteSelected($request, $modal, $prefix = null)
{
    $prefix = ($prefix) ? $prefix : 'App\\Models\\';
    $modelName = $prefix . $modal;

    $ids = explode(",", $request->ids);

    $ids = $modelName::whereIn('id', $ids)->get()->pluck('id');

    $modelName::destroy($ids);

    return true;
}

function updateTableRows($request, $modal, $prefix = null)
{
    $prefix = ($prefix) ? $prefix : 'App\\Models\\';
    $modelName = $prefix . $modal;

    $data = explode(',', $request->ids);
    $count = $request->min;

    // Find ids
    foreach ($data as $dat) {
        $modelName::where('id', $dat)->update(['position' => $count++]);
    }

    return true;
}

function getWebp($name)
{
    if ($name) {
        $ext = '.' . pathinfo($name, PATHINFO_EXTENSION);
        return str_replace($ext, '.webp', $name);
    }
    return $name;
}

function filenamegenerator($arg = null, $ext = false)
{
    $name = sha1(mt_rand() . time());

    if (!$arg && !$ext) {
        return $name;
    }

    $name .= (gettype($arg) == 'string') ? '.' . $arg : '.' . strtolower($arg->getClientOriginalExtension());

    return $name;
}

function getImage($file)
{
    $newfile = '';
    $name   = pathinfo($file, PATHINFO_FILENAME);
    $ext   = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $path   = pathinfo($file, PATHINFO_DIRNAME) . '/';

    $ext = support_webp() ? 'webp' : $ext;

    $newfile = $path . $name . '.' . $ext;

    return $newfile;
}

function support_webp()
{
    return isset($_SERVER['HTTP_ACCEPT']) &&
        strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false;
}

function storeImage($dataArray)
{
    $formImage = $dataArray['image'];
    $path      = rtrim($dataArray['path'], '/') . '/';

    $resize =  isset($dataArray['resize']) ? $dataArray['resize'] : null;
    $fit =  isset($dataArray['fit']) ? $dataArray['fit'] : null;
    $resizeIf =  isset($dataArray['resizeIf']) ? $dataArray['resizeIf'][0] : null;
    $shouldWebp = isset($dataArray['webp']) ? $dataArray['webp'] : false;
    $thumbnails  = isset($dataArray['thumbnails']) ? $dataArray['thumbnails'] : null;

    if (!Storage::exists($path)) {
        Storage::makeDirectory($path);
    }

    $imageName = sha1(mt_rand() . time());
    $extO = strtolower($formImage->getClientOriginalExtension());

    $img = Image::make($formImage)->orientate();

    if ($resize != null) {
        if ($resize[0] && $resize[1]) {
            $img->resize($resize[0], $resize[1]);
        } else {
            $img->resize($resize[0], $resize[1], function ($constraint) {
                $constraint->aspectRatio();
            });
        }
    } else if ($fit != null) {
        $img->fit($fit[0], $fit[1]);
    } else if ($resizeIf != null) {

        list($width, $height) = getimagesize($formImage);

        if ($width > $resizeIf) {
            $img->resize($resizeIf, null, function ($constraint) {
                $constraint->aspectRatio();
            });
        }
    }

    if ($shouldWebp) {
        if ($extO != 'webp'); {
            $img->encode('webp', 100);
        }
        $cimg = clone $img;
        $img->save(Storage::path($path . $imageName . '.webp'));
        makeThumbnail($thumbnails, $cimg, $path, $imageName, 'webp');

        // Original Image Extention
        if ($extO == 'webp') {
            $cimg->encode('jpg', 100);
            $extO = 'jpg';
        }
    } else {
        $cimg = $img;
    }

    $cimg->save(Storage::path($path . $imageName . '.' . $extO));
    makeThumbnail($thumbnails, $cimg, $path, $imageName, $extO);

    $imageName = $imageName . '.' . $extO;

    return $imageName;
}

function makeThumbnail($thumbnails, $cloned, $path, $imageName, $extO)
{
    if ($thumbnails != null) {
        $cloneArr = [];
        foreach ($thumbnails as $key => $thumbnail) {
            if ($key > 0) {
                $path .= rtrim($thumbnails[$key - 1][0], '/') . '/';
            }
            $subpath = rtrim($thumbnail[0], '/') . '/';
            if (!Storage::exists($path . $subpath)) {
                Storage::makeDirectory($path . $subpath);
            }
            $cloneArr[] = clone $cloned;

            $cloneArr[$key]->fit($thumbnail[1], $thumbnail[2])->save(Storage::path($path . $subpath . $imageName . '.' . $extO));
        }
    }
}

function summernote($text = null, $path = null, $oldtext = null)
{

    $internalErrors = libxml_use_internal_errors(true);
    $dom = new \domdocument('1.0', 'UTF-8');
    $body = null;

    $newimages = [];
    // It will not trigger for delete
    if ($text) {
        // Store new images + text
        $dom->loadHtml(config('website.editor_utf') . $text, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
        $images = $dom->getElementsByTagName('img');

        foreach ($images as $img) {
            $src = $img->getAttribute('src');

            if (preg_match('/data:image/', $src)) {
                preg_match('/data:image\/(?<mime>.*?)\;/', $src, $groups);
                $mimetype = $groups['mime'];
                $filename = sha1(mt_rand() . time());
                $filepath = $path . "$filename.$mimetype";
                $image    = Image::make($src)->save(Storage::path($filepath));
                $new_src  = Storage::url($filepath);
                $img->setAttribute('src', $new_src);
            } else {
                $newimages[] = $src;
            }
        }

        $body = $dom->savehtml();
    }

    // It will only trigger for edit/delete
    if ($oldtext) {

        // Get old images from db and add them to array
        $dom->loadHTML(html_entity_decode($oldtext));
        $images  = $dom->getElementsByTagName("img");

        $oldimages = [];
        foreach ($images as $img) {
            $oldimages[] = $img->getAttribute('src');
        }

        // Delete old images from path
        if (count($oldimages)) {
            $deletableimages = array_diff($oldimages, $newimages);
            if (!empty($deletableimages)) {
                foreach ($deletableimages as $img) {
                    Storage::delete($path . basename($img));
                }
            }
        }
    }

    libxml_use_internal_errors($internalErrors);
    // return trim(preg_replace('/(<p><br><\/p>)+/', '<p><br></p>', $body));
    return trim(preg_replace('/(<p><br\s?\/?><\/p>(<\/p>)?)+$/', '', $body));
}
