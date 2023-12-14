<?php

namespace trinity\http;

use trinity\apiResponses\CreateResponse;
use trinity\apiResponses\DeleteResponse;
use trinity\apiResponses\HtmlResponse;
use trinity\apiResponses\JsonResponse;
use trinity\apiResponses\UpdateResponse;
use trinity\contracts\HttpKernelInterface;
use trinity\contracts\ResponseInterface;
use trinity\contracts\RouterInterface;
use trinity\exception\baseException\Exception;
use trinity\http\errorHandler\ErrorHandler;
use Throwable;

class HttpKernel implements HttpKernelInterface
{
    public function __construct(
        private RouterInterface   $router,
        private ErrorHandler      $errorHandler,
        private ResponseInterface $response,
    )
    {
        $this->errorHandler->register();
    }

    /**
     * @throws Exception
     */
    public function handle(): ResponseInterface
    {
        try {

            $output = $this->router->dispatch();

            return $this->normalizeResponse($output);

        } catch (Throwable $e) {

            $this->errorHandler->handleException($e);

        }

        throw new Exception;
    }

    private function normalizeResponse(object $output): ResponseInterface
    {
        $responseHandlers = match (get_class($output)) {
            JsonResponse::class => function ($output) {
                return $this->response = $this->response->withBody(json_encode($output))->withHeader('Content-Type', 'application/json');
            },

            HtmlResponse::class => function ($output) {
                return $this->response = $this->response->withHeader('Content-Type', 'text/html')->withBody($output);
            },

            CreateResponse::class => function ($output) {
                return $this->response = $this->response->withHeader('Content-Type', 'application/json')->withStatus(201, 'Created')->withBody($output);
            },

            DeleteResponse::class => function ($output) {
                return $this->response = $this->response->withHeader('Content-Type', 'application/json')->withStatus(204, 'Successfully deleted')->withBody($output);
            },

            UpdateResponse::class => function ($output) {
                return $this->response = $this->response->withHeader('Content-Type', 'application/json')->withStatus(204, 'Successfully updated')->withBody($output);
            }
        };

        return $responseHandlers($output->data);
    }
}
