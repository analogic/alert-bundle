Alert Bundle
------------

Simple symfony bundle for reporting PHP exceptions and JS exceptions to email. It's intended use is for applications running in production
 
_Notice: Alerter uses internal symfony mailer service, so it must be properly set_

Documentation
=============

## Requirements

- Twig
- Swift Mailer

## Installation

Run from terminal:

```bash
$ composer require analogic/alert-bundle
```

Enable bundle in the kernel:

```php
<?php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = [
        // ...
        new Analogic\AlertBundle\AlertBundle(),
    ];
}
```

## configuration

```yaml
# app/config/config.yml
analogic_alert:
    enabled: true
    prefix: "[PANIC] "
    from:
        email: "exception@source.com"
        name: "Alert Monkey"
    to: 
        - "code_monkey1@example.com"
        - "code_monkey2@example.com" 
    ignore:
        - Symfony\Component\HttpKernel\Exception\NotFoundHttpException
        - Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
```

## javascript errors catching

Insert this code to html header

```html
<!-- base.html.twig -->
<script>{{ javascript_error_listener() }}</script>
```