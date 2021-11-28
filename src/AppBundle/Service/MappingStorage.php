<?php

namespace AppBundle\Service;

use iikoExchangeBundle\Application\Restaurant;
use iikoExchangeBundle\Contract\Exchange\ExchangeInterface;
use iikoExchangeBundle\Contract\Service\ExchangeMappingStorageInterface;
use iikoExchangeBundle\Exception\MappingNotFoundException;

class MappingStorage implements ExchangeMappingStorageInterface
{
	private string $rootDir;

	/**
	 * @param string $rootDir
	 */
	public function setRootDir(string $rootDir): void
	{
		$this->rootDir = $rootDir;
	}

	public function saveMapping(ExchangeInterface $exchange, string $mappingCode, array $collection, ?int $restaurantId = null)
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
		$fileContent['mapping'] ??= [];
		$fileContent['mapping'][$mappingCode] = $collection;


		file_put_contents($this->getConfigDir() . DIRECTORY_SEPARATOR . $this->getFileName($exchange), json_encode($fileContent));
	}

	public function getMapping(ExchangeInterface $exchange, string $mappingCode, ?Restaurant $restaurant = null)
	{
		$fileName = $this->getConfigDir() . DIRECTORY_SEPARATOR . $this->getFileName($exchange);
		if (!file_exists($fileName))
		{
			throw new \Exception("Mapping file {$fileName} doesn't exist");
		}

		$config = json_decode(file_get_contents($fileName), true)['mapping'] ?? null;
		if ($config === null)
		{
			throw new \Exception("Mapping file {$fileName} doesn't contain mapping");
		}
		if(!array_key_exists($mappingCode, $config))
		{
			throw new \Exception("Mapping not found in set");
		}

		return $config[$mappingCode];
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