<?php

namespace projectorangebox\orange\library;

interface TemplateParserInterface
{
	/* construct */
	public function __construct(array $config);

	/* default parse & parse_string */
	public function parse(string $templateFile, array $data = [], bool $return = false): string;
	public function parse_string(string $templateStr, array $data = [], bool $return = false): string;

	/* allow searching for a matching template */
	public function exists(string $name): string;
}
