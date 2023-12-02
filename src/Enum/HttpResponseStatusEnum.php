<?php

namespace ApiClientBundle\Enum;

enum HttpResponseStatusEnum: string
{
    case STATUS_100 = 'Continue';
    case STATUS_101 = 'Switching Protocols';
    case STATUS_102 = 'Processing';            // RFC2518
    case STATUS_103 = 'Early Hints';
    case STATUS_200 = 'OK';
    case STATUS_201 = 'Created';
    case STATUS_202 = 'Accepted';
    case STATUS_203 = 'Non-Authoritative Information';
    case STATUS_204 = 'No Content';
    case STATUS_205 = 'Reset Content';
    case STATUS_206 = 'Partial Content';
    case STATUS_207 = 'Multi-Status';          // RFC4918
    case STATUS_208 = 'Already Reported';      // RFC5842
    case STATUS_226 = 'IM Used';               // RFC3229
    case STATUS_300 = 'Multiple Choices';
    case STATUS_301 = 'Moved Permanently';
    case STATUS_302 = 'Found';
    case STATUS_303 = 'See Other';
    case STATUS_304 = 'Not Modified';
    case STATUS_305 = 'Use Proxy';
    case STATUS_307 = 'Temporary Redirect';
    case STATUS_308 = 'Permanent Redirect';    // RFC7238
    case STATUS_400 = 'Bad Request';
    case STATUS_401 = 'Unauthorized';
    case STATUS_402 = 'Payment Required';
    case STATUS_403 = 'Forbidden';
    case STATUS_404 = 'Not Found';
    case STATUS_405 = 'Method Not Allowed';
    case STATUS_406 = 'Not Acceptable';
    case STATUS_407 = 'Proxy Authentication Required';
    case STATUS_408 = 'Request Timeout';
    case STATUS_409 = 'Conflict';
    case STATUS_410 = 'Gone';
    case STATUS_411 = 'Length Required';
    case STATUS_412 = 'Precondition Failed';
    case STATUS_413 = 'Content Too Large';                                           // RFC-ietf-httpbis-semantics
    case STATUS_414 = 'URI Too Long';
    case STATUS_415 = 'Unsupported Media Type';
    case STATUS_416 = 'Range Not Satisfiable';
    case STATUS_417 = 'Expectation Failed';
    case STATUS_418 = 'I\'m a teapot';                                               // RFC2324
    case STATUS_421 = 'Misdirected Request';                                         // RFC7540
    case STATUS_422 = 'Unprocessable Content';                                       // RFC-ietf-httpbis-semantics
    case STATUS_423 = 'Locked';                                                      // RFC4918
    case STATUS_424 = 'Failed Dependency';                                           // RFC4918
    case STATUS_425 = 'Too Early';                                                   // RFC-ietf-httpbis-replay-04
    case STATUS_426 = 'Upgrade Required';                                            // RFC2817
    case STATUS_428 = 'Precondition Required';                                       // RFC6585
    case STATUS_429 = 'Too Many Requests';                                           // RFC6585
    case STATUS_431 = 'Request Header Fields Too Large';                             // RFC6585
    case STATUS_451 = 'Unavailable For Legal Reasons';                               // RFC7725
    case STATUS_500 = 'Internal Server Error';
    case STATUS_501 = 'Not Implemented';
    case STATUS_502 = 'Bad Gateway';
    case STATUS_503 = 'Service Unavailable';
    case STATUS_504 = 'Gateway Timeout';
    case STATUS_505 = 'HTTP Version Not Supported';
    case STATUS_506 = 'Variant Also Negotiates';                                     // RFC2295
    case STATUS_507 = 'Insufficient Storage';                                        // RFC4918
    case STATUS_508 = 'Loop Detected';                                               // RFC5842
    case STATUS_510 = 'Not Extended';                                                // RFC2774
    case STATUS_511 = 'Network Authentication Required';

    public static function tryFromCode(int $code) : self {
        foreach (self::cases() as $enum) {
            if ($enum->name === 'STATUS_'.$code) {
                return $enum;
            }
        }

        throw new \Exception("Not a valid http code");
    }
}
