<?php

namespace projectorangebox\orange\library\exceptions\Http;

use projectorangebox\orange\library\exceptions\HttpException;

class conflictException extends HttpException
{
	protected $code = 409;
}