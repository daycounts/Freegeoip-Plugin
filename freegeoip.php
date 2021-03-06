<?php defined('_JEXEC') or die;

/**
 * File       freegeoip.php
 * Created    9/17/14 9:20 AM
 * Author     Matt Thomas | matt@betweenbrain.com | http://betweenbrain.com
 * Support    https://github.com/betweenbrain/
 * Copyright  Copyright (C) 2014 betweenbrain llc. All Rights Reserved.
 * License    GNU GPL v2 or later
 */
class plgSystemFreegeoip extends JPlugin
{

	/**
	 * Constructor.
	 *
	 * @param   object &$subject The object to observe
	 * @param   array  $config   An optional associative array of configuration settings.
	 *
	 * @since   0.1
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		$this->app     = JFactory::getApplication();
		$this->session = JFactory::getSession();

		// Load the language file on instantiation
		$this->loadLanguage();
	}

	/**
	 * Event triggered after the framework has loaded and the application initialise method has been called.
	 *
	 * @return bool
	 */
	function onAfterInitialise()
	{
		if ($this->app->isSite())
		{
			if (!$this->session->get('freegeoip_ip'))
			{
				$this->setFreegeoip();
			}
		}

		return true;
	}

	/**
	 * Gets the Freegeoip data about the user
	 *
	 * @return mixed
	 */
	private function getFreegeoip()
	{
		$debug_ip = $this->params->get('debug_ip');
		$ipAddress = ($debug_ip) ? $debug_ip : $_SERVER['REMOTE_ADDR'];

		$curl = curl_init();
		curl_setopt_array($curl, array(
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_URL            => 'http://freegeoip.net/json/' . $ipAddress,
				CURLOPT_FAILONERROR    => true
			)
		);
		$response = curl_exec($curl);

		if (!curl_exec($curl))
		{
			$this->app->enqueueMessage(JText::sprintf('PLG_SYSTEM_FREEGEOIP_CURL_ERROR', curl_error($curl), curl_errno($curl)), 'warning');
		}

		curl_close($curl);

		return $response;
	}

	/**
	 * Sets the Freegeoip data in the user's session
	 *
	 * @return bool
	 */
	private function setFreegeoip()
	{
		$response = $this->getFreegeoip();
		if ($response) {
			$response = json_decode($this->getFreegeoip());
	
			foreach ($response as $key => $value)
			{
				$this->session->set('freegeoip_' . $key, $value);
			}

			return true;
		} else {
			return false;
		}
	}
}
