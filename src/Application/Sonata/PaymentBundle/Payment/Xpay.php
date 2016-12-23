<?php
/**
 * Created by PhpStorm.
 * User: RICCA
 * Date: 13/11/2016
 * Time: 19:53
 */

namespace Application\Sonata\PaymentBundle\Payment;


use Application\Sonata\OrderBundle\Entity\Order;
use Psr\Log\LoggerInterface;
use Sonata\Component\Basket\BasketInterface;
use Sonata\Component\Order\OrderInterface;
use Sonata\Component\Payment\BasePayment;
use Sonata\Component\Payment\TransactionInterface;
use Sonata\Component\Product\ProductInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class Xpay extends BasePayment
{
    /**
     * @var RouterInterface
     */
    protected $router;

    protected $translator;

    protected $chiaveMac = '8t37Qy7Fk4IYkVy861NH1lUe27136B7B3120Jm52';

    protected $divisa = 'EUR';

    protected $languageId = 'ITA';

    protected $alias = 'payment_1504508';

    public function __construct(RouterInterface $router, TranslatorInterface $translator = null)
    {
        $this->setEnabled(1);
        $this->setOptions(array(
            'url_callback'=>    'sonata_payment_callback',
            'url_return_ko'=>   'sonata_payment_error',
            'url_return_ok'=>   'sonata_payment_confirmation'
        ));

        $this->router = $router;
        $this->translator = $translator;
    }

    public function getCode()
    {
        return 'xpay';
    }

    public function getName()
    {
        return 'Carta di Credito (Xpay)';
    }

    /**
     * Send information to the bank, this method should handle
     * everything when called
     *
     * @param  \Sonata\Component\Order\OrderInterface $order
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sendbank(OrderInterface $order)
    {
        $params = array(
            'order' => $order->getReference(),
            'bank'  => $this->getCode(),
            'check' => $this->generateUrlCheck($order),
        );
/*
            'notify_url'    => $this->router->generate($this->getOption('url_callback'), $params, true),

            // user link
            'cancel_return' => $this->router->generate($this->getOption('url_return_ko'), $params, true),
            'return'        => $this->router->generate($this->getOption('url_return_ok'), $params, true),
*/
        $url_action = 'https://ecommerce.keyclient.it/ecomm/ecomm/DispatcherServlet';
        $alias = $this->alias;
        $codTrans = $order->getReference(); // Inserisci qui il tuo identificativo dell'ordine;
        $importo_da_passare = (int)(round($order->getTotalInc(), 2) * 100);
        $divisa = $this->divisa;
        $languageId = $this->languageId;
        $url_back = $this->router->generate($this->getOption('url_return_ko'), $params, true);
        $url = $this->router->generate($this->getOption('url_callback'), $params, true);
        $mac = $this->getMac($order);

        $html = '<html><body onload="document.getElementById(\'submit_button\').disabled = \'disabled\'; document.getElementById(\'formPaiement\').submit();">';
        $html .= '<div class=" col-xs-12 col-sm-6 col-sm-offset-3 no-pad " >
        <div class="col-xs-12 col-sm-6 col-sm-offset-3 sonata-payment-confirmation text-center no-pad"  >
            <div class="text-up-bold-2 no-pad col-xs-12 col-sm-12 marg-top-10"  >';
        $html .= $this->translator->trans('process_to_paiement_bank_page', array(), 'SonataPaymentBundle', 'it');
        $html .= '</div></div>';
        $html .= '<div class=" col-xs-12 text-center ">
        <p>';
        $html .= "
        <form id='formPaiement' name='formKeyclient' method='post' action='$url_action'>
          <input type='hidden' name='alias' value='$alias'>
          <input type='hidden' name='importo' value='$importo_da_passare'>
          <input type='hidden' name='divisa' value='$divisa'>
          <input type='hidden' name='codTrans' value='$codTrans'>
          <input type='hidden' name='url' value='$url'>
          <input type='hidden' name='urlPost' value='$url'>
          <input type='hidden' name='url_back' value='$url_back'>
          <input type='hidden' name='languageId' value='$languageId'>
          <input type='hidden' name='mac' value='$mac'>
          <input type='submit' id='submit_button' class='btn basket-control' value='INVIA PAGAMENTO'>
        </form>
        </p>
        </div>
        </div>";

        $html .= '</body></html>';

        $response = new \Symfony\Component\HttpFoundation\Response($html, 200, array(
            'Content-Type' => 'text/html'
        ));
        $response->setPrivate(true);

        return $response;
    }

    /**
     *
     * @param  \Sonata\Component\Payment\TransactionInterface $transaction
     * @return boolean                                        true if callback ok else false
     */
    public function isCallbackValid(TransactionInterface $transaction)
    {
        $order  = $transaction->getOrder();

        if (!$this->isRequestValid($transaction)) {

            $transaction->setState(TransactionInterface::STATE_KO);
            $transaction->setStatusCode(TransactionInterface::STATUS_WRONG_CALLBACK);

            return false;
        }

        if ($order->isValidated()) {

            $transaction->setState(TransactionInterface::STATE_KO);
            $transaction->setStatusCode(TransactionInterface::STATUS_WRONG_CALLBACK);

            return false;
        }

        if ($transaction->get('esito') === 'OK') {

            $transaction->setState(TransactionInterface::STATE_OK);
            $transaction->setStatusCode(TransactionInterface::STATUS_VALIDATED);

            $this->getLogger()->debug('Tutto ok');

            return true;
        }

        $transaction->setState(TransactionInterface::STATE_KO);
        $transaction->setStatusCode(TransactionInterface::STATUS_UNKNOWN);

        return false;
    }

    /**
     * Method called when an error occurs
     *
     * @param \Sonata\Component\Payment\TransactionInterface $transaction
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleError(TransactionInterface $transaction)
    {
        if ($transaction->getOrder()->isOpen()) {
            $transaction->getOrder()->setPaymentStatus($transaction->getStatusCode());
        }
    }

    /**
     * Send post back confirmation to the bank when the bank callback the site
     *
     * @param  \Sonata\Component\Payment\TransactionInterface $transaction
     * @return \Symfony\Component\HttpFoundation\Response,    false otherwise
     */
    public function sendConfirmationReceipt(TransactionInterface $transaction)
    {
        $order = $transaction->getOrder();
        if (!$order) {
            $transaction->setState(TransactionInterface::STATE_KO);
            $transaction->setStatusCode(TransactionInterface::STATUS_ORDER_UNKNOWN);

            return new Response('', 302, array(
                'Location' => $this->router->generate('sonata_payment_error', $transaction->getParameters(), true),
                'Content-Type' => 'text/plain',
            ));
        }

        if ($transaction->get('codiceEsito' != '0'))
        {
            $transaction->setState(TransactionInterface::STATE_KO);
            $transaction->setStatusCode(TransactionInterface::STATUS_ERROR_VALIDATION);

            return new Response('', 302, array(
                'Location' => $this->router->generate('sonata_payment_error', $transaction->getParameters(), true),
                'Content-Type' => 'text/plain',
            ));
        }

        $transaction->setState(TransactionInterface::STATE_OK);
        $transaction->setStatusCode(TransactionInterface::STATUS_VALIDATED);
        $transaction->getOrder()->setValidatedAt(new \DateTime);
        $transaction->getOrder()->setStatus(OrderInterface::STATUS_VALIDATED);
        $transaction->getOrder()->setPaymentStatus(TransactionInterface::STATUS_VALIDATED);

        return new Response('', 302, array(
            'Location' => $this->router->generate('sonata_payment_confirmation', $transaction->getParameters(), true),
            'Content-Type' => 'text/plain',
        ));
    }

    /**
     * Test if the request variables are valid for the current request
     *
     * WARNING : this methods does not check if the callback is valid
     *
     * @param \Sonata\Component\Payment\TransactionInterface $transaction
     *
     * @return boolean true if all parameter are ok
     */
    public function isRequestValid(TransactionInterface $transaction)
    {

        $checkUrl = $transaction->get('check');
        $checkPrivate = $this->generateUrlCheck($transaction->getOrder());

        $this->getLogger()->debug('Check URL: '.$checkUrl);
        $this->getLogger()->debug('Check Private: '.$checkPrivate);

        $this->getLogger()->debug('MAC: '.$transaction->get('mac'));
        $this->getLogger()->debug('Response MAC: '.$this->getResponseMac($transaction));


        return $checkUrl == $checkPrivate;
        /*&& $transaction->get('mac') == $this->getResponseMac($transaction);*/

    }

    /**
     * {@inheritdoc}
     */
    public function isBasketValid(BasketInterface $basket)
    {
        if ($basket->countBasketElements() == 0) {
            return false;
        }

        foreach ($basket->getBasketElements() as $element) {
            $product = $element->getProduct();
            if ($product->isRecurrentPayment() === true) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isAddableProduct(BasketInterface $basket, ProductInterface $product)
    {
        if (!$product->isRecurrentPayment()) {
            return true;
        }

        return false;
    }

    /**
     * return the transaction id from the bank
     *
     * @param \Sonata\Component\Payment\TransactionInterface $transaction
     */
    public function applyTransactionId(TransactionInterface $transaction)
    {
        $transaction->setTransactionId('codAut');
    }

    /**
     * return the order reference from the transaction
     *
     * @param \Sonata\Component\Payment\TransactionInterface $transaction
     *
     * @return string
     */
    public function getOrderReference(TransactionInterface $transaction)
    {
        return $transaction->get('codTrans');
    }

    private function getMac(OrderInterface $order)
    {
        $importo_da_passare = (int)(round($order->getTotalInc(), 2) * 100);
        $mac = 'codTrans=' . $order->getReference() . 'divisa=' . $this->divisa . 'importo=' . $importo_da_passare . $this->chiaveMac;

        return sha1($mac);
    }

    private function getResponseMac(TransactionInterface $transaction)
    {
        $order = $transaction->getOrder();
        $importo = (int)(round($order->getTotalInc(), 2) * 100);
        $mac = 'codTrans=' . $order->getReference() .
            'esito=' . $transaction->get('esito') .
            'importo=' .$importo .
            'divisa=' . $transaction->get('divisa') .
            'data=' . $transaction->get('data') .
            'orario=' . $transaction->get('orario') .
            'codAut=' . $transaction->get('codAut') .
            $this->chiaveMac;

        $this->getLogger()->debug('Response MAC no encrypt: '.$mac);

        return sha1($mac);
    }

}