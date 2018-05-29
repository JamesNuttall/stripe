<?php
/**
 * EnupalStripe plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2018 Enupal
 */

namespace enupal\stripe\controllers;

use Craft;
use craft\elements\Asset;
use craft\helpers\UrlHelper;
use craft\web\Controller as BaseController;
use enupal\stripe\Stripe;
use yii\web\NotFoundHttpException;


use enupal\stripe\elements\PaymentForm as StripeElement;

class PaymentFormsController extends BaseController
{
    /**
     * Save a Button
     *
     * @return null|\yii\web\Response
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveForm()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $paymentForm = new StripeElement;

        $formId = $request->getBodyParam('formId');

        if ($formId) {
            $paymentForm = Stripe::$app->paymentForms->getPaymentFormById($formId);
        }

        $paymentForm = Stripe::$app->paymentForms->populatePaymentFormFromPost($paymentForm);

        // Save it
        if (!Stripe::$app->paymentForms->savePaymentForm($paymentForm)) {
            Craft::$app->getSession()->setError(Stripe::t('Couldn’t save payment form.'));

            Craft::$app->getUrlManager()->setRouteParams([
                    'paymentForm' => $paymentForm
                ]
            );

            return null;
        }

        Craft::$app->getSession()->setNotice(Stripe::t('Payment form saved.'));

        return $this->redirectToPostedUrl($paymentForm);
    }

    /**
     * Edit a Payment Form.
     *
     * @param int|null           $formId The button's ID, if editing an existing button.
     * @param StripeElement|null $paymentForm   The button send back by setRouteParams if any errors on savePaymentForm
     *
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionEditForm(int $formId = null, StripeElement $paymentForm = null)
    {
       # $paymentForm = Stripe::$app->paymentForms->getPaymentFormById($formId);
       # foreach ($paymentForm->{Stripe::$app->paymentForms::MULTIPLE_PLANS_HANDLE} as $item) {
       #     Craft::dd($item->selectPlan);

        #}
        #Craft::dd($paymentForm->{Stripe::$app->paymentForms::MULTIPLE_PLANS_HANDLE});

        // Immediately create a new Slider
        if ($formId === null) {
            $paymentForm = Stripe::$app->paymentForms->createNewPaymentForm();

            if ($paymentForm->id) {
                $url = UrlHelper::cpUrl('enupal-stripe/forms/edit/'.$paymentForm->id);
                return $this->redirect($url);
            } else {
                throw new \Exception(Stripe::t('Error creating Button'));
            }
        } else {
            if ($formId !== null) {
                if ($paymentForm === null) {
                    // Get the payment form
                    $paymentForm = Stripe::$app->paymentForms->getPaymentFormById($formId);

                    if (!$paymentForm) {
                        throw new NotFoundHttpException(Stripe::t('Button not found'));
                    }
                }
            }
        }

        $variables['logoElement'] = [$paymentForm->getLogoAsset()];
        $variables['elementType'] = Asset::class;

        $variables['formId'] = $formId;
        $variables['stripeForm'] = $paymentForm;

        // Set the "Continue Editing" URL
        $variables['continueEditingUrl'] = 'enupal-stripe/forms/edit/{id}';

        $variables['settings'] = Stripe::$app->settings->getSettings();

        return $this->renderTemplate('enupal-stripe/forms/_edit', $variables);
    }

    /**
     * Delete a Stripe Button.
     *
     * @return \yii\web\Response
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDeleteForm()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $formId = $request->getRequiredBodyParam('formId');
        $paymentForm = Stripe::$app->paymentForms->getPaymentFormById($formId);

        // @TODO - handle errors
        Stripe::$app->paymentForms->deletePaymentForm($paymentForm);

        Craft::$app->getSession()->setNotice(Stripe::t('Payment form deleted.'));

        return $this->redirectToPostedUrl($paymentForm);
    }

    /**
     * Retrieve all stripe plans as options for dropdown select field
     *
     * @return \yii\web\Response
     */
    public function actionRefreshPlans()
    {
        try {
            $this->requirePostRequest();
            $this->requireAcceptsJson();

            $plans = Stripe::$app->plans->getStripePlans();
        } catch (\Throwable $e) {
            return $this->asErrorJson($e->getMessage());
        }

        return $this->asJson(['success'=> true, 'plans' => $plans]);
    }
}