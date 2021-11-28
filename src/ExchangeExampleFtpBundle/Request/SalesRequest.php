<?php


namespace ExchangeExampleFtpBundle\Request;


use ExchangeExampleFtpBundle\Mapping\PaymentTypeMapping;
use IikoApiBundle\Reports\Olap\Version52\Delco\FilterFields;
use IikoApiBundle\Reports\Olap\Version52\Sales\AggregateFields;
use iikoExchangeBundle\Contract\Extensions\WithMappingExtensionInterface;
use iikoExchangeBundle\ExtensionTrait\WithMappingExtensionTrait;
use iikoExchangeBundle\Library\Request\iikoSalesOlapDSRequest;

class SalesRequest extends iikoSalesOlapDSRequest implements WithMappingExtensionInterface
{

	use WithMappingExtensionTrait;

	const CODE = 'SALES';

	public function __construct()
	{
		parent::__construct(self::CODE);
	}

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

	public function processResponse(string $data)
	{
		return json_decode($data, true)['data'];
	}


	public function getFilters()
	{
		$paymentTypes = $this->getUniqMappingIdentifiers(PaymentTypeMapping::CODE, PaymentTypeMapping::ID_PAY_TYPE);

		return parent::getFilters() +
			[
				FilterFields::PayTypesGUID => [
					"filterType" => "IncludeValues",
					"values" => $paymentTypes
				]
			];
	}
}