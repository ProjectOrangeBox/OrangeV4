<?php

namespace projectorangebox\orange\library;

use Exception;
use projectorangebox\orange\library\TemplateParserInterface;
use projectorangebox\orange\library\exceptions\MVC\ParserForExtentionNotFoundException;
use projectorangebox\orange\library\exceptions\MVC\TemplateNotFoundException;
use projectorangebox\orange\library\exceptions\MVC\ViewNotFoundException;

/**
 *
 * This is a little different than the default CodeIgniter Parser
 * because this let's you register file extension to be hadnled by different parsers
 *
 * $parser->md = new MarkdownHandler($config);
 * $parser->php = new NormalHandler($config);
 * $parser->hbs = new HandlebarsHandler($config);
 *
 *	$parser->hbs->parse('main/index',['data'=>'foobar']);
 *
 *	Determine from found template key
 *	$parser->parse('main/index',['data'=>'foobar']);
 *
 */
class Parser
{
	protected $parsers = [];

	protected $fourohfour = '404';

	/* pass thru based on extension ...parser->html->parse(...) */
	public function __get(string $extension)
	{
		$extension = $this->normalizeExtension($extension);

		if (!\array_key_exists($extension,$this->parsers)) {
			throw new ParserForExtentionNotFoundException($extension);
		}

		return $this->parsers[$extension];
	}

	/* set parser extension handler ...parser->html = $handlebars */
	public function __set(string $extension, TemplateParserInterface $parser)
	{
		$this->parsers[$this->normalizeExtension($extension)] = &$parser;
	}

	public function parse(string $key,array $data = []): string
	{
		$key = $this->normailizedKey($key);

		/* ok who has this view? if nobody look for the 404 view */
		try {
			$extension = $this->findView($key);
		} catch (ViewNotFoundException $e) {
			$key = $this->normailizedKey($this->fourohfour);

			try {
				$extension = $this->findView($key);
			} catch (ViewNotFoundException $e) {
				throw new TemplateNotFoundException($key.' or '.$this->fourohfour);
			}
		}

		return $this->parsers[$extension]->parse($key,$data,true);
	}

	public function parse_string(string $string,string $extension,array $data = []): string
	{
		$extension = $this->normalizeExtension($extension);

		if (!\array_key_exists($extension,$this->parsers)) {
			throw new ParserForExtentionNotFoundException($extension);
		}

		return $this->parsers[$extension]->parse_string($string,$data,true);
	}

	public function normailizedKey(string $key): string
	{
		return strtolower(trim($key,'/'));
	}

	public function normalizeExtension(string $extension): string
	{
		return strtolower(trim($extension,'.'));
	}

	protected function findView(string $key): string
	{
		$extension = '';

		foreach (\array_keys($this->parsers) as $extension) {
			try {
				/* if it's not found this will throw an error therefore we need to capture it so it doesn't bubble up */
				$this->parsers[$extension]->exists($key);
				break;
			} catch (Exception $e) {
				$extension = '';
			}
		}

		if ($extension == '') {
			throw new ViewNotFoundException($key);
		}

		/* return the handler that said they have the matching key */
		return $extension;
	}

}