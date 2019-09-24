<?php

namespace projectorangebox\orange\library\serviceLocator;

interface ServiceLocator_interface {

	function findService(string $serviceName, bool $throwException = true, string $prefix = ''); /* mixed false or string */
	function addService(string $serviceName, string $class): void;

	function servicePrefix(string $key): string;
	function addServicePrefix(string $key, string $prefix): void;

	function serviceAlias(string $name): string;
	function addAlias(string $alias, string $real): void;

	function singleton(string $name, array $userConfig = [], string $as = null): object;
	function factory(string $name, array $userConfig = []): object;

}
