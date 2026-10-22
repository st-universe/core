<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action;

use Doctrine\Common\Collections\ArrayCollection;
use Dom\HTMLDocument;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Communication\Action\AddKnPlotMember\AddKnPlotMember;
use Stu\Module\Communication\Action\AddKnPlotMember\AddKnPlotMemberRequestInterface;
use Stu\Module\Communication\Action\CreateKnPlot\CreateKnPlot;
use Stu\Module\Communication\Action\CreateKnPlot\CreateKnPlotRequestInterface;
use Stu\Module\Communication\Action\DeleteKnComment\DeleteKnComment;
use Stu\Module\Communication\Action\DeleteKnComment\DeleteKnCommentRequestInterface;
use Stu\Module\Communication\Action\PostKnComment\PostKnComment;
use Stu\Module\Communication\Action\PostKnComment\PostKnCommentRequestInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\KnComment;
use Stu\Orm\Entity\KnPost;
use Stu\Orm\Entity\RpgPlot;
use Stu\Orm\Entity\RpgPlotMember;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\KnCommentRepositoryInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\ProtectedMutationTestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

final class CommunicationMutationSecurityTest extends ProtectedMutationTestCase
{
    #[DataProvider('actions')]
    public function testEveryEntryPointRejectsForgedRequests(string $class): void
    {
        $arguments = [];
        foreach (new ReflectionClass($class)->getConstructor()->getParameters() as $parameter) {
            $arguments[] = $this->mock($parameter->getType()->getName());
        }
        $this->assertRequestProtection(new $class(...$arguments));
    }

    public static function actions(): iterable
    {
        foreach ([CreateKnPlot::class, PostKnComment::class, DeleteKnComment::class, AddKnPlotMember::class] as $class) {
            yield [$class];
        }
    }

    public function testCreatingPlotStillSavesOwnerAndMembership(): void
    {
        $request = $this->mock(CreateKnPlotRequestInterface::class);
        $request->shouldReceive('getTitle')->once()->andReturn('Neuer Testplot');
        $request->shouldReceive('getText')->once()->andReturn('Beschreibung');
        $plots = $this->mock(RpgPlotRepositoryInterface::class);
        $members = $this->mock(RpgPlotMemberRepositoryInterface::class);
        $plot = new RpgPlot();
        $member = new RpgPlotMember();
        $plots->shouldReceive('prototype')->once()->andReturn($plot);
        $plots->shouldReceive('save')->with($plot)->once();
        $members->shouldReceive('prototype')->once()->andReturn($member);
        $members->shouldReceive('save')->with($member)->once();
        $user = $this->user();
        new CreateKnPlot($request, $members, $plots)->handle($this->context($user));
        self::assertSame('Neuer Testplot', $plot->getTitle());
        self::assertSame($user, $plot->getUser());
        self::assertSame($user, $member->getUser());
        self::assertTrue($plot->getMembers()->contains($member));
    }

    public function testPostingCommentStillSavesTextAndAuthor(): void
    {
        $request = $this->mock(PostKnCommentRequestInterface::class);
        $request->shouldReceive('getKnId')->once()->andReturn(7);
        $request->shouldReceive('getText')->once()->andReturn('Neuer Kommentar');
        $comments = $this->mock(KnCommentRepositoryInterface::class);
        $posts = $this->mock(KnPostRepositoryInterface::class);
        $sender = $this->mock(PrivateMessageSenderInterface::class);
        $user = $this->user();
        $post = $this->mock(KnPost::class);
        $post->shouldReceive('getUser')->once()->andReturn($user);
        $post->shouldReceive('getComments')->once()->andReturn(new ArrayCollection());
        $posts->shouldReceive('find')->with(7)->once()->andReturn($post);
        $comment = new KnComment();
        $comments->shouldReceive('prototype')->once()->andReturn($comment);
        $comments->shouldReceive('save')->with($comment)->once();
        new PostKnComment($request, $comments, $posts, $sender)->handle($this->context($user));
        self::assertSame('Neuer Kommentar', $comment->getText());
        self::assertSame($user, $comment->getUser());
    }

