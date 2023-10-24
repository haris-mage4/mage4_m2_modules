<?php

namespace Baytonia\CustomApis\Controller\Webkul\MobikulApi\Customer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;

class SaveAccountInfo extends \Webkul\MobikulApi\Controller\Customer\SaveAccountInfo
{
    protected $countryCode;

    protected $telephone;

public function execute()
{
    try {
        $this->verifyRequest();
        // Checking customer token //////////////////////////////////////////////
        if (!$this->customerId && $this->customerToken != "") {
            $this->returnArray["otherError"] = "customerNotExist";
            throw new \Magento\Framework\Exception\LocalizedException(
                __("As customer you are requesting does not exist, so you need to logout.")
            );
        }
        $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
        $this->dob = $this->localeDate->formatDate($this->dob);
        $currentCustomerDataObject = $this->customerRepositoryInterface->getById($this->customerId);
        $inputData = [
            "dob" => $this->dob,
            "email" => $this->email,
            "prefix" => $this->prefix,
            "suffix" => $this->suffix,
            "taxvat" => $this->taxvat,
            "gender" => $this->gender,
            "lastname" => $this->lastName,
            "password" => $this->newPassword,
            "firstname" => $this->firstName,
            "middlename" => $this->middleName,
            'telephone_country_code' => $this->countryCode,
            'telephone' => $this->telephone,
            "password_confirmation" => $this->confirmPassword,
            "current_password" => $this->currentPassword
        ];
        $this->request->setParams($inputData);
        $storeManager = $this->storeManager;
        $customerCheck = $this->customerFactory->create()->setWebsiteId(
            $storeManager->getStore()->getWebsiteId()
        )->loadByEmail($this->email);
        $checkCustomerId = $customerCheck->getId();
        if ($checkCustomerId > 0 && $checkCustomerId != $this->customerId) {
            $this->returnArray["message"] = __(
                "A customer with the same email already exists in an associated website."
            );
            return $this->getJsonResponse($this->returnArray);
        }
        $inputData = $this->request;
        $customerCandidateDataObject = $this->populateNewCustomerDataObject($inputData, $currentCustomerDataObject);
        if ($this->doChangeEmail == 1) {
          /**  try {
                $this->getAuthentication()->authenticate(
                    $currentCustomerDataObject->getId(),
                    $this->currentPassword
                );
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("The password doesn't match this account.")
                );
            } */
            $this->customerRepositoryInterface->save($customerCandidateDataObject);
        }
        $isPasswordChanged = false;
        if ($this->doChangePassword == 1) {
            if ($this->newPassword != $this->confirmPassword) {
                throw new InputException(__("Password confirmation doesn't match entered password."));
            }
            $isPasswordChanged = $this->accountManagement->changePassword(
                $this->email,
                $this->currentPassword,
                $this->newPassword
            );
            $this->customerRepositoryInterface->save($customerCandidateDataObject);
        }
        $this->customerRepositoryInterface->save($customerCandidateDataObject);
        $this->getEmailNotification()->credentialsChanged(
            $customerCandidateDataObject,
            $currentCustomerDataObject->getEmail(),
            $isPasswordChanged
        );
        $customer = $this->customerFactory->create()->load($this->customerId);
        $this->returnArray["success"] = true;
        $this->returnArray["message"] = __("You saved the account information.");
        $this->returnArray["customerName"] = $customer->getName();
        $this->emulate->stopEnvironmentEmulation($environment);
        return $this->getJsonResponse($this->returnArray);
    } catch (\InvalidEmailOrPasswordException $e) {
        $this->returnArray["message"] = __($e->getMessage());
        return $this->getJsonResponse($this->returnArray);
    } catch (\UserLockedException $e) {
        $this->returnArray["message"] = __(
            "The account is locked. Please wait and try again or contact %1.",
            $this->getScopeConfig()->getValue("contact/email/recipient_email")
        );
        return $this->getJsonResponse($this->returnArray);
    } catch (InputException $e) {
        $message = [];
        $message[] = __($e->getMessage());
        foreach ($e->getErrors() as $error) {
            $message[] = $error->getMessage();
        }
        $this->returnArray["message"] = implode(",", $message);
        return $this->getJsonResponse($this->returnArray);
    } catch (\Magento\Framework\Exception\LocalizedException $e) {
        $this->returnArray["message"] = __($e->getMessage());
        return $this->getJsonResponse($this->returnArray);
    } catch (\Exception $e) {
        $this->returnArray["message"] = $e->getMessage();
        return $this->getJsonResponse($this->returnArray);
    }
}
protected function verifyRequest()
{
    if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
        $this->dob = $this->wholeData["dob"] ?? "";
        $this->email = $this->wholeData["email"] ?? "";
        $this->mobile = $this->wholeData["mobile"] ?? "";
        $this->prefix = $this->wholeData["prefix"] ?? "";
        $this->suffix = $this->wholeData["suffix"] ?? "";
        $this->taxvat = $this->wholeData["taxvat"] ?? "";
        $this->gender = $this->wholeData["gender"] ?? "";
        $this->storeId = $this->wholeData["storeId"] ?? 1;
        $this->lastName = $this->wholeData["lastName"] ?? "";
        $this->firstName = $this->wholeData["firstName"] ?? "";
        $this->middleName = $this->wholeData["middleName"] ?? "";
        $this->countryCode = $this->wholeData['countryCode'] ?? "";
        $this->telephone = $this->wholeData['telephone'] ?? "";
        $this->newPassword = $this->wholeData["newPassword"] ?? "";
        $this->doChangeEmail = $this->wholeData["doChangeEmail"] ?? 0;
        $this->customerToken = $this->wholeData["customerToken"] ?? "";
        $this->confirmPassword = $this->wholeData["confirmPassword"] ?? "";
        $this->currentPassword = $this->wholeData["currentPassword"] ?? "";
        $this->doChangePassword = $this->wholeData["doChangePassword"] ?? 0;
        $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
    } else {
        throw new \BadMethodCallException(__("Invalid Request"));
    }
}
    private function getEmailNotification()
    {
        if (!($this->emailNotification instanceof \Magento\Customer\Model\EmailNotificationInterface)) {
            return ObjectManager::getInstance()->get(\Magento\Customer\Model\EmailNotificationInterface::class);
        } else {
            return $this->emailNotification;
        }
    }

    /**
     * Verify Request function to verify the request
     *
     * @return void|jSon
     */
    private function populateNewCustomerDataObject(
        \Magento\Framework\App\RequestInterface $inputData,
        \Magento\Customer\Api\Data\CustomerInterface $currentCustomerData
    ) {
        $attributeValues = $this->getCustomerMapper()->toFlatArray($currentCustomerData);
        $customerDto = $this->customerExtractor->extract(
            "customer_account_edit",
            $inputData,
            $attributeValues
        );
        $customerDto->setId($currentCustomerData->getId());
        if (!$customerDto->getAddresses()) {
            $customerDto->setAddresses($currentCustomerData->getAddresses());
        }
        if (!$this->doChangeEmail) {
            $customerDto->setEmail($currentCustomerData->getEmail());
        }
        return $customerDto;
    }

    private function getCustomerMapper()
    {
        return $this->customerMapper;
    }

    private function getScopeConfig()
    {
        if (!($this->scopeConfig instanceof \Magento\Framework\App\Config\ScopeConfigInterface)) {
            return ObjectManager::getInstance()->get(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        } else {
            return $this->scopeConfig;
        }
    }

    private function getAuthentication()
    {
        if (!($this->authentication instanceof \Magento\Customer\Model\AuthenticationInterface)) {
            return ObjectManager::getInstance()->get(\Magento\Customer\Model\AuthenticationInterface::class);
        } else {
            return $this->authentication;
        }
    }

}
