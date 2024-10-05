<?php

namespace App;

trait ApiResponse
{
    private function resolveStatus($statusCode)
    {
        switch ($statusCode) {
            case 200:
                return "SUCCESS";
            case 401:
                return "NOT_AUTHORIZED";
            case 404:
                return "NOT_FOUND";
            case 417:
                return "VALIDATION_ERROR";
            case 500:
                return "INTERNAL_ERROR";
            default:
                return "UNKNOWN_ERROR";
        }
    }

    public function error($message, $statusCode, $errors = [])
    {
        return response()->json([
            'status' => $this->resolveStatus($statusCode),
            'code' => $statusCode,
            'title' => $this->resolveStatus($statusCode) == "VALIDATION_ERROR" ? "Validation Error" : "Error",
            'message' => $message,
            'data' => [
                'error' => $errors
            ]
        ], $statusCode);
    }

    public function success($message, $data, $statusCode)
    {
        return response()->json([
            'status' => $this->resolveStatus($statusCode),
            'code' => $statusCode,
            'title' => "Success",
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    public function validationError($errors, $message = "The provided data is not valid.")
    {
        return response()->json([
            'status' => $this->resolveStatus(417),
            'code' => 417,
            'title' => "Validation Error",
            'message' => $message,
            'data' => [
                'error' => $errors->all()
            ]
        ], 417);
    }
}
