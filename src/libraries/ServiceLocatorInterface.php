<?php

namespace projectorangebox\orange\library;

interface ServiceLocatorInterface
{
	public function __construct(array $config);

	public function __call(string $name, array $arguments);

	public function has(string $type, string $name): bool;
	public function add(string $type, string $name, string $serviceClass): bool;
	public function find(string $type, string $name): string;

	/* singleton */
	public function get(string $name, array $userConfig = [], string $as = null): object;

	/* factory */
	public function create(string $name, array $userConfig = []): object;
}
