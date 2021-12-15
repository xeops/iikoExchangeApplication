<?php

namespace AppBundle\Controller;

use iikoExchangeBundle\Application\Period;
use iikoExchangeBundle\Application\Restaurant;
use iikoExchangeBundle\Contract\Schedule\ScheduleInterface;
use iikoExchangeBundle\Exception\ExchangeParameters;
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

		$exchange = $this->get('exchange.directory')->getExchangeByCode($request->query->get('exchangeCode'));
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

			$params->setRestaurant(new Restaurant($request->query->getInt('restaurantId'), 'My restaurant'));
		}

		if ($this->get('exchange.manager')->startExchange($exchange, ScheduleInterface::TYPE_PREVIEW, $params) !== false)
		{
			if ($this->get('exchange.storage.preview')->existData($exchange))
			{
				return $this->render($exchange->getPreviewTemplate(), $this->get('exchange.storage.preview')->getData($exchange));
			}
		}

		return new JsonResponse($this->get('exchange.storage.preview')->getError($exchange) ?? 'Unknown error.', 500);
	}
}
