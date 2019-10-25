<?php

namespace projectorangebox\orange\library\exceptions\Http;

use projectorangebox\orange\library\exceptions\HttpException;

class unauthorizedException extends HttpException
{
	protected $code = 401;
}