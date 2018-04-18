var enupalStripe = {};

(function($) {
    'use strict';
    enupalStripe = {
        // All Enupal Stripe buttons
        buttonsList: {},
        finalData: {},

        init: function() {
            this.buttonsList = $('.enupal-stripe-form');

            this.buttonsList.each(function() {
                var enupalButtonElement = $(this);
                enupalStripe.initializeForm(enupalButtonElement);
            });
        },

        initializeForm: function(enupalButtonElement) {
            // get the form ID
            var enupalStripeData = $.parseJSON($(enupalButtonElement).find('[name="enupalStripe[stripeData]"]').val());
            // reset our values
            $(enupalButtonElement).find('[name="enupalStripe[stripeData]"]').val('');

            this.finalData.finalAmount = enupalStripeData.amount;

            //  Stripe config
            var stripeHandler = null;

            // Stripe Checkout handler configuration.
            // Docs: https://stripe.com/docs/checkout#integration-custom
            stripeHandler = StripeCheckout.configure({
                key: enupalStripeData.pbk,
                token: processStripeToken,
                opened: function() {
                },
                closed: function() {
                }
            });

            // Callback function to handle StripeCheckout.configure
            function processStripeToken(token, args) {
                // At this point the Stripe Checkout overlay is validated and submitted.
                // Set values to hidden elements to pass via POST when submitting the form for payment.
                enupalButtonElement.find('[name="enupalStripe[token]"]').val(token.id);
                enupalButtonElement.find('[name="enupalStripe[email]"]').val(token.email);

                // Add others values to form
                enupalStripe.addValuesToForm(enupalButtonElement, args, enupalStripeData);

                // Disable pay button and show a nice UI message
                enupalButtonElement.find('.enupal-stripe-button')
                    .prop('disabled', true)
                    .find('span')
                    .text(enupalStripeData.paymentButtonProcessingText);

                // Unbind original form submit trigger before calling again to "reset" it and submit normally.
                enupalButtonElement.unbind('submit', [enupalButtonElement, enupalStripeData]);

                enupalButtonElement.submit();
            }

            // Pay Button clicked
            enupalButtonElement.find('.enupal-stripe-button').on('click', function(e) {
                e.preventDefault();

                enupalStripe.submitPayment(enupalButtonElement, enupalStripeData, stripeHandler);
            });
        },

        addValuesToForm: function(enupalButtonElement, args, enupalStripeData) {
            if (enupalStripeData.stripe.shippingAddress){
                if (args.shipping_name) {
                    enupalButtonElement.find('[name="enupalStripe[address][name]"]').val(args.shipping_name);
                }

                if (args.shipping_address_country) {
                    enupalButtonElement.find('[name="enupalStripe[address][country]"]').val(args.shipping_address_country);
                }

                if (args.shipping_address_zip) {
                    enupalButtonElement.find('[name="enupalStripe[address][zip]"]').val(args.shipping_address_zip);
                }

                if (args.shipping_address_state) {
                    enupalButtonElement.find('[name="enupalStripe[address][state]"]').val(args.shipping_address_state);
                }

                if (args.shipping_address_line1) {
                    enupalButtonElement.find('[name="enupalStripe[address][line1]"]').val(args.shipping_address_line1);
                }

                if (args.shipping_address_city) {
                    enupalButtonElement.find('[name="enupalStripe[address][city]"]').val(args.shipping_address_city);
                }
            }
        },

        submitPayment: function(enupalButtonElement, enupalStripeData, stripeHandler) {
            var stripeConfig = enupalStripeData.stripe;
            stripeConfig.amount = this.convertToCents(stripeConfig.amount);
            enupalButtonElement.find('[name="enupalStripe[amount]"]').val(stripeConfig.amount);
            // If everything checks out then let's open the form
            stripeHandler.open(stripeConfig);
        },

        convertToCents: function(amount) {
            return (amount * 100);
        }
    };

    $(document).ready(function($) {
        enupalStripe.init();
    });
})(jQuery);