document.addEventListener('DOMContentLoaded', function() {
    const cryptoItems = document.querySelectorAll('.crypto-item');
    const paymentDetails = document.getElementById('payment-details');
    const paymentSelection = document.getElementById('payment-selection');
    const cryptoNameSpan = document.getElementById('crypto-name');
    const cryptoAddressSpan = document.getElementById('crypto-address');
    const cryptoAmountSpan = document.getElementById('crypto-amount');
    const cryptoUnitSpan = document.getElementById('crypto-unit');
    const cryptoIconLarge = document.getElementById('crypto-icon-large');
    const timerElement = document.getElementById('timer');
    const backButton = document.getElementById('back-button');
    const copyText = document.getElementById('copy-text');
    const completePaymentButton = document.getElementById('complete-payment');
    const confirmationMessage = document.getElementById('confirmation-message');
    let qrCodeElement = document.getElementById('qr-code');
    let qrCode; // Store the QR code instance

    let countdownInterval;

    // Handle cryptocurrency item selection
    cryptoItems.forEach(item => {
        item.addEventListener('click', function() {
            const currency = this.getAttribute('data-currency');
            const address = this.getAttribute('data-address');

            // Mark the selected cryptocurrency
            cryptoItems.forEach(i => i.classList.remove('selected'));
            this.classList.add('selected');

            // Hide payment selection and show payment details
            paymentSelection.classList.add('hidden');
            paymentDetails.classList.remove('hidden');

            // Ensure all elements exist before setting properties
            if (cryptoNameSpan && cryptoAddressSpan && cryptoUnitSpan && cryptoIconLarge && qrCodeElement) {
                // Set payment details
                cryptoNameSpan.textContent = currency;
                cryptoAddressSpan.textContent = address;
                cryptoUnitSpan.textContent = 'USD'; // Always set to USD
                cryptoIconLarge.src = vestorPay.pluginUrl + 'icons/icon-' + currency.toLowerCase() + '.svg';

                // Clear previous QR code and generate a new one
                qrCodeElement.innerHTML = ''; // Clear the QR code container
                qrCode = new QRCode(qrCodeElement, {
                    text: address,
                    width: 128,
                    height: 128
                });

                // Set total amount to USD value from the localized PHP script
                if (cryptoAmountSpan) {
                    cryptoAmountSpan.textContent = vestorPay.amountUsd;
                }
            } else {
                console.error("Some elements are missing on the payment details page.");
            }
        });
    });

    // Copy address functionality
    [cryptoAddressSpan, copyText].forEach(element => {
        if (element) {
            element.addEventListener('click', function() {
                navigator.clipboard.writeText(cryptoAddressSpan.textContent).then(function() {
                    alert('Address copied to clipboard!');
                }, function() {
                    alert('Failed to copy address. Please try again.');
                });
            });
        }
    });

    // Handle back button click
    if (backButton) {
        backButton.addEventListener('click', function() {
            paymentDetails.classList.add('hidden');
            paymentSelection.classList.remove('hidden');
        });
    }

    // Handle payment proof submission via AJAX
    if (completePaymentButton) {
        completePaymentButton.addEventListener('click', function() {
            const formData = new FormData();
            formData.append('action', 'upload_payment_proof');
            formData.append('order_id', vestorPay.orderId);
            formData.append('payment_proof', document.getElementById('payment-proof').files[0]);
            const selectedCrypto = document.querySelector('.crypto-item.selected');
            if (selectedCrypto) {
                formData.append('crypto_used', selectedCrypto.getAttribute('data-currency')); // Send the selected cryptocurrency
            } else {
                alert('Please select a cryptocurrency.');
                return; // Prevent submission if no cryptocurrency is selected
            }
            formData.append('vestor_pay_nonce_field', vestorPay.nonce);

            console.log('Sending AJAX request for payment proof upload'); // Debugging log

            fetch(vestorPay.ajaxUrl, {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(response => {
                console.log('AJAX response:', response); // Debugging log
                if (response.success) {
                    confirmationMessage.style.display = 'block'; // Show confirmation message
                } else {
                    alert(response.data); // Show the error message
                }
            })
            .catch(error => console.error('Error uploading payment proof:', error)); // Debugging log
        });
    }

    // Countdown timer logic
    function startCountdown(duration) {
        let timer = duration, minutes, seconds;
        countdownInterval = setInterval(function () {
            minutes = parseInt(timer / 60, 10);
            seconds = parseInt(timer % 60, 10);

            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;

            if (timerElement) {
                timerElement.textContent = minutes + ":" + seconds;
            }

            if (--timer < 0) {
                clearInterval(countdownInterval);
                // Handle countdown end logic
            }
        }, 1000);
    }

    // Start the 20-minute countdown timer
    startCountdown(1200);

    // Handle the processing of payment proof via AJAX in the admin panel
    window.processPaymentProof = function(order_id, action) {
        const processProofNonce = vestorPay.processProofNonce;
        jQuery.ajax({
            url: vestorPay.ajaxUrl,
            method: 'POST',
            data: {
                action: 'process_payment_proof',
                order_id: order_id,
                process_action: action,
                _ajax_nonce: processProofNonce
            },
            success: function(response) {
                alert(response);
                location.reload();
            },
            error: function() {
                alert('An error occurred.');
            }
        });
    };
});
