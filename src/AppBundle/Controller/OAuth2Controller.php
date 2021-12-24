<?php

namespace AppBundle\Controller;

use iikoExchangeBundle\Contract\Connection\OAuth2ConnectionInterface;
use iikoExchangeBundle\Contract\Exchange\ExchangeInterface;
use iikoExchangeBundle\Contract\Service\ExchangeConfigStorageInterface;
use iikoExchangeBundle\Exception\ConfigNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OAuth2Controller extends Controller
{
	/**
	 * @Route("/api/exchange/oauth2/goto")
	 * @Method("GET")
	 */
	public function getOAuth2Uri(Request $request)
	{
		$exchangeSkeleton = $this->get('exchange.directory')->get($request->query->get('exchangeCode'));
		$exchangeSkeleton->setId($request->query->getInt('exchangeId', 1));
		/** @var ExchangeConfigStorageInterface $exchangeConfigStorage */
		$exchangeConfigStorage = $this->get('exchange.storage.config');

		$type = $request->query->get('type', 'loader');
		/** @var OAuth2ConnectionInterface $node */
		$node = $type === 'loader' ? $exchangeSkeleton->getLoader() : $exchangeSkeleton->getExtractor();

		$redirectUri = $this->getRedirectUrl($request, $exchangeSkeleton, $type);

		$exchangeConfigStorage->appendConfiguration($exchangeSkeleton, $node, $node::CONFIG_REDIRECT_URI, $redirectUri);

		$config = $exchangeConfigStorage->getConfiguration($exchangeSkeleton, $type === 'loader' ? $exchangeSkeleton->getLoader() : $exchangeSkeleton->getExtractor());
		$node->setConfigCollection($config);


		return $this->redirect($node->getRedirectToLoginUrl($config[OAuth2ConnectionInterface::CONFIG_CLIENT_ID], $redirectUri));
	}

	private function getRedirectUrl(Request $request, ExchangeInterface $exchange, string $type)
	{
		return sprintf("%s%s",
			str_replace([$request->getPathInfo(), $request->getQueryString(), "?"], "", $request->getUri()),
			$this->generateUrl("Exchange.RedirectUri", ['exchangeCode' => $exchange->getCode(), 'exchangeId' => $exchange->getId(), 'type' => $type])
		);
	}

	/**
	 * @Route("/api/exhange/oauth2/auth/{exchangeCode}/{exchangeId}/{type}", name="Exchange.RedirectUri")
	 * @Method("GET")
	 */
	public function redirectUri(Request $request, string $exchangeCode, int $exchangeId, string $type)
	{
		$exchangeSkeleton = $this->get('exchange.directory')->get($exchangeCode);
		$exchangeSkeleton->setId($exchangeId);

		if ($request->query->has('error'))
		{
			return new JsonResponse($request->query->get('error'));
		}

		$code = $request->query->get('code');

		/** @var ExchangeConfigStorageInterface $exchangeConfigStorage */
		$exchangeConfigStorage = $this->get('exchange.storage.config');

		/** @var OAuth2ConnectionInterface $node */
		$node = $type === 'loader' ? $exchangeSkeleton->getLoader() : $exchangeSkeleton->getExtractor();

		$config = $exchangeConfigStorage->getConfiguration($exchangeSkeleton, $type === 'loader' ? $exchangeSkeleton->getLoader() : $exchangeSkeleton->getExtractor());
		$node->setConfigCollection($config);
		try
		{
			$authData = $node->getAccessToken($code);
		}
		catch (\Exception | \Throwable | ConfigNotFoundException $exception)
		{
			return new JsonResponse($exception->getMessage(), 500);
		}
		if (isset($authData['error']) && $authData['error'])
		{
			return new JsonResponse($authData['error'] . ": " . ($authData['error_description'] ?? null));
		}

		foreach ($node->getAuthDataMapping() as $configCode => $valueCode)
		{
			$exchangeConfigStorage->appendConfiguration($exchangeSkeleton, $node, $configCode, $authData[$valueCode]);
		}

		return new JsonResponse('ALL OK! Close the window');
	}

}
