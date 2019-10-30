<?php

namespace projectorangebox\orange\library;

interface ServiceLocatorInterface
{

	public function __construct(array $config);

	public function __call(string $name, array $arguments);

	public function add(string $type, string $name, string $serviceClass): bool;
	public function find(string $type, string $name): string;

	public function alias(string $name): string;
	public function addAlias(string $alias, string $real): void;

	/* singleton */
	public function get(string $name, array $userConfig = [], string $as = null): object;

	/* factory */
	public function create(string $name, array $userConfig = []): object;
}
