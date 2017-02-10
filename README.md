Alert Bundle
------------

Simple symfony bundle for reporting Request Exceptions, Command Exceptions and JS Exceptions to email. It's intended use is for applications running in production giving some valuable feedback when something wrong happens.
 
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
        new Analogic\AlertBundle\AnalogicAlertBundle(),
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

## Commands exceptions catching

No settings needed. Commands in Symfony are run in DEV environment by default(?) so you might need add "-e prod" if you disabled alerting for DEV.
  
## "Faster" exceptions

By default Symfony email configuration is to sent every email right away. For production is better to setup file spooling (see: [https://symfony.com/doc/current/email/spool.html](https://symfony.com/doc/current/email/spool.html)) with crond or better [incron](http://inotify.aiken.cz/?section=incron&page=about&lang=en) which does not slow down reporting.