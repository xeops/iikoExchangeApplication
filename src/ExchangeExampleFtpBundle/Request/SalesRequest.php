<?php


namespace ExchangeExampleFtpBundle\Request;


use ExchangeExampleFtpBundle\Mapping\PaymentTypeMapping;

use iikoExchangeBundle\Contract\Extensions\WithMappingExtensionInterface;
use iikoExchangeBundle\ExtensionTrait\WithMappingExtensionTrait;
use iikoExchangeBundle\ExtensionTrait\WithPeriodExtensionTrait;
use iikoExchangeBundle\Library\Request\iiko\Report\Sales\AggregateFields;
use iikoExchangeBundle\Library\Request\iiko\Report\Sales\FilterFields;
use iikoExchangeBundle\Library\Request\iikoOlapRequest;
use iikoExchangeBundle\Library\Request\iikoSalesOlapDSRequest;

class SalesRequest extends iikoOlapRequest implements WithMappingExtensionInterface
{
	use WithMappingExtensionTrait;

	const CODE = 'SALES';

	public function getType(): string
	{
		return self::TYPE_SALES;
	}


	public function getGroupFields(): array
	{
		return [];
	}

	public function getAggregateFields(): array
	{
		return [
			AggregateFields::DishDiscountSumInt,
			AggregateFields::DishDiscountSumIntWithoutVAT,
			AggregateFields::GuestNum,
			AggregateFields::UniqOrderIdOrdersCount
		];
	}


	public function getFilters(): array
	{
		$paymentTypes = $this->getUniqMappingIdentifiers(PaymentTypeMapping::CODE, PaymentTypeMapping::ID_PAY_TYPE);

		return
			[
				FilterFields::PayTypesGUID => [
					"filterType" => "IncludeValues",
					"values" => $paymentTypes
				]
			];
	}
}