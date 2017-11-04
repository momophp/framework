<?php

declare(strict_types=1);

/*
 * This file is part of eelly package.
 *
 * (c) eelly.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eelly\Di;

use Eelly\Db\Adapter\Pdo\Mysql as Connection;
use Eelly\Queue\Adapter\AMQPFactory;
use Phalcon\Di\Injectable as DiInjectable;

/**
 * @author hehui<hehui@eelly.net>
 */
abstract class Injectable extends DiInjectable implements InjectionAwareInterface
{
    /**
     * Register db service.
     */
    public function registerDbService(): void
    {
        $di = $this->getDI();
        // mysql master connection service
        $di->setShared('dbMaster', function () {
            $config = $this->getModuleConfig()->mysql->master;

            $connection = new Connection($config->toArray());
            $connection->setEventsManager($this->get('eventsManager'));

            return $connection;
        });

        // mysql slave connection service
        $di->setShared('dbSlave', function () {
            $config = $this->getModuleConfig()->mysql->slave->toArray();
            shuffle($config);

            $connection = new Connection(current($config));
            $connection->setEventsManager($this->get('eventsManager'));

            return $connection;
        });

        // register modelsMetadata service
        $di->setShared('modelsMetadata', function () {
            $config = $this->getModuleConfig()->mysql->metaData->toArray();

            return $this->get($config['adapter'], [
                $config['options'][$config['adapter']],
            ]);
        });

        // add trace id
        if (class_exists('Eelly\SDK\EellyClient')) {
            $this->eventsManager->attach('db:afterConnect', function (Connection $connection): void {
                $connection->execute('SELECT trace_?', [
                    \Eelly\SDK\EellyClient::$traceId,
                ]);
            });
        }
    }

    /**
     * Register queue service.
     */
    public function registerQueueService(): void
    {
        $this->getDI()->set('queueFactory', function () {
            $connectionOptions = $this->getModuleConfig()->amqp->toArray();

            return new AMQPFactory($connectionOptions);
        });
    }
}