    #[DataProvider('ownership')]
    public function testDeletingCommentsPreservesOwnership(bool $owned): void
    {
        $request = $this->mock(DeleteKnCommentRequestInterface::class);
        $request->shouldReceive('getCommentId')->once()->andReturn(7);
        $comments = $this->mock(KnCommentRepositoryInterface::class);
        $comment = $this->mock(KnComment::class);
        $comment->shouldReceive('getUserId')->once()->andReturn($owned ? 101 : 102);
        $comments->shouldReceive('find')->with(7)->once()->andReturn($comment);
        if ($owned) {
            $comment->shouldReceive('setDeleted')->withArgs(fn (int $timestamp): bool => $timestamp > 0)->once();
            $comments->shouldReceive('save')->with($comment)->once();
        }
        new DeleteKnComment($request, $comments)->handle($this->context($this->user()));
    }

    #[DataProvider('plotAccess')]
    public function testAddingPlotMembersPreservesOwnershipAndActiveStatus(bool $owned, bool $active): void
    {
        $request = $this->mock(AddKnPlotMemberRequestInterface::class);
        $request->shouldReceive('getPlotId')->once()->andReturn(7);
        $plots = $this->mock(RpgPlotRepositoryInterface::class);
        $members = $this->mock(RpgPlotMemberRepositoryInterface::class);
        $users = $this->mock(UserRepositoryInterface::class);
        $sender = $this->mock(PrivateMessageSenderInterface::class);
        $plot = $this->mock(RpgPlot::class);
        $plot->shouldReceive('getUserId')->andReturn($owned ? 101 : 102);
        if ($owned) {
            $plot->shouldReceive('isActive')->once()->andReturn($active);
        }
        $plots->shouldReceive('find')->with(7)->once()->andReturn($plot);
        if ($owned && $active) {
            $request->shouldReceive('getRecipientId')->once()->andReturn(102);
            $recipient = $this->mock(User::class);
            $recipient->shouldReceive('getId')->andReturn(102);
            $users->shouldReceive('find')->with(102)->once()->andReturn($recipient);
            $plot->shouldReceive('getId')->once()->andReturn(7);
            $plot->shouldReceive('getTitle')->once()->andReturn('Testplot');
            $members->shouldReceive('getByPlotAndUser')->with(7, 102)->once()->andReturn(null);
            $member = new RpgPlotMember();
            $members->shouldReceive('prototype')->once()->andReturn($member);
            $members->shouldReceive('save')->with($member)->once();
            $sender->shouldReceive('send')->withArgs(fn (int $from, int $to, string $text): bool => $from === 101 && $to === 102 && str_contains($text, 'Testplot'))->once();
        }
        new AddKnPlotMember($request, $members, $plots, $sender, $users)->handle($this->context($this->user()));
    }

    public function testCreationAndRefreshedCommentFormsSupplyTokens(): void
    {
        $twig = new Environment(new FilesystemLoader(__DIR__ . '/../../../../../src'), ['autoescape' => 'html']);
        $twig->addFilter(new TwigFilter('bbcode', fn (string $value): string => $value));
        $html = $twig->render('html/communication/plot/createPlot.twig', ['SESSIONSTRING' => 'plot-token']);
        $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);
        self::assertSame('post', $document->querySelector('form')->getAttribute('method'));
        self::assertSame('plot-token', $document->querySelector('[name="sstr"]')->getAttribute('value'));
        $post = $this->mock(KnPost::class);
        $post->shouldReceive('getId')->andReturn(7);
        foreach (['first-token', 'renewed-token'] as $token) {
            $html = $twig->render('html/communication/knComments.twig', ['SESSIONSTRING' => $token, 'POST' => $post, 'COMMENTS' => [], 'CHARLIMIT' => 250, 'WRAP_KN_COMMENTS' => false]);
            $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);
            self::assertSame($token, $document->querySelector('#kn_comment_session_string')->getAttribute('value'));
        }
    }

    public static function ownership(): iterable
    {
        yield 'own' => [true];
        yield 'foreign' => [false];
    }

    public static function plotAccess(): iterable
    {
        yield 'active owner' => [true, true];
        yield 'foreign owner' => [false, true];
        yield 'inactive plot' => [true, false];
    }

    private function user(): User
    {
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn(101);
        return $user;
    }

    private function context(User $user): ActionControllerContext
    {
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('getUser')->andReturn($user);
        $context->shouldReceive('setView')->zeroOrMoreTimes();
        $information = $this->mock(InformationWrapper::class);
        $information->shouldReceive('addInformation')->zeroOrMoreTimes();
        $context->shouldReceive('getInfo')->andReturn($information);
        return $context;
    }
}
