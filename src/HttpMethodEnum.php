<?php

declare(strict_types=1);

namespace PTS\Next2;

/**
 * @link http://www.iana.org/assignments/http-methods/http-methods.xhtml
 */
enum HttpMethodEnum
{
    case GET;
    case HEAD;
    case POST;
    case PUT;
    case PATCH;
    case DELETE;
    case OPTIONS;
}