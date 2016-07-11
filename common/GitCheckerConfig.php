<?php

namespace common;

class GitCheckerConfig
{
    # Available protocols
    const PROTOCOL_SSH = 'ssh';
    const PROTOCOL_HTTP = 'http';
    const PROTOCOL_HTTPS = 'https';

    # Available authentication types
    const AUTH_TYPE_LOGIN = 'login';
    const AUTH_TYPE_KEY = 'key';
    const AUTH_TYPE_OFF = null;

    # Test branch name settings
    const TEST_BRANCH_PREFIX = 'test-';
    const TEST_BRANCH_DATE_FORMAT = 'Ymd';
    const TEST_BRANCH_CREATER_NAME = 'gitchecker';
    const TEST_BRANCH_NAME_DATE_FORMAT = 'His';

    # Test file name
    const TEST_FILE_NAME = '.gitchecker';

    # Test commit settings
    const TEST_COMMIT_PREFIX = '[GitChecker]';
    const TEST_COMMIT_DATE_FORMAT = 'Y-m-d H:i:s';
    const TEST_COMMIT_MESSAGE = ' Test commit';

    # Test remote name
    const TEST_REMOTE = 'origin';

    /**
     * @var string $path
     */
    private $path;

    /**
     * @var array $repositories
     */
    private $repositories = [];

    /**
     * GitCheckerConfig constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
       $this->parseConfig($config);
    }

    /**
     * @param array $config
     * 
     * @throws \Exception
     */
    private function parseConfig(array $config)
    {
        $path = $config['path'] ?? null;
        $connectionConf = $config['connection'] ?? null;
        $repositoriesConf = $config['repository'] ?? null;

        if (!empty($path) && is_writable($path)) {
            $this->path = $path;
        } else {
            throw new \Exception("Specified path '{$path}' is not writable!");
        }

        $protocol = $connectionConf['protocol'] ?? null;
        $host = $connectionConf['host'] ?? null;
        $port = $connectionConf['port'] ?? null;
        $authType = $connectionConf['auth'] ?? null;
        $user = $connectionConf['user'] ?? null;
        $pass = $connectionConf['pass'] ?? null;
        $url = $connectionConf['url'] ?? null;

        foreach ($repositoriesConf as $repository) {
            $subPath = $url . '/' . $repository . '.git';
            $this->repositories[$repository] = $this->generateRepositoryUrl(
                $protocol,
                $host,
                $port,
                $authType,
                $user,
                $pass,
                $subPath
            );
        }
    }

    /**
     * @param string $protocol
     * @param string $host
     * @param string $port
     * @param string $authType
     * @param string $user
     * @param string $pass
     * @param string $subPath
     *
     * @return string
     * @throws \Exception
     */
    private function generateRepositoryUrl(
        string $protocol,
        string $host,
        string $port,
        string $authType,
        string $user,
        string $pass,
        string $subPath
    ) : string
    {
        $authType = $authType == GitCheckerConfig::AUTH_TYPE_KEY
        || $authType == GitCheckerConfig::AUTH_TYPE_LOGIN ? $authType : GitCheckerConfig::AUTH_TYPE_OFF;

        if (empty($protocol) || empty($host) || empty($port) || empty($authType)) {
            throw new \Exception('Required parameters is empty!');
        }

        switch ($protocol) {
            case GitCheckerConfig::PROTOCOL_SSH:
                if ($authType == GitCheckerConfig::AUTH_TYPE_LOGIN) {
                    $authParams = !empty($user) ? $user . (!empty($pass) ? ':' . $pass : null) . '@' : null;
                } elseif ($authType == GitCheckerConfig::AUTH_TYPE_KEY) {
                    $authParams = !empty($user) ? $user . '@' : null;
                } else {
                    $authParams = null;
                }

                $repositoryUrl = $protocol . '://' . $authParams . $host . ':' . $port . $subPath;
                break;
            case GitCheckerConfig::PROTOCOL_HTTP:
            case GitCheckerConfig::PROTOCOL_HTTPS:
                if ($authType == GitCheckerConfig::AUTH_TYPE_LOGIN) {
                    $authParams = !empty($user) ? $user . (!empty($pass) ? ':' . $pass : null) . '@' : null;
                } else {
                    $authParams = null;
                }

                $repositoryUrl = $protocol . '://' . $authParams . $host . ':' . $port . $subPath;
                break;
            default:
                throw new \Exception("Unsupported protocol '{$protocol}'!");
                break;
        }

        return $repositoryUrl;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getRepositories()
    {
        return $this->repositories;
    }
}
