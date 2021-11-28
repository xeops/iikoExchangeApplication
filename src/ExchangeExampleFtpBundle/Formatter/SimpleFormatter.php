<?php


namespace ExchangeExampleFtpBundle\Formatter;


use ExchangeExampleFtpBundle\Mapping\DepartmentMapping;
use ExchangeExampleFtpBundle\Request\SalesRequest;
use iikoExchangeBundle\Configuration\ConfigType\ConfigItemString;
use iikoExchangeBundle\Contract\Extensions\WithMappingExtensionInterface;
use iikoExchangeBundle\Contract\Extensions\WithPeriodExtensionInterface;
use iikoExchangeBundle\Contract\Extensions\WithRestaurantExtensionInterface;
use iikoExchangeBundle\Exchange\Exchange;
use iikoExchangeBundle\ExtensionTrait\WithMappingExtensionTrait;
use iikoExchangeBundle\ExtensionTrait\WithPeriodExtensionTrait;
use iikoExchangeBundle\ExtensionTrait\WithRestaurantExtensionTrait;
use iikoExchangeBundle\Format\Formatter;


class SimpleFormatter extends Formatter implements WithMappingExtensionInterface, WithRestaurantExtensionInterface, WithPeriodExtensionInterface
{

	use WithMappingExtensionTrait;
	use WithRestaurantExtensionTrait
	{
		WithRestaurantExtensionTrait::jsonSerialize as restaurantJsonSerialize;
	}
	use WithPeriodExtensionTrait;

	const CONFIG_FILE_PREFIX = 'CONFIG_FILE_PREFIX';
	const CONFIG_FILE_PATH = 'CONFIG_FILE_PATH';

	/**
	 * @param Exchange $exchange
	 * @param array[] $data
	 * @return false|resource
	 */
	public function getFormattedData(Exchange $exchange, $data)
	{

		$navisionStoreCode = $this->getMappingValue(DepartmentMapping::CODE, [DepartmentMapping::ID_DEPARTMENT => $this->getRestaurant()->getId()], DepartmentMapping::VALUE_DEPARTMENT);
		$fileName = strtr("%CONFIG_FILE_PREFIX%_%NavStoreCode%_%YYYYMMDD%_%Timestamp%.csv", [
			"%CONFIG_FILE_PREFIX%" => $this->getConfigValue(self::CONFIG_FILE_PREFIX),
			"%NavStoreCode%" => $navisionStoreCode,
			"%YYYYMMDD%" => $this->getPeriod()->getStartDate()->format('Ymd'),
			"%Timestamp%" => (new \DateTime())->getTimestamp()
		]);

		$path = str_replace("//", "/", sys_get_temp_dir() . "/" . $this->getConfigValue(self::CONFIG_FILE_PATH) . "/");

		if (!is_dir($path))
		{
			mkdir($path, 0700, true);
		}
		$file = fopen($path . $fileName, "w+");

		foreach ($data as $row)
		{
			/** @var array $row */
			fputs($file, implode(";", $row) . PHP_EOL);
		}

		return $file;
	}


	public function exposeConfiguration(): array
	{
		return [
			new ConfigItemString(self::CONFIG_FILE_PREFIX, "SALES_"),
			new ConfigItemString(self::CONFIG_FILE_PATH, "/")
		];
	}

	public function jsonSerialize()
	{
		return parent::jsonSerialize() + $this->restaurantJsonSerialize();
	}
}