<?php


namespace ExchangeExampleFtpBundle\Mapping;


use iikoExchangeBundle\Configuration\ConfigType\ConfigItemSelect;
use iikoExchangeBundle\Configuration\ConfigType\ConfigItemString;
use iikoExchangeBundle\Mapping\AbstractMapping;

class PaymentTypeMapping extends AbstractMapping
{

	const CODE = 'MAPPING_PAYMENT_TYPE';

	const ID_PAY_TYPE = 'MAPPING_ID_PAYMENT_TYPE';

	const VALUE_ACCOUNT_CODE = 'MAPPING_VALUE_ACCOUNT_CODE';


	public function __construct()
	{
		parent::__construct(self::CODE);
	}

	public function exposeIdentifiers(): array
	{
		return [
			new ConfigItemSelect(self::ID_PAY_TYPE, "PaymentType", "")
		];
	}

	public function exposeValues(): array
	{
		return [
			new ConfigItemString(self::VALUE_ACCOUNT_CODE)
		];
	}
}