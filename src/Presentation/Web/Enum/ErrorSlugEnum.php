<?php

namespace App\Presentation\Web\Enum;

enum ErrorSlugEnum: string
{
    // 3xx HTTP codes
    case MultipleChoices = 'MULTIPLE_CHOICES';
    case MovedPermanently = 'MOVED_PERMANENTLY';
    case Found = 'FOUND';
    case SeeOther = 'SEE_OTHER';
    case NotModified = 'NOT_MODIFIED';
    case UseProxy = 'USE_PROXY';
    case SwitchProxy = 'SWITCH_PROXY';
    case TemporaryRedirect = 'TEMPORARY_REDIRECT';
    case PermanentRedirect = 'PERMANENT_REDIRECT';

    // 4xx HTTP codes
    case BadRequest = 'BAD_REQUEST';
    case Unauthorized = 'UNAUTHORIZED';
    case PaymentRequired = 'PAYMENT_REQUIRED';
    case Forbidden = 'FORBIDDEN';
    case NotFound = 'NOT_FOUND';
    case MethodNotAllowed = 'METHOD_NOT_ALLOWED';
    case NotAcceptable = 'NOT_ACCEPTABLE';
    case ProxyAuthenticationRequired = 'PROXY_AUTHENTICATION_REQUIRED';
    case RequestTimeout = 'REQUEST_TIMEOUT';
    case Conflict = 'CONFLICT';
    case Gone = 'GONE';
    case LengthRequired = 'LENGTH_REQUIRED';
    case PreconditionFailed = 'PRECONDITION_FAILED';
    case PayloadTooLarge = 'PAYLOAD_TOO_LARGE';
    case UriTooLong = 'URI_TOO_LONG';
    case UnsupportedMediaType = 'UNSUPPORTED_MEDIA_TYPE';
    case RangeNotSatisfiable = 'RANGE_NOT_SATISFIABLE';
    case ExpectationFailed = 'EXPECTATION_FAILED';
    case ImATeapot = 'IM_A_TEAPOT';
    case MisdirectedRequest = 'MISDIRECTED_REQUEST';
    case UnprocessableEntity = 'UNPROCESSABLE_ENTITY';
    case Locked = 'LOCKED';
    case FailedDependency = 'FAILED_DEPENDENCY';
    case TooEarly = 'TOO_EARLY';
    case UpgradeRequired = 'UPGRADE_REQUIRED';
    case PreconditionRequired = 'PRECONDITION_REQUIRED';
    case TooManyRequests = 'TOO_MANY_REQUESTS';
    case RequestHeaderFieldsTooLarge = 'REQUEST_HEADER_FIELDS_TOO_LARGE';
    case UnavailableForLegalReasons = 'UNAVAILABLE_FOR_LEGAL_REASONS';

    // 5xx HTTP codes
    case InternalServerError = 'INTERNAL_SERVER_ERROR';
    case NotImplemented = 'NOT_IMPLEMENTED';
    case BadGateway = 'BAD_GATEWAY';
    case ServiceUnavailable = 'SERVICE_UNAVAILABLE';
    case GatewayTimeout = 'GATEWAY_TIMEOUT';
    case HttpVersionNotSupported = 'HTTP_VERSION_NOT_SUPPORTED';
    case VariantAlsoNegotiates = 'VARIANT_ALSO_NEGOTIATES';
    case InsufficientStorage = 'INSUFFICIENT_STORAGE';
    case LoopDetected = 'LOOP_DETECTED';
    case NotExtended = 'NOT_EXTENDED';
    case NetworkAuthenticationRequired = 'NETWORK_AUTHENTICATION_REQUIRED';

    // Validation slugs
    case EmptyField = 'EMPTY_FIELD';
    case WrongField = 'WRONG_FIELD';
    case UserExists = 'USER_EXISTS';
    case TokenExpired = 'TOKEN_EXPIRED';

    public function getSlug(): string
    {
        return $this->value;
    }
}
