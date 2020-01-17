<?php

namespace projectorangebox\orange\library;

/**
 * Orange
 *
 * Manage Events in your Application
 *
 * An open source extensions for CodeIgniter 3.x
 *
 * This content is released under the MIT License (MIT)
 * Copyright (c) 2014 - 2019, Project Orange Box
 *
 * Some parts copyright CodeIgniter 4.x MIT
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 */

class Event
{
	const PRIORITY_LOWEST = 10;
	const PRIORITY_LOW = 20;
	const PRIORITY_NORMAL = 50;
	const PRIORITY_HIGH = 80;
	const PRIORITY_HIGHEST = 90;

	/**
	 * storage for events
	 *
	 * @var array
	 */
	protected $listeners = [];

	protected $paused = [];
	protected $pauseAll = false;

	public function __construct()
	{
		log_message('info', 'Orange Event Class Initialized');
	}

	/**
	 * pause
	 * Prevent All Triggers from firing.
	 *
	 * @return Event
	 */
	public function pauseAll(): Event
	{
		log_message('debug', 'event::pauseAll');

		$this->pauseAll = true;

		/* allow chaining */
		return $this;
	}

	/**
	 * pause
	 * Prevent Trigger by name from firing.
	 *
	 * @return Event
	 */
	public function pause(string $event): Event
	{
		$event = $this->normalize($event);

		log_message('debug', 'event::pause::' . $event);

		$this->paused[$event] = true;

		/* allow chaining */
		return $this;
	}

	/**
	 * unpause
	 * Allow all Triggers to fire.
	 *
	 * @return Event
	 */
	public function unpauseAll(): Event
	{
		log_message('debug', 'event::unpauseAll');

		$this->pauseAll = false;

		/* allow chaining */
		return $this;
	}

	/**
	 * unpause
	 * Allow Triggers by name to fire.
	 *
	 * @return Event
	 */
	public function unpause(string $event): Event
	{
		$event = $this->normalize($event);

		log_message('debug', 'event::unpause::' . $event);

		unset($this->paused[$event]);

		/* allow chaining */
		return $this;
	}

	/**
	 * Register a listener
	 *
	 * #### Example
	 * ```php
	 * register('open.page',function(&$var1) { echo "hello $var1"; },EVENT::PRIORITY_HIGH);
	 * ```
	 * @access public
	 *
	 * @param string $event name of the event we want to listen for
	 * @param callable $callable function to call if the event if triggered
	 * @param int $priority the priority this listener has against other listeners
	 *
	 * @return Event
	 *
	 */
	public function register(string $event, $callable, int $priority = EVENT::PRIORITY_NORMAL): Event
	{
		/* clean up the name */
		$event = $this->normalize($event);

		/* log a debug event */
		log_message('debug', 'event::register::' . $event);

		$this->listeners[$event][0] = !isset($this->listeners[$event]); // Sorted?
		$this->listeners[$event][1][] = $priority;
		$this->listeners[$event][2][] = $callable;

		/* allow chaining */
		return $this;
	}

	/**
	 * Trigger an event
	 *
	 * #### Example
	 * ```php
	 * trigger('open.page',$var1);
	 * ```
	 * @param string $event event to trigger
	 * @param mixed ...$arguments pass by reference
	 *
	 * @return Event
	 *
	 * @access public
	 *
	 */
	public function trigger(string $event, &...$arguments): Event
	{
		/* clean up the name */
		$event = $this->normalize($event);

		/* do we even have any events with this name? */
		if (isset($this->listeners[$event])) {
			/* are we pausing all triggers? */
			if (!$this->pauseAll) {
				/* are we pausing just this trigger? */
				if (!isset($this->paused[$event])) {
					/* log a debug event */
					log_message('debug', 'event::trigger::' . $event);

					foreach ($this->_listeners($event) as $listener) {
						if ($listener(...$arguments) === false) {
							break;
						}
					}
				}
			}
		}

		/* allow chaining */
		return $this;
	}

	/**
	 *
	 * Is there any listeners for a certain event?
	 *
	 * #### Example
	 * ```php
	 * $bool = ci('event')->has('page.load');
	 * ```
	 * @access public
	 *
	 * @param string $event event to search for
	 *
	 * @return bool
	 *
	 */
	public function has(string $event): bool
	{
		/* clean up the name */
		$event = $this->normalize($event);

		return isset($this->listeners[$event]);
	}

	/**
	 *
	 * Return an array of all of the event names
	 *
	 * #### Example
	 * ```php
	 * $triggers = ci('event')->events();
	 * ```
	 * @access public
	 *
	 * @return array
	 *
	 */
	public function events(): array
	{
		return array_keys($this->listeners);
	}

	/**
	 *
	 * Return the number of events for a certain name
	 *
	 * #### Example
	 * ```php
	 * $listeners = ci('event')->count('database.user_model');
	 * ```
	 * @access public
	 *
	 * @param string $event
	 *
	 * @return int
	 *
	 */
	public function count(string $event): int
	{
		/* clean up the name */
		$event = $this->normalize($event);

		return (isset($this->listeners[$event])) ? count($this->listeners[$event][1]) : 0;
	}

	/**
	 *
	 * Removes a single listener from an event.
	 * this doesn't work for closures!
	 *
	 * @access public
	 *
	 * @param string $event
	 * @param $matches
	 *
	 * @return bool
	 *
	 */
	public function unregister(string $event, $matches = null): bool
	{
		/* clean up the name */
		$event = $this->normalize($event);

		$removed = false;

		if (isset($this->listeners[$event])) {
			if ($matches == null) {
				unset($this->listeners[$event]);

				$removed = true;
			} else {
				foreach ($this->listeners[$event][2] as $idx => $check) {
					if ($matches === $check) {
						unset($this->listeners[$event][1][$idx]);
						unset($this->listeners[$event][2][$idx]);

						$removed = true;
					}
				}
			}
		}

		return $removed;
	}

	/**
	 *
	 * Removes all listeners.
	 *
	 * If the event_name is specified, only listeners for that event will be
	 * removed, otherwise all listeners for all events are removed.
	 *
	 * @access public
	 *
	 * @param string $event
	 *
	 * @return \Event
	 *
	 */
	public function unregisterAll(): Event
	{
		$this->listeners = [];

		/* allow chaining */
		return $this;
	}

	/**
	 *
	 * Normalize the event name
	 *
	 * @access protected
	 *
	 * @param string $event
	 *
	 * @return string
	 *
	 */
	protected function normalize(string $event): string
	{
		return trim(preg_replace('/[^a-z0-9]+/', '.', strtolower($event)), '.');
	}

	/**
	 *
	 * Do the actual sorting
	 *
	 * @access protected
	 *
	 * @param string $event
	 *
	 * @return array
	 *
	 */
	protected function _listeners(string $event): array
	{
		$event = $this->normalize($event);
		$listeners = [];

		if (isset($this->listeners[$event])) {
			/* The list is not sorted */
			if (!$this->listeners[$event][0]) {
				/* Sort it! */
				array_multisort($this->listeners[$event][1], SORT_DESC, SORT_NUMERIC, $this->listeners[$event][2]);

				/* Mark it as sorted already! */
				$this->listeners[$event][0] = true;
			}

			$listeners = $this->listeners[$event][2];
		}

		return $listeners;
	}
} /* end class */
