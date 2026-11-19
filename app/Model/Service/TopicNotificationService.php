<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicWatchEntity;
use App\Settings\SiteUrl;
use Nette\Application\LinkGenerator;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\Localization\Translator;

/**
 * A new reply: every watcher of the topic (but its author) who was not notified since his last visit gets ONE queued mail
 * (each his own, the old code put all recipients into one To: and mailed user ids instead of addresses).
 */
class TopicNotificationService
{
    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly MailQueueService $mailQueue,
        private readonly LatteFactory $latteFactory,
        private readonly LinkGenerator $linkGenerator,
        private readonly Translator $translator,
        private readonly TopicPaginationService $topicPagination,
        private readonly SiteUrl $siteUrl,
    ) {
    }

    public function notifyWatchers(PostEntity $reply) : int
    {
        /** @var list<TopicWatchEntity> $watches */
        $watches = $this->em->getRepository(TopicWatchEntity::class)
            ->createQueryBuilder('_w')
            ->select('_w', '_u')
            ->innerJoin('_w.user', '_u')
            ->where('_w.topic = :topic')
            ->andWhere('_w.isNotified = false')
            ->andWhere('_w.user != :author')
            ->andWhere('_u.isActive = true')
            ->setParameter('topic', $reply->topic)
            ->setParameter('author', $reply->user)
            ->getQuery()
            ->getResult();

        if ($watches === []) {
            return 0;
        }

        // against the URL of the web, not of the current request (there is none in the console / cron)
        $url = $this->linkGenerator->withReferenceUrl((string) $this->siteUrl->get())->link('Forum:Topic:View:default', [
            'uuid' => $reply->topic->uuid->toString(),
            'page' => $this->topicPagination->pageOf($reply),
        ]) . '#post-' . $reply->id;
        $subject = (string) $this->translator->translate('forum-topic-mail.newReply.subject', ['topic' => $reply->topic->name]);
        $body = $this->latteFactory->create()->renderToString(__DIR__ . '/../../UI/Forum/Topic/Mailing/newReply.latte', [
            'topic' => $reply->topic,
            'reply' => $reply,
            'url' => $url,
        ]);

        foreach ($watches as $watch) {
            $this->mailQueue->enqueue($watch->user->email, $subject, $body);
            $watch->isNotified = true;
        }

        $this->em->flush();

        return count($watches);
    }
}
