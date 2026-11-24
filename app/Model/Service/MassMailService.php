<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\MailEntity;
use App\Model\Entity\MassMailEntity;
use App\Model\Entity\UserEntity;
use Nette\Utils\Strings;

/**
 * Mass e-mails of the administration (slice 7e4): the text in Markdown goes to all active users through the mail queue
 * (MailQueueService, sent by the cron), the sending is kept in the history (mass_mail).
 */
class MassMailService
{
    public const string ERROR_SUBJECT = 'admin-mail.error.subject';
    public const string ERROR_BODY = 'admin-mail.error.body';
    public const string ERROR_NO_RECIPIENTS = 'admin-mail.error.noRecipients';

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly MailQueueService $mailQueue,
        private readonly MarkdownRenderer $markdownRenderer,
    ) {
    }

    /**
     * @return list<string> the e-mails of the active users
     */
    public function recipients() : array
    {
        /** @var list<string> $emails */
        $emails = $this->em->getRepository(UserEntity::class)
            ->createQueryBuilder('_u')
            ->select('_u.email')
            ->where('_u.isActive = true')
            ->orderBy('_u.id', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();

        return $emails;
    }

    public function send(UserEntity $sentBy, string $subject, string $body) : MassMailEntity
    {
        $subject = Strings::trim($subject);
        $body = Strings::trim($body);

        if ($subject === '' || Strings::length($subject) > 1024) {
            throw new AdminException(self::ERROR_SUBJECT);
        }

        if ($body === '') {
            throw new AdminException(self::ERROR_BODY);
        }

        $recipients = $this->recipients();

        if ($recipients === []) {
            throw new AdminException(self::ERROR_NO_RECIPIENTS);
        }

        $html = $this->markdownRenderer->toHtml($body);

        foreach ($recipients as $email) {
            $this->mailQueue->enqueue($email, $subject, $html);
        }

        $massMail = new MassMailEntity();
        $massMail->subject = $subject;
        $massMail->body = $body;
        $massMail->sentBy = $sentBy;
        $massMail->recipientCount = count($recipients);

        $this->em->persist($massMail);
        $this->em->flush();

        return $massMail;
    }

    /**
     * @return list<MassMailEntity> the newest first
     */
    public function history(int $limit) : array
    {
        return $this->em->getRepository(MassMailEntity::class)->findBy([], ['id' => 'DESC'], $limit);
    }

    /**
     * @return list<MailEntity> the queued mails (not sent yet), the oldest first
     */
    public function queue(int $limit) : array
    {
        return $this->em->getRepository(MailEntity::class)->findBy(['sentAt' => null], ['id' => 'ASC'], $limit);
    }

    public function queueCount() : int
    {
        return $this->em->getRepository(MailEntity::class)->count(['sentAt' => null]);
    }
}
