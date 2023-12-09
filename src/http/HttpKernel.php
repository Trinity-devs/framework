<?php

namespace src\http;

use src\apiResponses\CreateResponse;
use src\apiResponses\DeleteResponse;
use src\apiResponses\HtmlResponse;
use src\apiResponses\JsonResponse;
use src\apiResponses\UpdateResponse;
use src\contracts\HttpKernelInterface;
use src\contracts\ResponseInterface;
use src\contracts\RouterInterface;
use src\exception\baseException\Exception;
use src\Http\errorHandler\ErrorHandler;
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
