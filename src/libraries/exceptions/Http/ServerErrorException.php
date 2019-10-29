<?php

namespace projectorangebox\orange\library\exceptions\Http;

use projectorangebox\orange\library\exceptions\HttpException;

class serverErrorException extends HttpException
{
	protected $code = 500;
}