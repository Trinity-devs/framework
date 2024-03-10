<?php

namespace trinity\http;

use GuzzleHttp\Psr7\Utils;
use Throwable;
use trinity\api\responses\{AuthResponse, CreateResponse, DeleteResponse, HtmlResponse, JsonResponse, UpdateResponse};
use trinity\contracts\http\{HttpKernelInterface, ResponseInterface};
use trinity\contracts\router\RouterInterface;

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
                return $this->response = $this->response
                    ->withBody(Utils::streamFor(json_encode($output, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)))
                    ->withHeader('Content-Type','application/json');
            },

            HtmlResponse::class => function ($output) {
                return $this->response = $this->response->withHeader('Content-Type', 'text/html')->withBody(
                    Utils::streamFor($output)
                );
            },

            AuthResponse::class => function ($output) {
                return $this->response = $this->response->withHeader('Content-Type', 'application/json')->withStatus(
                    201,
                    'Successful entry'
                )->withBody(Utils::streamFor(json_encode($output, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)));
            },

            CreateResponse::class => function ($output) {
                return $this->response = $this->response->withHeader('Content-Type', 'application/json')->withStatus(
                    201,
                    'Created'
                )->withBody(Utils::streamFor($output));
            },

            DeleteResponse::class => function ($output) {
                return $this->response = $this->response->withHeader('Content-Type', 'application/json')->withStatus(
                    204,
                    'Successfully deleted'
                )->withBody(Utils::streamFor($output));
            },

            UpdateResponse::class => function ($output) {
                return $this->response = $this->response->withHeader('Content-Type', 'application/json')->withStatus(
                    200,
                    'Successfully updated'
                )->withBody(Utils::streamFor($output));
            }
        };

        return $responseHandlers($output->data);
    }
}
