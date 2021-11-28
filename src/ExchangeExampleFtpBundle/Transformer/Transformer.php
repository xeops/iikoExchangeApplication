<?php


namespace ExchangeExampleFtpBundle\Transformer;


use ExchangeExampleFtpBundle\Mapping\DepartmentMapping;
use ExchangeExampleFtpBundle\Request\SalesRequest;
use iikoExchangeBundle\Configuration\ConfigType\ConfigItemBoolean;
use iikoExchangeBundle\Contract\Extensions\WithMappingExtensionInterface;
use iikoExchangeBundle\Contract\Extensions\WithRestaurantExtensionInterface;
use iikoExchangeBundle\Engine\ExchangeEngine;
use iikoExchangeBundle\Exchange\Exchange;
use iikoExchangeBundle\ExtensionTrait\WithMappingExtensionTrait;
use iikoExchangeBundle\ExtensionTrait\WithRestaurantExtensionTrait;
use iikoExchangeBundle\Library\Request\iiko\Report\Sales\AggregateFields;
use iikoExchangeBundle\Library\Transform\AbstractTransformer;

class Transformer extends AbstractTransformer implements WithMappingExtensionInterface, WithRestaurantExtensionInterface
{
	const CONFIG_REVENUE_TYPE = 'REVENUE_TYPE';

	use WithMappingExtensionTrait;
	use WithRestaurantExtensionTrait
	{
		WithRestaurantExtensionTrait::jsonSerialize as restaurantJsonSerialize;
	}

	public function jsonSerialize()
	{
		return parent::jsonSerialize() + $this->restaurantJsonSerialize();
	}


	public function transform(Exchange $exchange, ExchangeEngine $exchangeEngine, $data)
	{
		$data = $data[SalesRequest::CODE] ?? [];

		$result = [];
		if (!empty($data))
		{
			foreach ($data as $datum)
			{
				$result[] = [
					$this->getMappingValue(DepartmentMapping::CODE, [DepartmentMapping::ID_DEPARTMENT => $this->getRestaurant()->getId()], DepartmentMapping::VALUE_DEPARTMENT),
					$datum[$this->getConfigValue(self::CONFIG_REVENUE_TYPE) ? AggregateFields::DishDiscountSumIntWithoutVAT : AggregateFields::DishDiscountSumInt],
					$datum[AggregateFields::GuestNum],
					$datum[AggregateFields::UniqOrderIdOrdersCount]
				];
			}
		}

		return $result;

	}

	public function exposeConfiguration(): array
	{
		return [
			new ConfigItemBoolean(self::CONFIG_REVENUE_TYPE, true)
		];
	}
}