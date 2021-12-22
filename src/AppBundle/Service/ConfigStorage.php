<?php

namespace AppBundle\Service;

use iikoExchangeBundle\Application\Restaurant;
use iikoExchangeBundle\Contract\Exchange\ExchangeInterface;
use iikoExchangeBundle\Contract\ExchangeNodeInterface;
use iikoExchangeBundle\Contract\Service\ExchangeConfigStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ConfigStorage implements ExchangeConfigStorageInterface
{
	private string $rootDir;

	/**
	 * @param string $rootDir
	 */
	public function setRootDir(string $rootDir): void
	{
		$this->rootDir = $rootDir;
	}

	public function getConfiguration(ExchangeInterface $exchange, ExchangeNodeInterface $exchangeNode, ?Restaurant $restaurant = null): array
	{
		$fileName = $this->getConfigDir() . DIRECTORY_SEPARATOR . $this->getFileName($exchange);
		if (!file_exists($fileName))
		{
			throw new \Exception("Configuration file {$fileName} doesn't exist");
		}

		$config = json_decode(file_get_contents($fileName), true)['config'] ?? null;
		if ($config === null)
		{
			throw new \Exception("Configuration file {$fileName} doesn't contain configuration");
		}

		return $config[$exchangeNode->getCode()] ?? [];
	}

	public function saveConfiguration(ExchangeInterface $exchange, ExchangeNodeInterface $exchangeNode, array $configuration, ?Restaurant $restaurant = null)
	{
		if (!@dir($this->getConfigDir()) && @mkdir($this->getConfigDir()) === false)
		{
			throw new \Exception('Cant create directory ' . $this->getConfigDir());
		}
		if (!file_exists($this->getConfigDir() . DIRECTORY_SEPARATOR . $this->getFileName($exchange)))
		{
			$fileContent = [];
		}
		else
		{
			$fileContent = json_decode(file_get_contents($this->getConfigDir() . DIRECTORY_SEPARATOR . $this->getFileName($exchange)), true);
		}

		$fileContent['config'] = array_merge($fileContent['config'] ?? [], [$exchangeNode->getCode() => $configuration]);

		file_put_contents($this->getConfigDir() . DIRECTORY_SEPARATOR . $this->getFileName($exchange), json_encode($fileContent));

	}

	public function appendConfiguration(ExchangeInterface $exchange, ExchangeNodeInterface $exchangeNode, string $code, $value, ?Restaurant $restaurant = null)
	{
		if (!@dir($this->getConfigDir()) && @mkdir($this->getConfigDir()) === false)
		{
			throw new \Exception('Cant create directory ' . $this->getConfigDir());
		}
		if (!file_exists($this->getConfigDir() . DIRECTORY_SEPARATOR . $this->getFileName($exchange)))
		{
			$fileContent = [];
		}
		else
		{
			$fileContent = json_decode(file_get_contents($this->getConfigDir() . DIRECTORY_SEPARATOR . $this->getFileName($exchange)), true);
		}
		if (!isset($fileContent['config']))
		{
			$fileContent['config'] = [];
		}
		if (!isset($fileContent['config'][$exchangeNode->getCode()]))
		{
			$fileContent['config'][$exchangeNode->getCode()] = [];
		}
		$fileContent['config'][$exchangeNode->getCode()][$code] = $value;

		file_put_contents($this->getConfigDir() . DIRECTORY_SEPARATOR . $this->getFileName($exchange), json_encode($fileContent));
	}

	private function getConfigDir(): string
	{
		return $this->rootDir . '/../var/exchangeConfig';
	}

	private function getFileName(ExchangeInterface $exchange): string
	{
		return "exchange.{$exchange->getCode()}.{$exchange->getId()}.cfg.json";
	}
}