<?php

namespace App\TestCases;

use Config\Config;
use Library\LoggerFactory;
use Library\Utility;
use Library\FileHandler;

/**
 * Base Test class that will be used to build test cases for
 * automated API testing
 * 
 * @package App\TestCases
 */
class TestBase
{
	/**
	 * Response from the api calls
	 * 
	 * @var string
	 */
	public $response;
	
	/**
	 * Result of the parsed response
	 * 
	 * @var string
	 */
	public $result;
	
	/**
	 * API URL for submitting REST requests
	 * 
	 * @var string
	 */
	public $restUrl;

	/**
	 * @var Config|null
	 */
	public $config = null;

	/**
	 * @var array
     */
	public $method = null;

	/**
	 * @var bool
     */
	public $dataProvider = false;

	/**
	 * Array containing the list of dependencies for a test case
	 * 
	 * @var array
	 */
	public $dependencies;
	
	/**
	 * TestBase constructor
	 */
	public function __construct()
	{
		if (is_null($this->config))
			$this->config = new Config();
	}
	
	/**
	 * Setter for $restUrl
	 * 
	 * @param string $url
	 */
	public function setRestUrl($url)
	{
		if(!is_null($url) && !empty($url))
		{
			$this->restUrl = $url;
		}
	}

	/**
	 * Setter for $method
	 *
	 * @param $method
     */
	public function setMethod($method)
	{
		if(!is_null($method) && !empty($method))
		{
			$this->method = $method;
		}
	}

	/**
	 * @param $testCase
	 * @param string $mode
     */
	public function setTestCaseProperties($testCase, $mode = 'rest')
	{
		$this->method = Utility::getClassShortName($testCase);
		if($mode=='soap'){
			$this->dataProvider = Utility::isSoapDataProvider($this->method);
			$this->dependencies = Utility::getSoapDependenciesByTestCase($this->method);
		} else {
			$this->dataProvider = Utility::isRestDataProvider($this->method);
			$this->dependencies = Utility::getRestDependenciesByTestCase($this->method);
		}
	}
	
	/**
	 * Setter for $dependency
	 * 
	 * @param array $dependencies
	 */
	public function setDependencies(array $dependencies)
	{
		if(!empty($dependencies))
			$this->dependencies = $dependencies;
	}
	
	/**
	 * Method for testing SOAP based endpoints
	 * 
	 * @param string $method
	 * @param array $params
	 * @param string $options
	 */
	public function executeSoapRequest($method, array $params, $options = null)
	{
		$logData = new \stdClass();
		$eLog = LoggerFactory::create('error');
		$dLog = LoggerFactory::create('data');

		if(!empty($method))
		{
			try 
			{
				if(is_null($options) || empty($options))
					$client = new \SoapClient(SOAP_WSDL);
				else
					$client = new \SoapClient(SOAP_WSDL, $options);


				$this->response = $client->__soapCall($method, $params);

				if($this->dataProvider) $this->saveSoapRequest();

				$logData->soapRequest = $client->__getLastRequest();
				$logData->soapResponse = $client->__getLastResponse();
				$logData->timestamp = date('Y-m-d h:i:s');

				$dLog->writeToLog("SOAP Transaction: " . print_r($logData, true));

				$this->responseHandler('soap');
			}
			catch (\Exception $e)
			{
				$eLog->writeToLog($e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL);
			}
		}
	}
	
	/**
	 * Method for testing REST based endpoints
	 * @param array $params
	 * @param string $mode
	 * @param array $options
	 */
	public function executeRestRequest(array $params, $mode, array $options = null)
	{
		$logData = new \stdClass();
		$eLog = LoggerFactory::create('error');
		$dLog = LoggerFactory::create('data');

		switch (strtolower($mode))
		{
			case "get": 
				$c = curl_init($this->restUrl . "?" . http_build_query($params));
				curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
				break;
			case "post" :
			default:
			$c = curl_init($this->restUrl);
			curl_setopt($c, CURLOPT_POST, 1);
			curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query($params));
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
			break;
		}
		
		if(!is_null($options) && !empty($options))
		{
			foreach($options as $key => $option)
			{
				curl_setopt($c, $key, $option);
			}
		}
		
