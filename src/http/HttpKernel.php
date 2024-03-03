<?php

namespace trinity\http;

use Throwable;
use trinity\api\responses\{AuthResponse, CreateResponse, DeleteResponse, HtmlResponse, JsonResponse, UpdateResponse};
use trinity\contracts\{http\HttpKernelInterface, http\ResponseInterface, router\RouterInterface};

class HttpKernel implements HttpKernelInterface
{
    /**
     * @param RouterInterface $router
     * @param ResponseInterface $response
     */
    public function __construct(
        private RouterInterface $router,
        private ResponseInterface $response,
    ) {
    }

    /**
     * @return ResponseInterface
     * @throws Throwable
     */
    public function handle(): ResponseInterface
    {
        $output = $this->router->dispatch();

        return $this->normalizeResponse($output);
    }

    /**
     * @param object $output
     * @return ResponseInterface
     */
    private function normalizeResponse(object $output): ResponseInterface
    {
        $responseHandlers = match (get_class($output)) {
            JsonResponse::class => function ($output) {
                return $this->response = $this->response->withBody(json_encode($output))->withHeader(
                    'Content-Type',
                    'application/json'
                );
            },

            HtmlResponse::class => function ($output) {
                return $this->response = $this->response->withHeader('Content-Type', 'text/html')->withBody(
                    $output
                );
            },

            AuthResponse::class => function ($output) {
                return $this->response = $this->response->withHeader('Content-Type', 'application/json')->withStatus(
                    201,
                    'Successful entry'
                )->withBody(json_encode($output));
            },

            CreateResponse::class => function ($output) {
                return $this->response = $this->response->withHeader('Content-Type', 'application/json')->withStatus(
                    201,
                    'Created'
                )->withBody($output);
            },

            DeleteResponse::class => function ($output) {
                return $this->response = $this->response->withHeader('Content-Type', 'application/json')->withStatus(
                    204,
                    'Successfully deleted'
                )->withBody($output);
            },

            UpdateResponse::class => function ($output) {
                return $this->response = $this->response->withHeader('Content-Type', 'application/json')->withStatus(
                    200,
                    'Successfully updated'
                )->withBody($output);
            }
        };

        return $responseHandlers($output->data);
    }
}