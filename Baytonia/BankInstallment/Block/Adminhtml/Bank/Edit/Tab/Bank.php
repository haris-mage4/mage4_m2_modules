<?php

namespace Baytonia\BankInstallment\Block\Adminhtml\Bank\Edit\Tab;

use Baytonia\BankInstallment\Model\Status;

class Bank extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $_objectFactory;

    /**
     * @var \Baytonia\BankInstallment\Model\Bank
     */
    protected $_bank;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;
    
    protected $_systemStore;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\DataObjectFactory $objectFactory
     * @param \Baytonia\BankInstallment\Model\Bank $bank
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\DataObjectFactory $objectFactory,
        \Baytonia\BankInstallment\Model\Bank $bank,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = [],
        \Magento\Store\Model\System\Store $systemStore
    ) {
        $this->_objectFactory = $objectFactory;
        $this->_bank = $bank;
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * prepare layout.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('page.title')->setPageTitle($this->getPageTitle());
        
        \Magento\Framework\Data\Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock(
                'Baytonia\BankInstallment\Block\Adminhtml\Form\Renderer\Fieldset\Element',
                $this->getNameInLayout().'_fieldset_element'
            )
        );

        return $this;

    }

    /**
     * Prepare form.
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $bankAttributes = $this->_bank->getStoreAttributes();
        $bankAttributesInStores = ['store_id' => ''];

        foreach ($bankAttributes as $bankAttribute) {
            $bankAttributesInStores[$bankAttribute.'_in_store'] = '';
        }

        $dataObj = $this->_objectFactory->create(
            ['data' => $bankAttributesInStores]
        );
        $model = $this->_coreRegistry->registry('bank');

        $dataObj->addData($model->getData());
        
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix($this->_bank->getFormFieldHtmlIdPrefix());

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Bank Information')]);

        if ($model->getId()) {
            $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }

        $elements = [];
        $elements['name'] = $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true,
            ]
        );
        
        $fieldset->addType('image', '\Baytonia\BankInstallment\Block\Adminhtml\Bank\Helper\Image');
        
        $image_path = null;
        if(preg_match('~\.(png|gif|jpe?g|bmp)~i', $this->_bank->getImage()))
              $image_path =  $this->_bank->getImage();
        
        if (!$this->_storeManager->isSingleStoreMode()) {
            $elements['store_id'] = $fieldset->addField(
                    'store_id', 'multiselect', [
                'name' => 'store_id[]',
                'label' => __('Store View'),
                'title' => __('Store View'),
                'required' => true,
                'values' => $this->_systemStore->getStoreValuesForForm(false, true),
                'disabled' => false,
                'value' => (null !== $model->getStoreId() ? $model->getStoreId() : 0)
                    ]
            );
            $renderer = $this->getLayout()->createBlock(
                    'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $elements['store_id']->setRenderer($renderer);
        } else {
            $elements['store_id'] = $fieldset->addField(
                    'store_id', 'hidden', ['name' => 'store_id[]', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
            $model->setStoreId($this->_storeManager->getStore(true)->getId());
        }



        $elements['image'] = $fieldset->addField(
            'image',
            'image',
            [
                'title' => __('Bank Logo Image'),
                'label' => __('Bank Logo Image'),
                'name' => 'image',
                'path' => $image_path,
                'note' => 'Allow image type: jpg, jpeg, gif, png',
                'required' => true,
                'value' => $image_path,
                'renderer' => 'Baytonia\BankInstallment\Block\Adminhtml\Bank\Helper\Renderer\Image'
            ]
        )->setAfterElementHtml('
        <script>    

            require([
                 "jquery",
            ], function($){
                $(document).ready(function () {                
                    if($("#page_image").attr("value")){
                        $("#page_image").removeClass("required-file");
                    }else{
                        $("#page_image").addClass("required-file");
                    }
                    $( "#page_image" ).attr( "accept", "image/x-png,image/gif,image/jpeg,image/jpg,image/png" );                    
                    
                });
              });
       </script>
    ');

        $elements['url'] = $fieldset->addField(
            'url',
            'text',
            [
                'title' => __('Bank Url'),
                'label' => __('Bank Url'),
                'name' => 'url',
                'required' => true
            ]
        );
        
        $elements['Sort Oder'] = $fieldset->addField(
            'sort_order',
            'text',
            [
                'label' => __('Sort Oder'),
                'title' => __('Sort Oder'),
                'name' => 'sort_order'
            ]
        );
        
        $elements['status'] = $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Bank Status'),
                'name' => 'status',
                'options' => Status::getAvailableStatuses(),
            ]
        );
        
        $form->addValues($dataObj->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return mixed
     */
    public function getBank()
    {
        return $this->_coreRegistry->registry('bank');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getPageTitle()
    {
        return $this->getBank()->getId()
            ? __("Edit Bank '%1'", $this->escapeHtml($this->getBank()->getName())) : __('New Bank Logo');
    }

    /**
     * Prepare label for tab.
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Bank Information');
    }

    /**
     * Prepare title for tab.
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Bank Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
