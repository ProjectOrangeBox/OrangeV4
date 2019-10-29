<?php

namespace projectorangebox\orange\library\input;

use \projectorangebox\orange\library\input\Dot;

/**
 * $_raw_input_stream = model.id=876&model.fname=Johnny&model.lname=Appleseed&model.age=23&model.test1=%26&model.test2=%3D&model.removeme=foobar&model.moveme=movebar&model2.fname=Jenny&model2.lname=Appleseed&model2.age=21&model.repeatable.%23.fname=Albert&model.repeatable.%23.lname=Appleseed&model.repeatable.%23.age=12&model.repeatable.%23.fname=Peter&model.repeatable.%23.lname=Appleseed&model.repeatable.%23.age=13&model.repeatable.%23.fname=Lynn&model.repeatable.%23.lname=Appleseed&model.repeatable.%23.age=14
 *
<form method="post" action="/form/output">

	Primary: <input type="text" name="model.id" value="876"><br>
	First name: <input type="text" name="model.fname" value="Johnny"><br>
	Last name: <input type="text" name="model.lname" value="Appleseed"><br>
	Age: <input type="text" name="model.age" value="23"><br>

	Test 1: <input type="text" name="model.test1" value="&"><br>
	Test 2: <input type="text" name="model.test2" value="="><br>

	Remove: <input type="text" name="model.removeme" value="foobar"><br>
	Move: <input type="text" name="model.moveme" value="movebar"><br>

	<hr>

	model 2 first name: <input type="text" name="model2.fname" value="Jenny"><br>
	model 2 last name: <input type="text" name="model2.lname" value="Appleseed"><br>
	model 2 age: <input type="text" name="model2.age" value="21"><br>

	<hr>

	repeating first name: <input type="text" name="model.repeatable.#.fname" value="Albert"><br>
	repeating last name: <input type="text" name="model.repeatable.#.lname" value="Appleseed"><br>
	repeating age: <input type="text" name="model.repeatable.#.age" value="12"><br>

	repeating first name: <input type="text" name="model.repeatable.#.fname" value="Peter"><br>
	repeating last name: <input type="text" name="model.repeatable.#.lname" value="Appleseed"><br>
	repeating age: <input type="text" name="model.repeatable.#.age" value="13"><br>

	repeating first name: <input type="text" name="model.repeatable.#.fname" value="Lynn"><br>
	repeating last name: <input type="text" name="model.repeatable.#.lname" value="Appleseed"><br>
	repeating age: <input type="text" name="model.repeatable.#.age" value="14"><br>

	<input type="submit" value="Submit"><br>

</form>

$remap = [
	// to this position from this position
	'model.repeatable.#.parent_id'=>'model.id',
	'model.copied'=>'model.moveme',
	'model.removeme','model.moveme'
];

$output = ci('input')->requestRemap($remap); /* optional boolean replace input request

*/

class RequestRemap {
	/**
	 * $formData
	 *
	 * @var undefined
	 */
	protected $dotArray;

	/**
	 * $repeatKeys
	 *
	 * @var array
	 */
	protected $repeatKeys = [];

	/**
	 * $inputRepeatingKey
	 *
	 * The form and input character which signifies this input key should be treated like an array
	 *
	 * @var string
	 */
	protected $inputRepeatingKey = '#';

	/**
	 * process a request with advanced options
	 * this makes it easier to pass the returned array into a models
	 * or something else for further processing
	 *
	 * @return array
	 *
	 */
	/**
	 * process
	 *
	 * @param mixed array of remapping rules
	 * @param string $rawStream raw HTTP request string
	 * @return array
	 */
	public function processRaw(array $remap = [],string $rawStream) : Dot
	{
		/* fresh instance */
		$this->dotArray = new Dot;

		/* convert the raw http request to a key value pair */
		$this->buildArray($rawStream);

		/* run thought rules - they are either copy or delete */
		foreach ($remap as $key=>$value) {
			/* if it has a key & value it's a copy if not it's a remove */
			if (!is_integer($key)) {
				/* copy from one location to another with repeating support */
				$this->copyValue($key,$value);
			} else {
				/* delete the value */
				$this->dotArray->delete($value);
			}
		}

		/* return the new array */
		return $this->dotArray;
	}

	public function processArray(array $remap = [],array $array) : Dot
	{
		/* fresh instance */
		$this->dotArray = new Dot($array);

		/* run thought rules - they are either copy or delete */
		foreach ($remap as $key=>$value) {
			/* if it has a key & value it's a copy if not it's a remove */
			if (!is_integer($key)) {
				/* copy from one location to another with repeating support */
				$this->copyValue($key,$value);
			} else {
				/* delete the value */
				$this->dotArray->delete($value);
			}
		}

		/* return the new array */
		return $this->dotArray;
	}

	protected function buildArray(string $rawStream) : void
	{
		/* convert raw stream to key value pairs */
		foreach (explode('&',$rawStream) as $section) {
			list($key,$value) = explode('=',$section,2);

			/* now that's it split up we can decode the key */
			$key = urldecode($key);

			/* test if is set so we don't throw a index not found error - then get the next value for this repeating */
			$this->repeatKeys[$key] = (!isset($this->repeatKeys[$key])) ? 0 : ++$this->repeatKeys[$key];

			$this->dotArray->set(str_replace($this->inputRepeatingKey,$this->repeatKeys[$key],$key),urldecode($value));
		}
	}

	protected function copyValue(string $copyTo,string $copyFrom) : void
	{
		/* is it a repeater or regular? */
		if (strpos($copyTo,$this->inputRepeatingKey) === false) {
			$oldValue = $this->dotArray->get($copyFrom,null);

			if ($oldValue !== null) {
				$this->dotArray->set($copyTo,$oldValue);
			}
		} else {
			list($dotnotation,$newKey) = explode($this->inputRepeatingKey,$copyTo);

			foreach (array_keys($this->dotArray->get(trim($dotnotation,'.'),[])) as $index) {
				$this->dotArray->set($dotnotation.$index.$newKey,$this->dotArray->get($copyFrom));
			}
		}
	}

} /* end class */