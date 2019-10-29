<?php

namespace projectorangebox\orange\library;

/**
 * Orange
 *
 * An open source extensions for CodeIgniter 3.x
 *
 * This content is released under the MIT License (MIT)
 * Copyright (c) 2014 - 2019, Project Orange Box
 */

/**
 * Authorization class.
 *
 * Handles login, logout, refresh user data
 *
 * @package CodeIgniter / Orange
 * @author Don Myers
 * @copyright 2019
 * @license http://opensource.org/licenses/MIT MIT License
 * @link https://github.com/ProjectOrangeBox
 * @version v2.0
 * @filesource
 *
 * @uses # \session - CodeIgniter Session
 * @uses # \event - Orange Event
 * @uses # \load - CodeIgniter Loader
 *
 * @config sticky types `['red','danger','warning','yellow']`
 * @config initial pause `3`
 * @config pause for each `1000`
 *
 */
class Wallet
{
	/**
	 * Storage of redirect messages
	 *
	 * @var Array
	 */
	protected $messages = [];

	/**
	 * Session key for wallet messages
	 *
	 * @var String
	 */
	protected $msgKey = 'internal::wallet::msg';

	/**
	 * View variable to place the redirect messages
	 * This can then be used by the views javascript to display the messages
	 * or for further processing
	 *
	 * @var String
	 */
	protected $viewVariable = 'wallet_messages';

	/**
	 * Servers HTTP Referer
	 *
	 * @var string
	 */
	protected $httpReferer;

	/**
	 * Local reference of wallet configuration
	 *
	 * @var Array
	 */
	protected $config;

	/**
	 * Local reference of CodeIgniter Loader
	 *
	 * @var Object
	 */
	protected $load;

	/**
	 * Local reference of CodeIgniter Session
	 *
	 * @var Object
	 */
	protected $session;

	/**
	 * Local reference of Orange Event
	 *
	 * @var \Event
	 */
	protected $event;

	/**
	 * $stickyTypes
	 *
	 * @var array
	 */
	protected $stickyTypes = [];

	/**
	 * $initialPause
	 *
	 * @var integer
	 */
	protected $initialPause = 3;

	/**
	 * $pauseForEach
	 *
	 * @var integer
	 */
	protected $pauseForEach = 1000;

	/**
	 * $defaultType
	 *
	 * @var string
	 */
	protected $defaultType = 'info';

	/**
	 *
	 * Constructor
	 *
	 * @access public
	 *
	 * @param array $config []
	 *
	 */
	public function __construct(array &$config=null)
	{
		if (is_array($config)) {
			$this->config = &$config;
		}

		$this->session = ci('session');
		$this->event = ci('event');
		$this->load = ci('load');

		$this->load->helper('url');

		/* where did we come from? */
		$this->httpReferer = ci('input')->server('HTTP_REFERER');

		/* if it's empty then default to home page */
		if (empty($this->httpReferer)) {
			$this->httpReferer = '/';
		}

		/* What msg types should be considered "sticky" */
		$this->stickyTypes = $this->config['sticky types'] ?? ['red','danger','warning','yellow'];
		$this->initialPause = $this->config['initial pause'] ?? 3;
		$this->pauseForEach = $this->config['pause for each'] ?? 1000;
		$this->defaultType = $this->config['default type'] ?? 'info';

		/* Are there any messages in cold storage? */
		$previousMessages = $this->session->flashdata($this->msgKey);

		if (is_array($previousMessages)) {
			$this->messages = $previousMessages;
		}

		/* set the view variable for this page based on what is in $this->messages */
		$this->setViewVariable();

		log_message('info', 'Orange Wallet Class Initialized');
	}

	/**
	 *
	 * Add a flash message to the current page as javascript variable
	 * Add a flash message as a session flash message and redirect
	 *
	 * @access public
	 *
	 * @param string $msg
	 * @param string $type yellow
	 * @param $redirect null, string, or true. if true http referring page will be used.
	 *
	 * @return \Wallet
	 *
	 * #### Example
	 * ```php
	 * ci('wallet')->msg('Oh No!','info');
	 * ci('wallet')->msg('oH No!','red','/folder/new');
	 * ```
	 */
	public function msg(string $msg = '', string $type = null) : Wallet
	{
		$type = ($type) ?? $this->defaultType;

		/* is this type sticky? - use names not colors - colors support for legacy code */
		$sticky = in_array($type, $this->stickyTypes);

		/* trigger a event incase they need to do something */
		$this->event->trigger('wallet.msg', $msg, $type, $sticky);

		$this->messages[md5(trim($type.$msg))] = ['msg' => trim($msg), 'type' => $type, 'sticky' => $sticky];

		/* put in view variable incase they want to use it on this page */
		$this->setViewVariable();

		return $this;
	}

	public function redirect(string $redirect) : void
	{
		/* store this in a session variable for redirect */
		$this->session->set_flashdata($this->msgKey, $this->messages);

		/* if it starts with @ then pick up the referer */
		if ($redirect[0] == '@') {
			$redirect = $this->httpReferer;
		}

		redirect($redirect);
	}

	/**
	 * getMessages
	 *
	 * @param mixed bool
	 * @return void
	 */
	public function getMessages(bool $detailed = false) : array
	{
		$messages = array_values($this->messages);

		return ($detailed) ? ['messages'=>$messages,'count'=>count($this->messages),'initial_pause'=>$this->initialPause,'pause_for_each'=>$this->pauseForEach] : $messages;
	}

	/**
	 *
	 * Add multiple messages at one time
	 *
	 * @access public
	 *
	 * @param array $array
	 * @param string $type blue
	 *
	 * @return \Wallet
	 *
	 * #### Example
	 * ```php
	 * ci('wallet')->msgs(['Whoops!','Defcon 1'=>'red','Info']);
	 * ```
	 */
	public function msgs(array $array, string $type = null) : Wallet
	{
		$type = ($type) ?? $this->defaultType;

		foreach ($array as $a=>$b) {
			if (is_numeric($a)) {
				$this->msg($b, $type);
			} else {
				$this->msg($a, $b);
			}
		}

		return $this;
	}

	/**
	 *
	 * Set the page view variable
	 *
	 * @access public
	 *
	 * @param $messages
	 *
	 * @return \Wallet
	 *
	 */
	public function setViewVariable(string $variable = null) : Wallet
	{
		$variable = ($variable) ?? $this->viewVariable;

		$this->load->vars([$variable => $this->getMessages(true)]);

		return $this;
	}

} /* end class */