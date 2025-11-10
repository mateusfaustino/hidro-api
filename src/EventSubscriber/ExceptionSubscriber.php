<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Domain\Auth\Exception\InvalidCredentialsException;
use App\Domain\Auth\Exception\InvalidRefreshTokenException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Exception Subscriber
 * 
 * Captura e trata todas as exceções da aplicação
 * Loga erros detalhados e retorna respostas padronizadas RFC 7807
 */
class ExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $environment
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 0],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        // Log detalhado da exceção
        $this->logException($exception, $request->getPathInfo());

        // Se for uma HttpException, deixa o Symfony tratar
        if ($exception instanceof HttpExceptionInterface) {
            return;
        }

        // Monta resposta RFC 7807 Problem Details
        $response = $this->createProblemResponse($exception);
        
        $event->setResponse($response);
    }

    private function logException(\Throwable $exception, string $path): void
    {
        $context = [
            'exception_class' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'path' => $path,
            'trace' => $exception->getTraceAsString(),
        ];

        // Log com nível apropriado baseado no tipo de exceção
        if ($exception instanceof InvalidCredentialsException || 
            $exception instanceof InvalidRefreshTokenException) {
            $this->logger->info('Authentication failed', $context);
        } else {
            $this->logger->error('Application exception occurred', $context);
        }
    }

    private function createProblemResponse(\Throwable $exception): JsonResponse
    {
        $statusCode = 500;
        $type = 'internal_server_error';
        $title = 'Internal Server Error';
        $detail = 'An unexpected error occurred.';

        // Exceções de autenticação
        if ($exception instanceof InvalidCredentialsException) {
            $statusCode = 401;
            $type = 'invalid_credentials';
            $title = 'Invalid Credentials';
            $detail = $exception->getMessage();
        } elseif ($exception instanceof InvalidRefreshTokenException) {
            $statusCode = 401;
            $type = 'invalid_refresh_token';
            $title = 'Invalid Refresh Token';
            $detail = $exception->getMessage();
        }

        $problemDetails = [
            'type' => $type,
            'title' => $title,
            'status' => $statusCode,
            'detail' => $detail,
        ];

        // Em ambiente de desenvolvimento, adiciona informações extras
        if ($this->environment === 'dev') {
            $problemDetails['debug'] = [
                'exception_class' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => explode("\n", $exception->getTraceAsString()),
            ];
        }

        return new JsonResponse($problemDetails, $statusCode);
    }
}
