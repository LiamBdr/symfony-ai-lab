<?php

namespace App\Controller;

use App\Dto\ChatMessageInput;
use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Chat\ManagedStoreInterface;
use Symfony\AI\Chat\MessageStoreInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\TokenUsage\TokenUsageInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\EventStreamResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ServerEvent;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Attribute\RateLimit;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

final class ChatController extends AbstractController
{
    #[Route('/', name: 'app_chat_index', methods: ['GET'])]
    public function index(
        #[Autowire(service: 'ai.message_store.session.default')] MessageStoreInterface&ManagedStoreInterface $store,
    ): Response {
        $store->drop();

        return $this->render('chat.html.twig');
    }

    #[Route('/chat/stream', name: 'app_chat_stream', methods: ['POST'], format: 'json')]
    #[RateLimit('chat')]
    #[IsCsrfTokenValid('chat', tokenKey: 'X-CSRF-Token', tokenSource: IsCsrfTokenValid::SOURCE_HEADER)]
    public function send(
        #[MapRequestPayload(acceptFormat: 'json')] ChatMessageInput $input,
        #[Autowire(env: 'OLLAMA_CHAT_MODEL')] string $chatModel,
        #[Autowire(service: 'ai.agent.default')] AgentInterface $agent,
        #[Autowire(service: 'ai.message_store.session.default')] MessageStoreInterface $store,
        #[Autowire(service: 'profiler')] ?Profiler $profiler,
    ): EventStreamResponse {
        $profiler?->disable();

        $userMessage = $input->message;

        return new EventStreamResponse(static function () use ($agent, $store, $userMessage, $chatModel): iterable {
            set_time_limit(0);

            $messages = $store->load();
            $messages->add(Message::ofUser($userMessage));

            $startedAt = hrtime(true);

            $execution = $agent->call($messages, ['stream' => true]);

            foreach ($execution->asTextStream() as $delta) {
                $content = (string) $delta;
                if ('' !== $content) {
                    yield new ServerEvent(json_encode(['token' => $content], \JSON_THROW_ON_ERROR));
                }
            }

            $assistantMessage = Message::ofAssistant($execution->getResult());
            $assistantMessage->getMetadata()->merge($execution->getMetadata());
            $messages->add($assistantMessage);
            $store->save($messages);

            $usage = $execution->getMetadata()->get('token_usage');
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
