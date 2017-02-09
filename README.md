Alert Bundle
------------

Simple symfony bundle for reporting PHP exceptions and JS exceptions to email. It's intended use is for applications running in production
 
_Notice: Alert Bundle uses internal symfony mailer service, so it must be properly set_

Documentation
=============

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

## Configuration

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
You might also want to disable alerts for DEV environment:

```yaml
# app/config/config_dev.yml
analogic_alert:
    enabled: false
```

## Javascript errors catching

Insert this code into html header:

```html
<!-- base.html.twig -->
<script>{{ javascript_error_listener() }}</script>
```