<?php

use Doctrine\ORM\EntityManagerInterface;
use Noodlehaus\ConfigInterface;

use Stu\Component\Game\GameEnum;
use Stu\Module\Tick\Ship\ShipTickManagerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;

require_once __DIR__ . '/../../Config/Bootstrap.php';

$db = $container->get(EntityManagerInterface::class);

$db->beginTransaction();

try {
    $container->get(ShipTickManagerInterface::class)->work();
} catch (Throwable $t) {
    $admins = $container->get(ConfigInterface::class)->get('game.admins');

    $privateMessageSender = $container->get(PrivateMessageSenderInterface::class);

    foreach ($admins as $adminId) {
        $privateMessageSender->send(
            GameEnum::USER_NOONE,
            $adminId,
            _('[b][color=FF2626]Der Schiffstick-CRON ist fehlgeschlagen, bitte manuell den Grund prüfen![/color][/b]') + "\n"
                + _('Fehlermeldung:') + "\n"
                + $t->getMessage()
        );
    }

    throw $t;
}

$db->commit();
