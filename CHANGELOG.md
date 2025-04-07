# 1.3.0

## What's new
- We moved the module to the `dotdigital` namespace.


_The following is a historical changelog relating to `hyva-themes/magento2-dotdigitalgroup-email`._

# 1.2.1

### Improvements
- We added `registerInlineScript` to our templates for strict CSP compatibility.

# 1.2.0

### What's new
- Web behavior tracking now uses the same price calculations as catalog sync in Dotdigitalgroup_Email.

### Improvements
- Merchants can now see the currently installed version of the module at **Reports > Dotdigital > Dashboard**.

# 1.1.0

### What's new
- Back in stock product notifications can now be used in Hyvä themes.
- We added an `onComplete` code snippet to create Magento subscribers from email addresses submitted via Dotdigital forms.

### Improvements
- We added missing product data keys to the web behavior tracking payload.
- We made some changes to the compatibility code for email capture, to support Hyvä Checkout.
- We’re now adding our page tracking and web behavior tracking scripts on the `init-external-scripts` event.

### Bug fixes
- We added the `hyva_` prefix to our layout files to prevent changes for non-Hyvä themes.
- ROI tracking on the checkout success page now functions correctly.

# 1.0.3

### Improvements
- We updated our package name to `dotdigital/dotdigital-magento2-extension` in composer.json.

### Bug fixes
- Various template bugs were fixed. [External contribution](https://gitlab.hyva.io/hyva-themes/hyva-compat/magento2-dotdigitalgroup-email/-/merge_requests/2)
- We repaired the ‘Newsletter Subscriptions’ layout in the customer account.

