<?php

namespace App\Controller;

use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Chat\ChatStreamListener;
use Symfony\AI\Chat\ManagedStoreInterface;
use Symfony\AI\Chat\MessageStoreInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Result\Stream\Delta\TextDelta;
use Symfony\AI\Platform\Result\StreamResult;
use Symfony\AI\Platform\TokenUsage\TokenUsageInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\EventStreamResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ServerEvent;
use Symfony\Component\Routing\Attribute\Route;

final class ChatController extends AbstractController
{
    #[Route('/', name: 'app_chat_index', methods: ['GET'])]
    public function index(
        #[Autowire(service: 'ai.message_store.session.default')] MessageStoreInterface&ManagedStoreInterface $store,
    ): Response {
        $store->drop();

        return $this->render('chat.html.twig');
    }

    #[Route('/chat/stream', name: 'app_chat_stream', methods: ['POST'])]
    public function send(
        Request $request,
        #[Autowire(env: 'OLLAMA_CHAT_MODEL')] string $chatModel,
        #[Autowire(service: 'ai.agent.default')] AgentInterface $agent,
        #[Autowire(service: 'ai.message_store.session.default')] MessageStoreInterface $store,
    ): EventStreamResponse {
        $userMessage = $request->getPayload()->getString('message');

        return new EventStreamResponse(static function () use ($agent, $store, $userMessage, $chatModel): iterable {
            set_time_limit(0);

            $messages = $store->load();
            $messages->add(Message::ofUser($userMessage));

            $result = $agent->call($messages, ['stream' => true]);
            \assert($result instanceof StreamResult);
            $result->addListener(new ChatStreamListener($messages, $store));

            $startedAt = hrtime(true);

            foreach ($result->getContent() as $delta) {
                if (!$delta instanceof TextDelta) {
                    continue;
                }

                $content = (string) $delta;
                if ('' !== $content) {
                    yield new ServerEvent(json_encode(['token' => $content], \JSON_THROW_ON_ERROR));
                }
            }

            $usage = $result->getMetadata()->get('token_usage');
            $completionTokens = $usage instanceof TokenUsageInterface ? $usage->getCompletionTokens() : null;
            $durationMs = (int) ((hrtime(true) - $startedAt) / 1_000_000);

            yield new ServerEvent(json_encode([
                'done' => true,
                'model' => $chatModel,
                'completionTokens' => $completionTokens,
                'durationMs' => $durationMs,
            ], \JSON_THROW_ON_ERROR));
        });
    }
}
