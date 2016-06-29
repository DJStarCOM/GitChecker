### Config structure:
```php
$config = [
    'path' => '/tmp', # path to the folder for git cloning
    'connection' => [
        /**
        * Protocol:
        * ssh (auth by: key/login + password/no auth)
        * http/https (auth by: login + password or only login) 
        */
        'protocol' => 'ssh',
        'host' => 'localhost', # git remote repository host
        'port' => '26', # port of git remote repository
        'auth' => 'key', # type of authenticate: key or login
        'user' => 'git', # user for remote authenticate
        'pass' => '', # password for remote authenticate
        'url' => '/', # remote sub path if exist
    ],
    'repository' => [
        'some_repo', # name of repository
    ]
];
```
An example of the final links generated for git cloning:
`ssh://git@localhost:26/some_repo.git` _It generated from the above data._
