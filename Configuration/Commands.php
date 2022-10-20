<?php

return [
    'rkw_registration:cleanup' => [
        'class' => \RKW\RkwRegistration\Command\CleanupCommand::class,
        'schedulable' => true,
    ],
    'rkw_registration:anonymize' => [
        'class' => \RKW\RkwRegistration\Command\AnonymizeCommand::class,
        'schedulable' => true,
    ],
    'rkw_registration:encryptionKey' => [
        'class' => \RKW\RkwRegistration\Command\EncryptionKeyCommand::class,
        'schedulable' => false,
    ],
];
