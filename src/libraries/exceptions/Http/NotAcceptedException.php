<?php

namespace projectorangebox\orange\library\exceptions\Http;

use projectorangebox\orange\library\exceptions\HttpException;

class notAcceptedException extends HttpException
{
	protected $code = 406;
}