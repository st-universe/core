<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\AddContact;

use Mockery\MockInterface;
use Stu\ActionControllerTestCase;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Message\View\ShowContactList\ShowContactList;
use Stu\Module\Message\View\ShowContactMode\ShowContactMode;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

class AddContactTest extends ActionControllerTestCase
{
    private MockInterface&AddContactRequestInterface $addContactRequest;
    private MockInterface&ContactRepositoryInterface $contactRepository;
    private MockInterface&PrivateMessageSenderInterface $privateMessageSender;
    private MockInterface&UserRepositoryInterface $userRepository;

    private AddContact $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->addContactRequest = $this->mock(AddContactRequestInterface::class);
        $this->contactRepository = $this->mock(ContactRepositoryInterface::class);
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);
        $this->userRepository = $this->mock(UserRepositoryInterface::class);

        $this->subject = new AddContact(
            $this->addContactRequest,
            $this->contactRepository,
            $this->privateMessageSender,
            $this->userRepository
        );
    }

    public function testHandleReturnsToContactListWhenAddedFromContactList(): void
    {
        $user = $this->mock(User::class);
        $info = $this->mock(InformationWrapper::class);

        $this->addContactRequest->shouldReceive('getContactDiv')
            ->withNoArgs()
            ->once()
            ->andReturn('');
        $this->addContactRequest->shouldReceive('getRecipientId')
            ->withNoArgs()
            ->once()
            ->andReturn('invalid');

        $this->game->shouldReceive('setView')
            ->with(ShowContactList::VIEW_IDENTIFIER)
            ->once();
        $this->game->shouldReceive('setTemplateVar')
            ->never();
        $this->game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $this->game->shouldReceive('getInfo')
            ->withNoArgs()
            ->once()
            ->andReturn($info);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $info->shouldReceive('addInformation')
            ->with('Ungültiger Wert angegeben. Muss positive Zahl sein!')
            ->once();

        $this->subject->handle($this->game);
    }

    public function testHandleKeepsContactModeResponseForAjaxRequest(): void
    {
        $user = $this->mock(User::class);
        $info = $this->mock(InformationWrapper::class);

        $this->addContactRequest->shouldReceive('getContactDiv')
            ->withNoArgs()
            ->once()
            ->andReturn('contactbutton');
        $this->addContactRequest->shouldReceive('getRecipientId')
            ->withNoArgs()
            ->once()
            ->andReturn('invalid');

        $this->game->shouldReceive('setView')
            ->with(ShowContactMode::VIEW_IDENTIFIER)
            ->once();
        $this->game->shouldReceive('setTemplateVar')
            ->with('div', 'contactbutton')
            ->once();
        $this->game->shouldReceive('setTemplateVar')
            ->with('contact', null)
            ->once();
        $this->game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $this->game->shouldReceive('getInfo')
            ->withNoArgs()
            ->once()
            ->andReturn($info);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $info->shouldReceive('addInformation')
            ->with('Ungültiger Wert angegeben. Muss positive Zahl sein!')
            ->once();

        $this->subject->handle($this->game);
    }
}
