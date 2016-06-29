<?php

namespace common;

use Git;

class GitChecker
{
    /**
     * @var string $localPath
     */
    private $localPath;

    /**
     * @var array $repositories
     */
    private $repositories = [];

    /**
     * @var Git $git
     */
    private $git;

    /**
     * @var \GitRepo $currentRepository
     */
    private $currentRepository;

    /**
     * @var string $currentRepositoryPath
     */
    private $currentRepositoryPath;

    /**
     * GitChecker constructor.
     * @param GitCheckerConfig $config
     */
    public function __construct(GitCheckerConfig $config)
    {
        $this->localPath = $config->getPath();
        $this->repositories = $config->getRepositories();
    }

    public function runCommand()
    {
        foreach ($this->repositories as $repositoryName => $repository) {
            try {
                echo 'Repository: ' . $repositoryName . " [{$repository}]" . PHP_EOL;

                $this->currentRepositoryPath = $this->localPath . '/' . $repositoryName;
                $this->currentRepository = $this->checkCloneRepo($repository);
                $branches = $this->currentRepository->getRemoteBranchesCount();

                echo 'Branches: ' . $branches . PHP_EOL;

                if (count($branches) > 0) {
                    $testBranch = $this->checkPushRepo($repositoryName);
                    $this->checkPushedRepo($testBranch);
                }
            } catch (\Exception $e) {
                echo $e->getMessage() . PHP_EOL;
                continue;
            }
        }
    }

    /**
     * @param string $repository
     *
     * @return bool
     */
    private function checkCloneRepo($repository) : bool
    {
        return $this->git = Git::create($this->currentRepositoryPath, $repository);
    }

    /**
     * @param string $repositoryName
     *
     * @return string
     */
    private function checkPushRepo($repositoryName) : string
    {
        $testBranchName = GitCheckerConfig::TEST_BRANCH_PREFIX 
            . date(GitCheckerConfig::TEST_BRANCH_DATE_FORMAT) 
            . '-' . GitCheckerConfig::TEST_BRANCH_CREATER_NAME . '-'
            . date(GitCheckerConfig::TEST_BRANCH_NAME_DATE_FORMAT) . '-' . $repositoryName;

        $currentDateTime = date(GitCheckerConfig::TEST_COMMIT_DATE_FORMAT);
        $testFile = $this->currentRepositoryPath . '/' . GitCheckerConfig::TEST_FILE_NAME;

        echo $this->currentRepository->create_branch($testBranchName);
        echo $this->currentRepository->checkout($testBranchName);
        file_put_contents($testFile, $currentDateTime, LOCK_EX);
        echo $this->currentRepository->add($testFile);
        echo $this->currentRepository->commit(
            GitCheckerConfig::TEST_COMMIT_PREFIX . "[{$currentDateTime}]" . GitCheckerConfig::TEST_COMMIT_MESSAGE
        );
        echo $this->currentRepository->push(GitCheckerConfig::TEST_REMOTE, $testBranchName);
        return $testBranchName;
    }

    /**
     * @param string $branch
     */
    private function checkPushedRepo($branch)
    {
        $gitRepository = $this->git->open($this->currentRepositoryPath);
        echo $gitRepository->clone_from($branch);
        echo $gitRepository->status();
    }
}
