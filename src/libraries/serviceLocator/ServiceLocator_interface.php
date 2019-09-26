<?php

namespace projectorangebox\orange\library\serviceLocator;

interface ServiceLocator_interface {

	public function __construct(array $config);

	public function add(string $type,string $name,string $serviceClass): bool;
	public function find(string $type,string $name): string;

	public function alias(string $name): string;
	public function addAlias(string $alias, string $real): void;

	public function get(string $name, array $userConfig = [], string $as = null): object;
	public function create(string $name, array $userConfig = []): object;

}
