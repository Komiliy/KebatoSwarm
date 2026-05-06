<?php

declare(strict_types=1);

namespace Swarm\Adapters;

use Swarm\Helpers\Logger;

/**
 * DirectAdminAdapter — Manages subdomains via the DirectAdmin API.
 *
 * Status: Planned — community contributions welcome.
 * Docs: https://docs.directadmin.com/developer/api/
 */
class DirectAdminAdapter implements ControlPanelAdapter
{
    private string $hostname;
    private int $port;
    private string $username;
    private string $loginKey;
    private string $baseDomain;

    public function __construct(array $config)
    {
        $this->hostname = $config['da_hostname'] ?? '';
        $this->port = (int) ($config['da_port'] ?? 2222);
        $this->username = $config['da_username'] ?? '';
        $this->loginKey = $config['da_login_key'] ?? '';
        $this->baseDomain = \Swarm\Helpers\Url::baseDomain();
    }

    public function createSubdomain(string $slug, string $documentRoot): void
    {
        // TODO: Implement via DirectAdmin API
        // POST /CMD_API_SUBDOMAIN
        // domain=baseDomain&subdomain=slug&action=create
        throw new \RuntimeException(
            'DirectAdmin adapter is not yet implemented. ' .
            'Contributions welcome: https://github.com/NowSquare/Ricsian/issues'
        );
    }

    public function removeSubdomain(string $slug): void
    {
        throw new \RuntimeException('DirectAdmin adapter is not yet implemented.');
    }

    public function pauseSubdomain(string $slug): void
    {
        throw new \RuntimeException('DirectAdmin adapter is not yet implemented.');
    }

    public function resumeSubdomain(string $slug): void
    {
        throw new \RuntimeException('DirectAdmin adapter is not yet implemented.');
    }

    public function verify(): array
    {
        if (empty($this->hostname) || empty($this->username) || empty($this->loginKey)) {
            return ['ok' => false, 'message' => 'DirectAdmin hostname, username, and login key are required.'];
        }

        // TODO: Test connection to DirectAdmin API
        return ['ok' => false, 'message' => 'DirectAdmin adapter is not yet implemented. Contributions welcome.'];
    }
}
