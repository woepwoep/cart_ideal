<?php
namespace RedSeadog\SfEventMgtIdeal\Service;


use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Service\TypoScriptService;

/**
 *  PluginService
 */
class PluginService implements \TYPO3\CMS\Core\SingletonInterface
{
	protected $extName;
	protected $pluginSettings;
	protected $fullTsArray;

    /**
     * Constructs an instance of PluginService.
	 *
	 * @param string $extName
     */
    public function __construct($extName)
    {
		$this->extName = $extName;

		$objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
		$this->fullTsConf = $configurationManager->getConfiguration(
			\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
		$tsService = new TypoScriptService();
		$this->fullTsArray = $tsService->convertTypoScriptArrayToPlainArray($this->fullTsConf);
		$this->pluginSettings = $this->fullTsArray['plugin'][$extName];
		if (!is_array($this->pluginSettings)) {
			debug('PluginService: no such extension plugin found: '.$extName);
			exit(1);
		}
	}

	/** @return string */
	public function getIdealConfigurationFile()
	{
		$idealCf = $this->pluginSettings['settings']['idealConfigurationFile'];
		if ($idealCf[0] != '/') {
			$idealCf = GeneralUtility::getFileAbsFileName($idealCf);
		}
		return $idealCf;
	}

	/** @return string */
	public function getMerchantReturnUrl()
	{
		$linkConf = array(
			'parameter' => $this->pluginSettings['settings']['idealPid'],
			'forceAbsoluteUrl' => 1,
			'returnLast' => 'url'
		);
		$url = $GLOBALS['TSFE']->cObj->typoLink('', $linkConf);
		return $url;
	}

	/**
	 * @return int
	 */
	public function getPaymentReturnPid()
	{
		return (int)$this->pluginSettings['settings']['paymentReturnPid'];
	}

	/**
	 * @return string
	 */
	public function getPaymentForm()
	{
		return $this->pluginSettings['settings']['paymentForm'];
	}

	/** @return string */
	public function getLayoutRootPaths()
	{
		return $this->pluginSettings['view']['layoutRootPaths'];
	}

	/** @return string */
	public function getPartialRootPaths()
	{
		return $this->pluginSettings['view']['partialRootPaths'];
	}

	/** @return string */
	public function getTemplateRootPaths()
	{
		return $this->pluginSettings['view']['templateRootPaths'];
	}

	/**
	 * @param string $controller
	 * @param string $templateName
	 * @return string
	 */
	public function getTemplatePathAndFilename($controller,$templateName)
	{
		// find the template file
		$foundFile = '';
		$pathNames = $this->getTemplateRootPaths();
		if (empty($pathNames)) {
			debug('No templateRootPaths set for plugin '.$this->extName);
			exit(1);
		}
		foreach($pathNames as $pathName) {
			$tryFile = GeneralUtility::getFileAbsFileName($pathName).$controller.'/'.$templateName;
			if (file_exists($tryFile)) {
				$foundFile = $tryFile;
			}
		}
		if (!$foundFile) {
			debug($pathNames);
			debug('PluginService: could not find controller/template '.$controller.'/'.$templateName.'.');exit(1);
		}
		return $foundFile;
	}

	/**
	 *
	 */
	public function getSettings()
	{
		return $this->pluginSettings['settings'];
	}

	/**
	 *
	 */
	public function getAllSettings()
	{
		return $this->fullTsArray;
	}
}
