<?php

namespace App\Http\Traits;

trait RespondsWithHttpStatus
{
    protected function success($message, $data = [], $status = 200)
    {
        return response([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $status);
    }

    protected function success_user($message, $data = [], $user)
    {
        return response([
            'success' => true,
            'data' => $data,
            'user_name' => $user,
            'message' => $message,
        ], 200);
    }

    protected function failure($message, $status = 422)
    {
        return response([
            'success' => false,
            'message' => $message,
        ], $status);
    }

    public function validationFailure($error)
    {
        return response([
            "success" => false,
            "message" => $error,
        ]);
    }
}
