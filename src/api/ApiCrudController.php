<?php

namespace trinity\api;

use trinity\{api\responses\CreateResponse,
    api\responses\DeleteResponse,
    api\responses\JsonResponse,
    api\responses\UpdateResponse,
    contracts\http\RequestInterface,
    contracts\validator\FormRequestFactoryInterface,
    exception\httpException\NotFoundHttpException,
    validator\AbstractFormRequest};

abstract class ApiCrudController
{
    const CREATE = 'create';
    const UPDATE = 'update';
    const PATCH = 'patch';

    /**
     * @param RequestInterface $request
     */
    public function __construct(
        private RequestInterface $request,
    )
    {
    }

    protected array $forms = [];

    /**
     * @return JsonResponse
     * @throws NotFoundHttpException
     */
    public function actionList(): JsonResponse
    {
        $data = $this->list();

        return new JsonResponse($data);
    }

    /**
     * @return JsonResponse
     * @throws NotFoundHttpException
     */
    public function actionListItem(): JsonResponse
    {
        $data = $this->listItem($this->request->get('id'));

        return new JsonResponse($data);
    }

    /**
     * @param FormRequestFactoryInterface $formRequestFactory
     * @return CreateResponse
     * @throws NotFoundHttpException
     */
    public function actionCreate(FormRequestFactoryInterface $formRequestFactory): CreateResponse
    {
        $form = $formRequestFactory->create($this->forms[self::CREATE]);

        $this->create($form);

        return new CreateResponse();
    }

    /**
     * @param FormRequestFactoryInterface $formRequestFactory
     * @return UpdateResponse
     * @throws NotFoundHttpException
     */
    public function actionUpdate(FormRequestFactoryInterface $formRequestFactory): UpdateResponse
    {
        $form = $formRequestFactory->create($this->forms[self::UPDATE]);

        $this->update($this->request->get('id'), $form);

        return new UpdateResponse();
    }

    /**
     * @param FormRequestFactoryInterface $formRequestFactory
     * @return UpdateResponse
     * @throws NotFoundHttpException
     */
    public function actionPatch(FormRequestFactoryInterface $formRequestFactory): UpdateResponse
    {
        $form = $formRequestFactory->create($this->forms[self::PATCH]);

        $this->patch($form);

        return new UpdateResponse();
    }

    /**
     * @return DeleteResponse
     * @throws NotFoundHttpException
     */
    public function actionDelete(): DeleteResponse
    {
        $this->delete($this->request->get('id'));

        return new DeleteResponse();
    }

    /**
     * @return array
     * @throws NotFoundHttpException
     */
    protected function list(): array
    {
        throw new NotFoundHttpException('Not found');
    }

    /**
     * @param int|string $id
     * @return array
     * @throws NotFoundHttpException
     */
    protected function listItem(int|string $id): array
    {
        throw new NotFoundHttpException('Not found');
    }

    /**
     * @param AbstractFormRequest $form
     * @return void
     * @throws NotFoundHttpException
     */
    protected function create(AbstractFormRequest $form): void
    {
        throw new NotFoundHttpException('Not found');
    }

    /**
     * @param int|string $id
     * @param AbstractFormRequest $form
     * @return void
     * @throws NotFoundHttpException
     */
    protected function update(int|string $id, AbstractFormRequest $form): void
    {
        throw new NotFoundHttpException('Not found');
    }

    /**
     * @param AbstractFormRequest $form
     * @return void
     * @throws NotFoundHttpException
     */
    protected function patch(AbstractFormRequest $form): void
    {
        throw new NotFoundHttpException('Not found');
    }

    /**
     * @param int|string $id
     * @return void
     * @throws NotFoundHttpException
     */
    protected function delete(int|string $id): void
    {
        throw new NotFoundHttpException('Not found');
    }
}