# OrangeV4

boot order

1. index.php sets up some required constants
1. Load Orange Bootstrap.php
	1. Load Orange Bootstrap/Index.php Oranges version of CodeIgniters Index.php
	1. Load Application/Bootstrap.ENVIRONMENT.php if present User Bootstrap based on enviroment
	1. Load Application/Bootstrap.php if present User Bootstrap
	1. Load Orange/Bootstrap/Functions.php
	1. Load Orange/Bootstrap/Wrappers.php
	1. Load Orange FS.php File System Functions
	1. Set the Root in FS class
	1. Load CodeIgniter/Core/CodeIgniter.php




