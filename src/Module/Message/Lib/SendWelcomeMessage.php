<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

use Override;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;

final class SendWelcomeMessage implements SendWelcomeMessageInterface
{
    public function __construct(
        private FactionRepositoryInterface $factionRepository,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[Override]
    public function sendWelcomeMessage(UserInterface $user): void
    {
        $faction = $this->factionRepository->find($user->getFactionId());

        $welcomeMessage = $faction?->getWelcomeMessage();
        if ($welcomeMessage === null || trim($welcomeMessage) === '') {
            $welcomeMessage = sprintf(
                'Willkommen bei Star Trek Universe!

Mit diesem Schreiben wird Ihnen offiziell bestätigt, dass Ihre Registrierung erfolgreich abgeschlossen wurde. Sie haben nun Zugang zu den Grenzregionen der bekannten Galaxis erhalten.

Ihnen wurde die Siedlerlizenz [b]Klasse Standard[/b] mit der Patentnummer [b]#%d[/b] zugeteilt. Diese berechtigt Sie zur Gründung, Verwaltung und Verteidigung von Kolonien unter Ihrem Namen.

Zur Unterstützung Ihrer ersten Schritte wurde ein umfassendes Tutorial-System in Ihre Benutzeroberfläche integriert. Die Inhalte wurden von erfahrenen Siedlern zusammengestellt und regelmäßig aktualisiert.

Beachten Sie, dass Ihre Aktivitäten in den Grenzregionen dokumentiert werden. Erfolgreiche Siedlungsprojekte werden entsprechend gewürdigt.

Bei Fragen, technischen Problemen oder Unterstützungsbedarf stehe Ich Ihnen  jederzeit zur Verfügung. Zögern Sie nicht, Kontakt aufzunehmen.

Möge Ihre Reise durch die Sterne erfolgreich verlaufen!

[i]– %s, Abteilung Siedlungsangelegenheiten[/i]',
                $user->getId(),
                $faction?->getName() ?? 'Die Großmacht'
            );
        }

        $senderId = $this->getFactionNpcId($user->getFactionId());

        $this->privateMessageSender->send(
            $senderId,
            $user->getId(),
            $welcomeMessage,
            PrivateMessageFolderTypeEnum::SPECIAL_MAIN
        );
    }

    private function getFactionNpcId(int $factionId): int
    {
        return match ($factionId) {
            1 => UserEnum::USER_NPC_FEDERATION,
            2 => UserEnum::USER_NPC_ROMULAN,
            3 => UserEnum::USER_NPC_KLINGON,
            4 => UserEnum::USER_NPC_CARDASSIAN,
            5 => UserEnum::USER_NPC_FERG,
            default => UserEnum::USER_NPC_FEDERATION,
        };
    }
}
