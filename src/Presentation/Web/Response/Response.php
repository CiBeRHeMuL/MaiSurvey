<?php

namespace App\Presentation\Web\Response;

use App\Presentation\Web\Enum\ErrorSlugEnum;
use App\Presentation\Web\Enum\HttpStatusCodeEnum;
use App\Presentation\Web\Response\Model\Common\CriticalResponse;
use App\Presentation\Web\Response\Model\Common\Error;
use App\Presentation\Web\Response\Model\Common\ErrorResponse;
use App\Presentation\Web\Response\Model\Common\SuccessResponse;
use App\Presentation\Web\Response\Model\Common\SuccessWithPaginationResponse;
use App\Presentation\Web\Response\Model\Common\ValidationResponse;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

class Response
{
    public static function success(
        SuccessResponse $response = new SuccessResponse(),
        HttpStatusCodeEnum $statusCode = HttpStatusCodeEnum::Ok,
    ): JsonResponse {
        return self::response($response, $statusCode);
    }

    public static function successWithPagination(
        SuccessWithPaginationResponse $response = new SuccessWithPaginationResponse(),
        HttpStatusCodeEnum $statusCode = HttpStatusCodeEnum::Ok,
    ): JsonResponse {
        return self::response($response, $statusCode);
    }

    public static function validation(
        ValidationResponse $response = new ValidationResponse(),
        HttpStatusCodeEnum $statusCode = HttpStatusCodeEnum::UnprocessableEntity,
    ): JsonResponse {
        return self::response($response, $statusCode);
    }

    public static function error(
        ErrorResponse $response = new ErrorResponse(new Error('UNKNOWN_ERROR', 'Unknown error')),
        HttpStatusCodeEnum $statusCode = HttpStatusCodeEnum::BadRequest,
    ): JsonResponse {
        return self::response($response, $statusCode);
    }

    public static function notFound(): JsonResponse
    {
        return self::response(
            new ErrorResponse(
                new Error(
                    ErrorSlugEnum::NotFound->getSlug(),
                    ErrorSlugEnum::NotFound->getSlug(),
                ),
            ),
            HttpStatusCodeEnum::NotFound,
        );
    }

    public static function critical(
        CriticalResponse $response = new CriticalResponse(new Exception('Unknown exception')),
        HttpStatusCodeEnum $statusCode = HttpStatusCodeEnum::InternalServerError,
    ): JsonResponse {
        return self::response($response, $statusCode);
    }

    public static function response(mixed $data, HttpStatusCodeEnum $statusCode): JsonResponse
    {
        $response = new JsonResponse(status: $statusCode->getCode());
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRESERVE_ZERO_FRACTION,
        );
        $response->setData($data);
        return $response;
    }
}
