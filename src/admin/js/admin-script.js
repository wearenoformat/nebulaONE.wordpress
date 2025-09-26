document.addEventListener('DOMContentLoaded', function() {
    // Tooltip functionality for help icons
    var helpIcons = document.querySelectorAll('.nebulaone-help');
    var activeTooltip = null;

    helpIcons.forEach(function(icon) {
        icon.addEventListener('click', function(event) {
            if (activeTooltip) {
                activeTooltip.remove();
                activeTooltip = null;
            }

            var tooltip = document.createElement('div');
            tooltip.className = 'nebulaone-help-tooltip';
            tooltip.innerText = icon.getAttribute('data-help');
            document.body.appendChild(tooltip);

            // Position the tooltip relative to the icon
            var rect = icon.getBoundingClientRect();
            tooltip.style.left = rect.left + window.scrollX + 'px';
            tooltip.style.top = rect.top + window.scrollY + 20 + 'px';
            tooltip.style.display = 'block';
            activeTooltip = tooltip;

            event.stopPropagation(); // Prevent document click from closing immediately
        });
    });

    // Close tooltip when clicking anywhere else on the document
    document.addEventListener('click', function() {
        if (activeTooltip) {
            activeTooltip.remove();
            activeTooltip = null;
        }
    });

    // Terms and Conditions Modal & Acceptance
    if (!nebulaOneAdmin.termsAccepted) {
        // Disable submit button on load if terms are not accepted
        var submitButton = document.querySelector('input[name="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
        }

        var termsLink = document.getElementById('nebulaone-terms-link');
        var termsModal = document.getElementById('nebulaone-terms-modal');
        var closeTermsButton = document.getElementById('nebulaone-close-terms');
        var acceptTermsButton = document.getElementById('nebulaone-accept-terms');

        if (termsLink) {
            termsLink.addEventListener('click', function(e) {
                e.preventDefault();
                if (termsModal) {
                    termsModal.style.display = 'block';
                }
            });
        }

        if (closeTermsButton) {
            closeTermsButton.addEventListener('click', function() {
                if (termsModal) {
                    termsModal.style.display = 'none';
                }
            });
        }

        if (acceptTermsButton) {
            acceptTermsButton.addEventListener('click', function() {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', nebulaOneAdmin.ajaxurl, true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200 && JSON.parse(xhr.responseText).success) {
                        location.reload(); // Reload the page to enable fields
                    } else {
                        console.error('Error accepting terms:', xhr.responseText);
                        alert('There was an error accepting the terms. Please try again.');
                    }
                };
                xhr.onerror = function() {
                    console.error('Network error during terms acceptance.');
                    alert('Network error. Please check your connection and try again.');
                };
                xhr.send('action=nebulaone_accept_terms&nonce=' + nebulaOneAdmin.nonce);
            });
        }
    }
});