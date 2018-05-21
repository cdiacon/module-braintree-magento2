<?php

namespace Magento\Braintree\Controller\Adminhtml\Virtual;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Braintree\Gateway\Request\ChannelDataBuilder;

/**
 * Class Save
 * @package Magento\Braintree\Controller\Adminhtml\Virtual
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class Save extends Action
{
    const ADMIN_RESOURCE = 'Magento_Sales::create';

    /**
     * @var BraintreeAdapter
     */
    protected $braintreeAdapter;

    /**
     * @var ChannelDataBuilder
     */
    protected $channelDataBuilder;

    /**
     * Save constructor.
     * @param Context $context
     * @param BraintreeAdapter $braintreeAdapter
     * @param ChannelDataBuilder $channelDataBuilder
     */
    public function __construct(
        Context $context,
        BraintreeAdapter $braintreeAdapter,
        ChannelDataBuilder $channelDataBuilder
    ) {
        parent::__construct($context);
        $this->braintreeAdapter = $braintreeAdapter;
        $this->channelDataBuilder = $channelDataBuilder;
    }

    /**
     * Attempt to take the payment through the braintree api
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $request = [
            'paymentMethodNonce' => $this->getRequest()->getParam('payment_method_nonce'),
            'amount' => $this->getRequest()->getParam('amount'),
            'options' => [
                'submitForSettlement' => true
            ]
        ];
        $request = array_merge($request, $this->channelDataBuilder->build([]));

        try {
            $response = $this->braintreeAdapter->sale($request);
            if ($response instanceof \Braintree\Result\Successful) {
                $message = "A payment has been made on the %s card ending %s for %s %s"
                    . " (Braintree Transaction ID: %s)";

                $message = sprintf(
                    __($message),
                    $response->transaction->creditCard['cardType'],
                    $response->transaction->creditCard['last4'],
                    $response->transaction->currencyIsoCode,
                    $response->transaction->amount,
                    $response->transaction->id
                );

                $this->messageManager->addSuccessMessage($message);
            } elseif ($response instanceof \Braintree\Result\Error) {
                throw new \Magento\Framework\Exception\LocalizedException(__($response->message));
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("The response from the Braintree server was incorrect. Please try again.")
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('braintree/virtual/index');
        return $resultRedirect;
    }
}