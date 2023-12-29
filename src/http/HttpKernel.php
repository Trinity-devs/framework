<?php

namespace trinity\http;

use Throwable;
use trinity\api\{responses\AuthResponse,
    responses\CreateResponse,
    responses\DeleteResponse,
    responses\HtmlResponse,
    responses\JsonResponse,
    responses\UpdateResponse};
use trinity\contracts\{handlers\error\ErrorHandlerHttpInterface,
    http\HttpKernelInterface,
    http\ResponseInterface,
    router\RouterInterface};

class HttpKernel implements HttpKernelInterface
{
    /**
     * @param RouterInterface $router
     * @param ErrorHandlerHttpInterface $errorHandler
     * @param ResponseInterface $response
     */
    public function __construct(
        private RouterInterface $router,
        private ErrorHandlerHttpInterface $errorHandler,
        private ResponseInterface $response,
    )
    {
        $this->errorHandler->register();
    }

    /**
     * @return ResponseInterface
     */
    public function handle(): ResponseInterface
    {
        try {

            $output = $this->router->dispatch();

            return $this->normalizeResponse($output);

        } catch (Throwable $e) {
            $this->errorHandler->setTypeResponse($this->router->getTypeResponse());

            return $this->normalizeResponse($this->errorHandler->handleException($e), $this->errorHandler->getStatusCode($e));
        }
    }

    /**
     * @param object $output
     * @param int $statusCode
     * @return ResponseInterface
     */
    private function normalizeResponse(object $output, int $statusCode = 200): ResponseInterface
    {
        $responseHandlers = match (get_class($output)) {
            JsonResponse::class => function ($output, $statusCode) {
                return $this->response = $this->response->withBody(json_encode($output))->withHeader('Content-Type', 'application/json')->withStatus($statusCode ?? 200);
            },

            HtmlResponse::class => function ($output, $statusCode) {
                return $this->response = $this->response->withHeader('Content-Type', 'text/html')->withBody($output)->withStatus($statusCode ?? 200);
            },

            AuthResponse::class => function ($output) {
                return $this->response = $this->response->withHeader('Content-Type', 'application/json')->withStatus(201, 'Successful entry')->withBody(json_encode($output));
            },

            CreateResponse::class => function ($output) {
                return $this->response = $this->response->withHeader('Content-Type', 'application/json')->withStatus(201, 'Created')->withBody($output);
            },

            DeleteResponse::class => function ($output) {
                return $this->response = $this->response->withHeader('Content-Type', 'application/json')->withStatus(204, 'Successfully deleted')->withBody($output);
            },

            UpdateResponse::class => function ($output) {
                return $this->response = $this->response->withHeader('Content-Type', 'application/json')->withStatus(200, 'Successfully updated')->withBody($output);
            }
        };

        return $responseHandlers($output->data, $statusCode);
    }
}
