<?php

namespace AppBundle\Service;


use iikoExchangeBundle\Contract\Exchange\ExchangeInterface;
use iikoExchangeBundle\Contract\Service\ConnectionSessionStorage;

class SessionStorage implements ConnectionSessionStorage
{

	public function set(string $key, string $value)
	{
		if (!@dir($this->getConfigDir()) && @mkdir($this->getConfigDir()) === false)
		{
			throw new \Exception('Cant create directory ' . $this->getConfigDir());
		}
		file_put_contents($this->getConfigDir() . DIRECTORY_SEPARATOR . $this->getFileName($key), $value);
	}

	public function get(string $key): ?string
	{
		if (!@dir($this->getConfigDir()) && @mkdir($this->getConfigDir()) === false)
		{
			throw new \Exception('Cant read directory ' . $this->getConfigDir());
		}

		if (!file_exists($this->getConfigDir() . DIRECTORY_SEPARATOR . $this->getFileName($key)))
		{
			throw new \Exception('Cant read file ' . $this->getConfigDir() . DIRECTORY_SEPARATOR . $this->getFileName($key));
		}

		return file_get_contents($this->getConfigDir() . DIRECTORY_SEPARATOR . $this->getFileName($key));
	}

	public function has(string $key): bool
	{
		if (!@dir($this->getConfigDir()) && @mkdir($this->getConfigDir()) === false)
		{
			throw new \Exception('Cant read directory ' . $this->getConfigDir());
		}

		if (!file_exists($this->getConfigDir() . DIRECTORY_SEPARATOR . $this->getFileName($key)))
		{
			return false;
		}

		return true;
	}

	private function getConfigDir(): string
	{
		return $this->rootDir . '/../var/exchangeConfig';
	}

	private function getFileName(string $sessionKey): string
	{
		return "exchange.session.{$sessionKey}.cfg";
	}

	private string $rootDir;

	/**
	 * @param string $rootDir
	 */
	public function setRootDir(string $rootDir): void
	{
		$this->rootDir = $rootDir;
	}
}