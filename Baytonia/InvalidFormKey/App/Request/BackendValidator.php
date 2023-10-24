<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Baytonia\InvalidFormKey\App\Request;

use Magento\Backend\App\AbstractAction;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\Request\ValidatorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Backend\Model\Auth;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\Result\Raw as RawResult;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Backend\Model\UrlInterface as BackendUrl;
use Magento\Framework\Phrase;
use Magento\Framework\App\ObjectManager;

class BackendValidator extends \Magento\Backend\App\Request\BackendValidator
{

    /**
     * @var Auth
     */
    private $auth;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var BackendUrl
     */
    private $backendUrl;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var RawFactory
     */
    private $rawResultFactory;

    /**
     * @inheritDoc
     */
    public function validate(
        RequestInterface $request,
        ActionInterface $action
    ): void {
        $exception = null;
        if (!$this->validateRequest($request, $action)) {
            throw $this->createException($request, $action);
        }
    }

    /**
     * Validate request
     *
     * @param RequestInterface $request
     * @param ActionInterface $action
     *
     * @return bool
     */
    private function validateRequest(
        RequestInterface $request,
        ActionInterface $action
    ): bool {
        /** @var bool|null $valid */
        $valid = null;
        if ($action instanceof CsrfAwareActionInterface) {
            $valid = $action->validateForCsrf($request);
        }

        $this->auth = ObjectManager::getInstance()->create(Auth::class);
        $this->backendUrl = ObjectManager::getInstance()->create(BackendUrl::class);
        $this->formKeyValidator = ObjectManager::getInstance()->get(FormKeyValidator::class);

        if ($valid === null) {
            $validFormKey = true;
            $validSecretKey = true;
            if ($request instanceof HttpRequest && $request->isPost()) {
                $validFormKey = $this->formKeyValidator->validate($request);
            } elseif ($this->auth->isLoggedIn()
                && $this->backendUrl->useSecretKey()
            ) {
                $secretKeyValue = (string)$request->getParam(
                    BackendUrl::SECRET_KEY_PARAM_NAME,
                    null
                );
                $secretKey = $this->backendUrl->getSecretKey();
                $validSecretKey = ($secretKeyValue === $secretKey);
            }
            $valid = $validFormKey && $validSecretKey;
        }
        return $valid;
    }

    /* Create exception
     *
     * @param RequestInterface $request
     * @param ActionInterface $action
     *
     * @return InvalidRequestException
     */
    private function createException(
        RequestInterface $request,
        ActionInterface $action
    ): InvalidRequestException {
        /** @var InvalidRequestException|null $exception */
        $exception = null;

        if ($action instanceof CsrfAwareActionInterface) {
            $exception = $action->createCsrfValidationException($request);
        }

        if ($exception === null) {
            if ($request instanceof HttpRequest && $request->isAjax()) {
                //Sending empty response for AJAX request since we don't know
                //the expected response format and it's pointless to redirect.
                /** @var RawResult $response */
                $response = $this->rawResultFactory->create();
                $response->setHttpResponseCode(401);
                $response->setContents('');
                $exception = new InvalidRequestException($response);
            } else {
                 $this->backendUrl = ObjectManager::getInstance()->create(BackendUrl::class);
                 $this->redirectFactory = ObjectManager::getInstance()->create(RedirectFactory::class);
                //For regular requests.
                $startPageUrl = $this->backendUrl->getStartupPageUrl();
                $response = $this->redirectFactory->create()
                    ->setUrl($this->backendUrl->getUrl($startPageUrl));
                $exception = new InvalidRequestException($response);
            }
        }

        return $exception;
    }
}
