<?php

/**
 * Panda model
 * 
 * This is the class which manage the model part of the MVC pattern.
 * It contains the tools to communicate with one or more datasource(s).
 * 
 * @package Panda
 * @author Stanislas Michalak <stanislas.michalak@gmail.com>
 */

namespace Panda\component\datasource;

use Panda\component\AbstractComponent;
use Panda\Application;
use Doctrine\Common\EventManager;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;

class AbstractModel extends AbstractComponent
{

	static protected $entityManagers = array();
	protected $eventManager;

	/**
	 * Gets the default or $datasourceName entities manager
	 * 
	 * @param string|null $datasourceName
	 * @return Doctrine\ORM\EntityManager
	 * @throws InvalidArgumentException
	 */
	public function getEntityManager($datasourceName = null)
	{
		if ($datasourceName !== null) {
			if (!is_string($datasourceName) || empty($datasourceName)) {
				throw new \InvalidArgumentException('Invalid datasource name "'.((string) $datasourceName).'"');
			}
		} else {
			$datasourceName = $this->app->getComponent('config\Config')->get('datasources.default');
		}

		if (!isset(self::$entityManagers[$datasourceName])) {
			$bundleName = $this->app->getRoute()->getBundleName();
			$cacheName = $this->app->getComponent('config\Config')->get('datasources.list.' . $datasourceName . '.cache');
			$cacheClass = '\Doctrine\Common\Cache\\' . (!empty($cacheName) ? $cacheName : 'ArrayCache');
			
			$entityManagerConfig = Setup::createConfiguration($this->app->getComponent('config\Config')->get('panda.mode') === 'dev');
			
			//Load annotations stuff
			AnnotationRegistry::registerFile(VENDOR_DIR . 'doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php');
			$cache = new $cacheClass();
			$annotationReader = new AnnotationReader();
			$cachedAnnotationReader = new CachedReader(
			    $annotationReader,
			    $cache
			);

			//Load event manager
			$this->eventManager = new EventManager();
			$listenersList = $this->app->getComponent('config\Config')->get('datasources.listeners');
			if (!empty($listenersList)) {
				foreach ($listenersList as $listener) {
					$l = new $listener();
					$this->eventManager->addEventSubscriber($l);
				}
			}

			//Tables prefix
			if ($this->app->getComponent('config\Config')->exists('datasources.list.' . $datasourceName . '.prefix')) {
				$tablePrefix = new DoctrineTablePrefixListener($this->app->getComponent('config\Config')->get('datasources.list.' . $datasourceName . '.prefix'));
				$this->eventManager->addEventListener(\Doctrine\ORM\Events::loadClassMetadata, $tablePrefix);
			}

			//Load doctrine extensions listeners if any
			if (!empty($listenersList)) {
				$driverChain = new \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain();
				\Gedmo\DoctrineExtensions::registerAbstractMappingIntoDriverChainORM(
				    $driverChain,
				    $cachedAnnotationReader
				);
			}
			
			$driver = new AnnotationDriver(
				$cachedAnnotationReader,
				array(
					APP_DIR . $bundleName . '/entity'
				)
			);

			$entityManagerConfig->setMetadataDriverImpl($driver);

			self::$entityManagers[$datasourceName] = EntityManager::create(
				$this->app->getComponent('config\Config')->get('datasources.list.' . $datasourceName),
				$entityManagerConfig,
				$this->eventManager
			);
		}

		return self::$entityManagers[$datasourceName];
	}

	public function newEntity($entityName)
	{
		return new $entityName();
	}
}
