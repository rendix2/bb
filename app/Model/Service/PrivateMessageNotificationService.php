<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\PrivateMessageEntity;
use App\Model\Entity\PrivateMessageParticipantEntity;
use App\Settings\SiteUrl;
use Contributte\Translation\Translator;
use Nette\Application\LinkGenerator;
use Nette\Bridges\ApplicationLatte\LatteFactory;

/**
 * A new private message: every other active participant (not left) who wants mails (his choice in the thread) and was not
 * notified since his last visit of the thread gets ONE queued mail, in HIS language (users.language -> language.locale). The mail
 * names the author and the subject, not the text.
 */
class PrivateMessageNotificationService
{
    private const string TEMPLATE = __DIR__ . '/../../UI/Forum/Pm/Mailing/newMessage.';

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly MailQueueService $mailQueue,
        private readonly LatteFactory $latteFactory,
        private readonly LinkGenerator $linkGenerator,
        private readonly Translator $translator,
        private readonly SiteUrl $siteUrl,
    ) {
    }

    public function notify(PrivateMessageEntity $message) : int
    {
        /** @var list<PrivateMessageParticipantEntity> $participants */
        $participants = $this->em->getRepository(PrivateMessageParticipantEntity::class)
            ->createQueryBuilder('_p')
            ->select('_p', '_u', '_l')
            ->innerJoin('_p.user', '_u')
            ->innerJoin('_u.language', '_l')
            ->where('_p.thread = :thread')
            ->andWhere('_p.user != :author')
            ->andWhere('_p.isDeleted = false')
            ->andWhere('_p.wantsMail = true')
            ->andWhere('_p.isNotified = false')
            ->andWhere('_u.isActive = true')
            ->setParameter('thread', $message->thread)
            ->setParameter('author', $message->user)
            ->getQuery()
            ->getResult();

        if ($participants === []) {
            return 0;
        }

        // against the URL of the web, not of the current request (there is none in the console / cron)
        $url = $this->linkGenerator->withReferenceUrl((string) $this->siteUrl->get())->link('Forum:Pm:Thread:default', [
            'uuid' => $message->thread->uuid->toString(),
        ]) . '#message-' . $message->id;
        /** @var array<string, array{string, string}> $mails locale => [subject, body], rendered once per language */
        $mails = [];

        foreach ($participants as $participant) {
            $locale = $this->localeOf($participant);
            $mails[$locale] ??= [
                $this->translator->translate('forum-pm-mail.newMessage.subject', ['subject' => $message->thread->subject], null, $locale),
                $this->latteFactory->create()->renderToString(self::TEMPLATE . $locale . '.latte', [
                    'message' => $message,
                    'url' => $url,
                ]),
            ];
            [$subject, $body] = $mails[$locale];

            $this->mailQueue->enqueue($participant->user->email, $subject, $body);
            $participant->isNotified = true;
        }

        $this->em->flush();

        return count($participants);
    }

    /**
     * The locale of the recipient; a language without the mail template gets the default.
     */
    private function localeOf(PrivateMessageParticipantEntity $participant) : string
    {
        $locale = $participant->user->language->locale;

        return is_file(self::TEMPLATE . $locale . '.latte') ? $locale : $this->translator->getDefaultLocale();
    }
}
