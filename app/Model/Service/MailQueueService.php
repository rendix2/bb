<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\MailEntity;
use App\Settings\Email;
use DateTimeImmutable;
use Nette\Mail\Mailer;
use Nette\Mail\Message;

/**
 * The mail queue: the table mail holds the queued mails (sent_at null) and the history of the sent ones. Mails are sent by
 * the console command mail:send-queue (cron), not in the request that queued them.
 */
class MailQueueService
{
    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly Mailer $mailer,
        private readonly Email $from,
    ) {
    }

    public function enqueue(string $to, string $subject, string $htmlBody) : MailEntity
    {
        $mail = new MailEntity();
        $mail->emailTo = $to;
        $mail->subject = $subject;
        $mail->body = $htmlBody;

        $this->em->persist($mail);

        return $mail;
    }

    /**
     * Sends at most $limit queued mails, the oldest first; returns how many were sent.
     */
    public function sendQueued(int $limit) : int
    {
        /** @var list<MailEntity> $mails */
        $mails = $this->em->getRepository(MailEntity::class)
            ->createQueryBuilder('_m')
            ->where('_m.sentAt IS NULL')
            ->orderBy('_m.id', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        foreach ($mails as $mail) {
            $message = new Message();
            $message->setFrom((string) $this->from->get());
            $message->addTo($mail->emailTo);
            $message->setSubject($mail->subject);
            $message->setHtmlBody($mail->body);

            $this->mailer->send($message);

            // marked one by one: a failure later does not send the earlier mails again
            $mail->sentAt = new DateTimeImmutable();
            $this->em->flush();
        }

        return count($mails);
    }
}
