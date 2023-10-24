<?php

namespace Baytonia\BankInstallment\Api;

interface BankRepositoryInterface
{
    /**
     * get bank collection of bankinstallment.
     *
     * @return \Baytonia\BankInstallment\Model\ResourceModel\Bank\Collection
     */
    public function getBankCollection();
}