		$this->response = curl_exec($c);

		if(curl_errno($c))
		{
			$eLog->writeToLog(curl_error($c));
			$logData->message = "Error encountered: " . curl_error($c);
		}

		$logData->url = $this->restUrl;
		$logData->restRequest = $this->restUrl . "?" . http_build_query($params);
		$logData->mode = ($mode == strtolower('post') || $mode == strtolower('rest') ? $mode : "No mode provided, used POST");
		$logData->restResponse = $this->response;
		$logData->timestamp = date('Y-m-d h:i:s');

		$dLog->writeToLog("REST Transaction: " . print_r($logData, true));

		if($this->dataProvider) $this->saveRestRequest();

		$this->responseHandler('rest');
		
		curl_close($c);
	}
	
	/**
	 * Processes the response from an endpoint request
	 * 
	 * @param string $mode
	 * @return null|\stdClass|\SimpleXMLElement|NULL
	 */
	public function responseHandler($mode = 'soap')
	{
		$parsedResponse = null;
		if(!is_null($this->response) && !empty($this->response))
		{
			switch (strtolower($mode))
			{
				case "rest":
					$this->result = json_decode($this->response, true);
					return $this->result;
					break;
				case "soap" :
					$this->result = new \SimpleXMLElement($this->response);
					return $this->result;
				default:
					break;
			}
		}
		return $parsedResponse;
	}
	
	/**
	 * Fetches the response data from the dependency file 
	 * 
	 * @return boolean|array False on failure
	 */
	public function getDependencyData()
	{
		$fh = new FileHandler();
		$load = array();
		$filename = null;
		$path = (isset($this->config->tmp_path) ? $this->config->tmp_path : dirname(dirname(dirname(__FILE__))) . "/tmp");
		$log = LoggerFactory::create('error');

		if(!is_null($this->dependencies) && !empty($this->dependencies))
		{
			foreach($this->dependencies as $key => $dependency)
			{
				if(Utility::isSoapDataProvider($dependency))
				$filename = $dependency . ".xml";
			elseif(Utility::isRestDataProvider($dependency))
				$filename = $dependency . ".json";
			else
				$log->writeToLog("Dependency isn't found as a data provider");

			if(!is_null($filename))
				$load[$dependency] = $fh->loadFile($path . "/" . $filename);
			else
				return false;
			}
		}
	else
		{
			return false;
		}

		return (!empty($load) ? $load : false);
	}

	/**
	 * save raw rest response to file, overwrite if it already exists
     */
	public function saveRestRequest()
	{
		//save raw response to file, overwrite if it already exists
		$fileHandler = new FileHandler();
		$fileHandler->createFile($this->config->tmp_path . '/' . $this->method . '.json', $this->response, true);
	}

	/**
	 * save raw soap response to file, overwrite if it already exists
     */
	public function saveSoapRequest()
	{
		$fileHandler = new FileHandler();
		$fileHandler->createFile($this->config->tmp_path . '/' . $this->method . '.xml', $this->response, true);
	}

	/**
	 * Takes the dependency's parameters and supplied arguments and builds an array of
	 * parameters to be merged into the test case's parameters.
	 *
	 * @param array $dependParams
	 * @param array $args
	 * @return array Empty when there are no associated parameters
	 */
	public function parameterHandler(array $dependParams, array $args = array())
	{
		$returnParams = array();

		//Process dependency parameters if a dependency exists
		if(isset($this->method) && !is_null($this->method))
		{
			$this->dependencies = Utility::getRestDependenciesByTestCase($this->method);
			$data = $this->getDependencyData();

			if($data !== false)
			{
				if(!empty($dependParams))
				{
					$kv = array();
					foreach($dependParams as $key => $value)
					{
						array_walk_recursive($data, function(&$v, $k) use (&$kv, $value) { if($k === $value) $kv[$value] = $v; });
					}

					if(!empty($kv))
					{
						foreach($kv as $k => $v)
						{
							$returnParams[$k] = $v;
						}
					}
				}
			}
		}

		//Supplied argument override all other parameters
		if(!empty($args))
		{
			foreach($args as $key => $value)
			{
				$returnParams[$key] = $value;
			}
		}

		return $returnParams;

	}

}