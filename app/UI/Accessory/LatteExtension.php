<?php declare(strict_types=1);

namespace App\UI\Accessory;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\UserEntity;
use App\Model\Service\MarkdownRenderer;
use App\Model\Service\PrivateMessageService;
use App\Model\Service\ReportService;
use Latte\Extension;
use Latte\Runtime\Html;
use Nette\Security\User;

/**
 * Filters of the application: {$post->text|markdown} prints the Markdown as safe HTML (MarkdownRenderer).
 * Functions: unreadPrivateMessages() = threads with an unread message of the logged-in user (the layout of the forum), 0 for a guest;
 * openReports() = open reports of the forums the logged-in user moderates, null when he moderates none (no link to the reports).
 */
class LatteExtension extends Extension
{
    public function __construct(
        private readonly MarkdownRenderer $markdownRenderer,
        private readonly User $user,
        private readonly EntityManagerDecorator $em,
        private readonly PrivateMessageService $privateMessages,
        private readonly ReportService $reports,
    ) {
    }

    /**
     * @return array<string, callable>
     */
    public function getFunctions() : array
    {
        return [
            'unreadPrivateMessages' => fn () : int => $this->user->isLoggedIn()
                ? $this->privateMessages->unreadCount($this->em->getReference(UserEntity::class, $this->user->getId()))
                : 0,
            'openReports' => function () : ?int {
                if (!$this->user->isLoggedIn()) {
                    return null;
                }

                $user = $this->em->getReference(UserEntity::class, $this->user->getId());

                return $this->reports->isModerator($user) ? $this->reports->countFor($user, true) : null;
            },
        ];
    }

    /**
     * @return array<string, callable>
     */
    public function getFilters() : array
    {
        return [
            'markdown' => fn (?string $markdown) : Html => new Html($this->markdownRenderer->toHtml((string) $markdown)),
        ];
    }
}
