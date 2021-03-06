<?php

namespace AppBundle\Controller;

use iikoExchangeBundle\Application\Period;
use iikoExchangeBundle\Application\Restaurant;
use iikoExchangeBundle\Contract\Schedule\ScheduleInterface;
use iikoExchangeBundle\Exception\ExchangeParameters;
use iikoExchangeBundle\ExtensionHelper\WithRestaurantExtensionHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PreviewController extends Controller
{
	/**
	 * @Route("/api/exchange/preview/start")
	 */
	public function startExchangePreview(Request $request)
	{

		$exchange = $this->get('exchange.directory')->get($request->query->get('exchangeCode'));
		$exchange->setId($request->query->getInt('exchangeId', 1));

		if ($exchange->getPreviewTemplate() === null)
		{
			return new JsonResponse('Exchange doesnt have implementation of preview module', 500);
		}

		$params = new ExchangeParameters();
		if ($request->query->has('from') && $request->query->has('to'))
		{
			$params->setPeriod(new Period($request->query->get('from'), $request->query->get('to')));
		}
		if ($request->query->has('restaurant'))
		{
			$params->setRestaurant(new Restaurant($request->query->getInt('restaurant'), 'My restaurant', new \DateTimeZone(date_default_timezone_get())));
		}
		elseif (WithRestaurantExtensionHelper::isNeedRestaurant($exchange))
		{
			return new JsonResponse('Exchange need to restaurant. Pass via ?restaurantId=%d', 500);
		}

		if ($request->query->has('restaurants'))
		{

			$params->setRestaurantCollection(array_map(fn($item) => new
			Restaurant(
				(int)$item,
				"My restaurant#{$item}",
				new \DateTimeZone(date_default_timezone_get())
			), $request->query->get('restaurants')));

		}
		elseif (WithRestaurantExtensionHelper::isNeedMultiRestaurant($exchange))
		{
			return new JsonResponse('Exchange need to restaurant. Pass via ?restaurants[]=%d&restaurants[]=%d', 500);
		}


		if ($this->get('exchange.manager')->startExchange($exchange, ScheduleInterface::TYPE_PREVIEW, $params) !== false)
		{
			if ($this->get('exchange.storage.preview')->existData($exchange))
			{
				return $this->render($exchange->getPreviewTemplate(), ['data' => $this->get('exchange.storage.preview')->getData($exchange)]);
			}
		}

		return new JsonResponse($this->get('exchange.storage.preview')->getError($exchange) ?? 'Unknown error.', 500);
	}
}
