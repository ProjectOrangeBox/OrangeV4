<?php

namespace projectorangebox\orange\library\exceptions\Http;

use projectorangebox\orange\library\exceptions\HttpException;

class notFoundException extends HttpException
{
	protected $code = 404;
}