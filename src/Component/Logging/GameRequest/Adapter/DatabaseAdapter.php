<?php

declare(strict_types=1);

namespace Stu\Component\Logging\GameRequest\Adapter;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Monolog\Level;
use request;
use Stu\Orm\Entity\GameRequest;

/**
 * Adapter for saving game requests in the database
 *
 * @deprecated Use LogfileAdapter
 */
final class DatabaseAdapter extends AbstractAdapter
{
    public function __construct(private Connection $database)
    {
    }

    #[\Override]
    protected function log(
        GameRequest $gameRequest,
        Level $logLevel,
        bool $isRequestCheck
    ): void {
        if ($isRequestCheck && !request::isRequest()) {
            return;
        }

        $params = $gameRequest->getParameterArray();
        $params['request_id'] = $gameRequest->getRequestId();
        $params['log_type'] = $logLevel;

        /**
         * We do not perform transaction handling in here
         * If an error occurs, the app should end the transaction before saving the error game state
         */
        $this->database->insert(
            GameRequest::TABLE_NAME,
            [
                'turn_id' => $gameRequest->getTurnId(),
                'time' => $gameRequest->getTime(),
                'module' => $gameRequest->getModule(),
                'action' => $gameRequest->getAction(),
                'action_ms' => $gameRequest->getActionMs(),
                'view' => $gameRequest->getView(),
                'view_ms' => $gameRequest->getViewMs(),
                'user_id' => $gameRequest->getUserId(),
                'render_ms' => $gameRequest->getRenderMs(),
                'params' => json_encode($params, JSON_PRETTY_PRINT),
            ],
            [
                'turn_id' => ParameterType::INTEGER,
                'time' => ParameterType::INTEGER,
                'module' => ParameterType::STRING,
                'action' => ParameterType::STRING,
                'action_ms' => ParameterType::INTEGER,
                'view' => ParameterType::STRING,
                'view_ms' => ParameterType::INTEGER,
                'user_id' => ParameterType::INTEGER,
                'render_ms' => ParameterType::INTEGER,
                'params' => ParameterType::STRING,
            ]
        );
    }
}
