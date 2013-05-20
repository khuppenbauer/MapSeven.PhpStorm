<?php
namespace MapSeven\PhpStorm\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "MapSeven.PhpStorm".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandManager;

/**
 * Console command controller for the MapSeven.PhpStorm package
 *
 * @Flow\Scope("singleton")
 */
class ConsoleCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @var CommandManager
	 */
	protected $commandManager;

	/**
	 * @param CommandManager $commandManager
	 * @return void
	 */
	public function injectCommandManager(CommandManager $commandManager) {
		$this->commandManager = $commandManager;
	}


	/**
	 * Generates XML for PhpStrom Command Line Tools Console
	 *
	 * @return void
	 */
	public function generateXMLCommand() {
		$commands = $this->commandManager->getAvailableCommands();
		$xml = $this->generateXML($commands);

		$dom = new \DOMDocument('1.0', 'UTF-8');
		$dom->preserveWhiteSpace = FALSE;
		$dom->formatOutput = TRUE;
		$dom->loadXML($xml);
		$dom->save(FLOW_PATH_DATA . 'Persistent/commandlinetools.xml');
	}

	/**
	 * @param array<\TYPO3\Flow\Cli\Command> $commands
	 * @return string
	 */
	protected function generateXML(array $commands) {
		$xmlRootNode = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
			<framework xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="schemas/frameworkDescriptionVersion1.1.3.xsd" name="TYPO3Flow" invoke="./flow" alias="flow" enabled="true" version="2" />');
		foreach ($commands as $command) {
			$xmlItem = $xmlRootNode->addChild('command');
			$shortCommandIdentifier = $this->commandManager->getShortestIdentifierForCommand($command);
			$description = $command->getShortDescription();
			$xmlItem->addChild('name', $shortCommandIdentifier);
			$xmlItem->addChild('help', $description);

			$commandArgumentDefinitions = $command->getArgumentDefinitions();
			$params = array();
			foreach ($commandArgumentDefinitions as $commandArgumentDefinition) {
				if ($commandArgumentDefinition->isRequired()) {
					$params[] = $commandArgumentDefinition->getDashedName();
				} else {
					$params[] = $commandArgumentDefinition->getDashedName() . '[=null]';
				}
			}
			if (!empty($params)) {
				$xmlItem->addChild('params', implode(' ', $params));
			}
		}
		return $xmlRootNode->asXML();
	}

}

?>