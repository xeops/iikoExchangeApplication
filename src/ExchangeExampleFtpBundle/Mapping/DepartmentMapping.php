<?php


namespace ExchangeExampleFtpBundle\Mapping;


use iikoExchangeBundle\Configuration\ConfigType\ConfigItemSelect;
use iikoExchangeBundle\Configuration\ConfigType\ConfigItemString;
use iikoExchangeBundle\Mapping\AbstractMapping;

class DepartmentMapping  extends AbstractMapping
{
	const CODE = "MAPPING_DEPARTMENT";
	const ID_DEPARTMENT = 'MAPPING_ID_DEPARTMENT_IIKO';
	const VALUE_DEPARTMENT = 'MAPPING_VALUE_DEPARTMENT';

	protected bool $useRestaurant = false;

	public function __construct()
	{
		parent::__construct(self::CODE);
	}

	public function isFullTable(): bool
	{
		return true;
	}

	public function exposeIdentifiers(): array
	{
		return [
			new ConfigItemSelect(self::ID_DEPARTMENT, "DEPARTMENT")
		];
	}

	public function exposeValues(): array
	{
		return [
			new ConfigItemString(self::VALUE_DEPARTMENT)
		];
	}
}