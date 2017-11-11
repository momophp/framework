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

namespace Eelly\Process;

use Eelly\Http\Server;

/**
 * 服务器健康状态进程.
 */
class ServerHealth extends Process
{
    /**
     * @var Server
     */
    private $server;

    public function __construct(Server $server = null, bool $redirectStdinStdout = false, int $createPipe = 2)
    {
        parent::__construct([$this, 'processhandler'], $redirectStdinStdout, $createPipe);
        $this->server = $server;
    }

    public function getServer()
    {
        return $this->server;
    }

    public function processhandler(self $serverMonitor): void
    {
        $this->server->setProcessName('health');
        $this->server->tick(1000, function (): void {
            // TODO 异常监控
        });
    }
}