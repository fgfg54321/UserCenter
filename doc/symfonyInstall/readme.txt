windows config

0.php.exe add to system path

1.opn open_ssl

2.php.ini plus
[curl]
curl.cainfo="E:/phpStudy/php55n/ext/ssl/cacert.pem"

composer create-project symfony/framework-standard-edition my_project_name "2.8.*"


ÃÌº” css  javascript π‹¿Ì
$ composer require symfony/assetic-bundle

# app/config/config.yml
assetic:
    debug:          '%kernel.debug%'
    use_controller: '%kernel.debug%'
    filters:
        cssrewrite: ~

http://localhost/RecordWeb/recordweb/web/app_dev.php