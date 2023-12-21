<?php

namespace trinity\api;

use trinity\api\responses\CreateResponse;
use trinity\api\responses\DeleteResponse;
use trinity\api\responses\JsonResponse;
use trinity\api\responses\UpdateResponse;
use trinity\exception\httpException\NotFoundHttpException;

abstract class ApiCrudController
{
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
     * @return CreateResponse
     * @throws NotFoundHttpException
     */
    public function actionCreate(): CreateResponse
    {
        $data = $this->create();

        return new CreateResponse($data);
    }

    /**
     * @return UpdateResponse
     * @throws NotFoundHttpException
     */
    public function actionUpdate(): UpdateResponse
    {
        $data = $this->update();

        return new UpdateResponse($data);
    }

    /**
     * @return UpdateResponse
     * @throws NotFoundHttpException
     */
    public function actionPatch(): UpdateResponse
    {
        $data = $this->patch();

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
     * @throws NotFoundHttpException
     */
    protected function create(): void
    {
        throw new NotFoundHttpException('Not found');
    }

    /**
     * @throws NotFoundHttpException
     */
    protected function update(): array
    {
        throw new NotFoundHttpException('Not found');
    }

    /**
     * @throws NotFoundHttpException
     */
    protected function patch(): array
    {
        throw new NotFoundHttpException('Not found');
    }

    /**
     * @throws NotFoundHttpException
     */
    protected function delete(): array
    {
        throw new NotFoundHttpException('Not found');
    }
}