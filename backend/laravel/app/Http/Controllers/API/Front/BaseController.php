<?php


namespace App\Http\Controllers\API\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;
use Illuminate\Validation\ValidationException;

class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $status = 200)
    {
        return response()->json($result, $status);
    }


    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($errorMessages,  $exceptions = false, $status = 404)
    {
        // $response = [
        //     'success' => false,
        //     'message' => $error,
        // ];

        // if (!empty($errorMessages)) {
        //     $response['data'] = $errorMessages;
        // }

        // if (!empty($errorMessages)) {
        //     $response = $errorMessages;
        // }

        if($exceptions) {
            throw ValidationException::withMessages($errorMessages);
        }
        else {
            return response()->json($errorMessages, $status);
        }
    }
}
