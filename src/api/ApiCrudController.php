<?php

namespace trinity\api;

use trinity\api\responses\CreateResponse;
use trinity\api\responses\DeleteResponse;
use trinity\api\responses\JsonResponse;
use trinity\api\responses\UpdateResponse;
use trinity\contracts\FormRequestFactoryInterface;
use trinity\exception\httpException\NotFoundHttpException;
use trinity\validator\AbstractFormRequest;

abstract class ApiCrudController
{
    const CREATE = 'create';
    const UPDATE = 'update';
    const PATCH = 'patch';

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
        $data = $this->listItem();

        return new JsonResponse($data);
    }

    /**
     * @param FormRequestFactoryInterface $formRequestFactory
     * @return CreateResponse
     */
    public function actionCreate(FormRequestFactoryInterface $formRequestFactory): CreateResponse
    {
        $form = $formRequestFactory->create($this->forms[self::CREATE]);

        $data = call_user_func_array([$this, self::CREATE], [$form]);

        return new CreateResponse($data);
    }

    /**
     * @param FormRequestFactoryInterface $formRequestFactory
     * @return UpdateResponse
     */
    public function actionUpdate(FormRequestFactoryInterface $formRequestFactory): UpdateResponse
    {
        $form = $formRequestFactory->create($this->forms[self::UPDATE]);

        $data = call_user_func_array([$this, self::UPDATE], [$form]);

        return new UpdateResponse($data);
    }

    /**
     * @param FormRequestFactoryInterface $formRequestFactory
     * @return UpdateResponse
     */
    public function actionPatch(FormRequestFactoryInterface $formRequestFactory): UpdateResponse
    {
        $form = $formRequestFactory->create($this->forms[self::PATCH]);

        $data = call_user_func_array([$this, self::PATCH], [$form]);

        return new UpdateResponse($data);
    }

    /**
     * @return DeleteResponse
     * @throws NotFoundHttpException
     */
    public function actionDelete(): DeleteResponse
    {
        $data = $this->delete();

        return new DeleteResponse($data);
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
     * @return array
     * @throws NotFoundHttpException
     */
    protected function listItem(): array
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
     * @param AbstractFormRequest $form
     * @return void
     * @throws NotFoundHttpException
     */
    protected function update(AbstractFormRequest $form): void
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
     * @return void
     * @throws NotFoundHttpException
     */
    protected function delete(): void
    {
        throw new NotFoundHttpException('Not found');
    }
}