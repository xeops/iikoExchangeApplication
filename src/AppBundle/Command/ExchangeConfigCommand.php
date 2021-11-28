<?php

namespace AppBundle\Command;

use iikoExchangeBundle\Contract\Configuration\ConfigType\ConfigItemInterface;
use iikoExchangeBundle\Contract\ExchangeNodeInterface;
use iikoExchangeBundle\Contract\Extensions\ConfigurableExtensionInterface;
use iikoExchangeBundle\Contract\Mapping\MappingInterface;
use iikoExchangeBundle\ExtensionHelper\WithMappingExtensionHelper;
use Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class ExchangeConfigCommand extends ContainerAwareCommand
{
	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this
			->setName('exchange:config:fill')
			->addArgument('exchange-code', InputOption::VALUE_REQUIRED, 'Exchange code')
			->addArgument('exchange-id', InputOption::VALUE_REQUIRED, 'Exchange id')
			->setDescription('Set exchange config&mapping');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$directory = $this->getContainer()->get('exchange.directory');
		$questionHelper = $this->getQuestionHelper();

		$exchange = $directory->getExchangeByCode($input->getArgument('exchange-code'));
		$exchange->setId($input->getArgument('exchange-id'));


		$configDir = $this->getContainer()->getParameter('kernel.root_dir') . '/../var/exchangeConfig';
		if (!@dir($configDir) && @mkdir($configDir) === false)
		{
			$output->writeln('Cant create directory');
			return;
		}


		/** @var MappingInterface[] $mapping */
		$mapping = WithMappingExtensionHelper::extractMapping($exchange);

		$config = [
			'mapping' => array_fill_keys(array_map(fn($map) => $map->getCode(), $mapping), []),
			'config' => array_fill_keys(array_map(fn(ConfigurableExtensionInterface $configurable) => $configurable->getCode(), array_filter($this->getAllNodes($exchange), fn($node) => $node instanceof ConfigurableExtensionInterface)), [])
		];

		$output->writeln('Mapping:');

		foreach ($mapping as $item)
		{
			$output->writeln($item->getCode());
			while ($questionHelper->ask($input, $output, new Question($questionHelper->getQuestion('Do you want add a mapping row? (y/n): ', false))) === 'y')
			{
				$config['mapping'][$item->getCode()] = [
					MappingInterface::FIELD_IDENTIFIERS => array_combine(
						array_map(fn(ConfigItemInterface $configItem) => $configItem->getCode(), $item->exposeIdentifiers()),
						array_map(fn(ConfigItemInterface $configItem) => $questionHelper->ask($input, $output, new Question($questionHelper->getQuestion("IDENTIFIER (" . $configItem->getCode() . ")", false))), $item->exposeIdentifiers()),
					),
					MappingInterface::FIELD_VALUES => array_combine(
						array_map(fn(ConfigItemInterface $configItem) => $configItem->getCode(), $item->exposeValues()),
						array_map(fn(ConfigItemInterface $configItem) => $questionHelper->ask($input, $output, new Question($questionHelper->getQuestion("VALUE (" . $configItem->getCode() . ")", false))), $item->exposeValues()),
					),
				];
			}
		}

		$output->writeln("Configuration:");

		foreach ($this->getAllNodes($exchange) as $node)
		{
			if ($node instanceof ConfigurableExtensionInterface && $node->exposeConfiguration())
			{
				$output->writeln($node->getCode());
				foreach ($node->exposeConfiguration() as $configItem)
				{
					$config['config'][$node->getCode()][$configItem->getCode()] = $questionHelper->ask($input, $output, new Question($questionHelper->getQuestion("VALUE (" . $configItem->getCode() . ")", $configItem->getValue())));
				}
			}
		}

		file_put_contents($configDir . "/exchange.{$exchange->getCode()}.{$exchange->getId()}.cfg.json", json_encode($config));
	}

	private function getAllNodes(ExchangeNodeInterface $node)
	{
		$result = [$node];
		foreach ($node->getChildNodes() as $childNode)
		{
			$result = array_merge($result, $this->getAllNodes($childNode));
		}
		return $result;
	}

	protected function getQuestionHelper()
	{
		$question = $this->getHelperSet()->get('question');
		if (!$question || get_class($question) !== 'Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper')
		{
			$this->getHelperSet()->set($question = new QuestionHelper());
		}

		return $question;
	}
}
