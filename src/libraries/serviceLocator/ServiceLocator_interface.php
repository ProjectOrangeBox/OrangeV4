<?php

namespace projectorangebox\orange\library\serviceLocator;

interface ServiceLocator_interface {

	public function __construct(array $config);

	function serviceAlias(string $name): string;
	function addAlias(string $alias, string $real): void;

	function get(string $name, array $userConfig = [], string $as = null): object;
	function create(string $name, array $userConfig = []): object;

}
