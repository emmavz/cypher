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
    public function sendResponse($result)
    {
        // $response = [
        //     'success' => true,
        //     'data'    => $result,
        // ];

        $response = $result;

        return response()->json($response, 200);
    }


    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($errorMessages, $code = 404)
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

        throw new ValidationException($errorMessages);

        // return response()->json($response, $code);
    }
}
