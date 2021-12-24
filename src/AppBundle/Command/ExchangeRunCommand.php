<?php

namespace AppBundle\Command;

use iikoExchangeBundle\Application\Period;
use iikoExchangeBundle\Application\Restaurant;
use iikoExchangeBundle\Contract\Schedule\ScheduleInterface;
use iikoExchangeBundle\Exception\ExchangeParameters;
use iikoExchangeBundle\ExtensionHelper\PeriodicalExtensionHelper;
use iikoExchangeBundle\ExtensionHelper\WithRestaurantExtensionHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class ExchangeRunCommand extends ContainerAwareCommand
{
	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this
			->setName('exchange:run')
			->addArgument('exchange', InputArgument::REQUIRED, 'Pass an exchange code')
			->addOption('restaurant', 'r', InputOption::VALUE_OPTIONAL, 'Restaurant ID (some int value)', 1)
			->addOption('date-from', 'f', InputOption::VALUE_OPTIONAL, 'Start of the period', 'now')
			->addOption('date-to', 't', InputOption::VALUE_OPTIONAL, 'End of the period', 'now')
			->addOption('exchange-id', 'i', InputOption::VALUE_OPTIONAL, 'Exchange id', 1)
			->setDescription('Run the exchange');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$exchange = $this->getContainer()->get('exchange.directory')->get($input->getArgument('exchange'));
		$exchange->setId($input->getOption('exchange-id'));

		$params = new ExchangeParameters();
		if (PeriodicalExtensionHelper::isNeedPeriod($exchange))
		{
			$params->setPeriod(new Period($input->getOption('date-from'), $input->getOption('date-to')));

		}
		if (WithRestaurantExtensionHelper::isNeedRestaurant($exchange))
		{
			$params->setRestaurant(new Restaurant($input->getOption('restaurant'), 'My restaurant'));
		}


		$this->getContainer()->get('exchange.manager')->startExchange($exchange, ScheduleInterface::TYPE_MANUAL, $params);
	}
}
